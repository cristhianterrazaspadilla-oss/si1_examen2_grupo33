<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Pago;
use App\Models\Postulante;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Throwable;

/**
 * Paquete: Gestión de Postulantes y Admisión
 * Caso de Uso: CU8 (Registrar Pagos / Integración Pasarela Stripe)
 * 
 * Gestiona el registro de pagos de inscripción de postulantes.
 * Se integra con Stripe SDK para la creación y verificación de sesiones de pago (Checkout Sessions).
 * Tras confirmar el pago (Paid), inscribe formalmente al postulante en el preuniversitario.
 */
class PagoController extends Controller
{
    // Controlador del caso de uso: CU7 Gestionar Pagos
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estadoPago = $request->string('estado_pago')->toString();
        $isPostulante = $this->isPostulante();
        $canManagePayments = $this->canManagePayments();

        $pagos = Pago::query()
            ->with('postulante')
            ->when($isPostulante, function ($query): void {
                $query->whereHas(
                    'postulante',
                    fn ($postulanteQuery) => $postulanteQuery->where('usuario_id', auth()->id())
                );
            })
            ->when($search !== '', function ($query) use ($search): void {
                $query->whereHas('postulante', function ($postulanteQuery) use ($search): void {
                    $postulanteQuery->where('ci', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%");
                });
            })
            ->when($estadoPago !== '', fn ($query) => $query->where('estado_pago', $estadoPago))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_postulantes_admision.pagos.index', [
            'pagos' => $pagos,
            'search' => $search,
            'estadoPago' => $estadoPago,
            'isPostulante' => $isPostulante,
            'canManagePayments' => $canManagePayments,
        ]);
    }

    public function create(): View
    {
        $this->authorizePaymentManagement();

        $postulantes = Postulante::query()
            ->where('estado_inscripcion', 'REQUISITOS_APROBADOS')
            ->whereDoesntHave('pagos', fn ($query) => $query->where('estado_pago', 'PENDIENTE'))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return view('gestion_postulantes_admision.pagos.create', [
            'postulantes' => $postulantes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizePaymentManagement();

        $validated = $request->validate([
            'postulante_id' => ['required', 'exists:postulantes,id'],
            'monto' => ['required', 'numeric', 'min:1'],
            'moneda' => ['required', 'string', 'in:BOB,USD'],
            'observacion' => ['nullable', 'string'],
        ]);

        $postulante = Postulante::query()
            ->with('pagos')
            ->findOrFail($validated['postulante_id']);

        if ($postulante->estado_inscripcion !== 'REQUISITOS_APROBADOS') {
            return back()
                ->withErrors(['postulante_id' => 'Solo se puede generar pagos para postulantes con requisitos aprobados.'])
                ->withInput();
        }

        $tienePagoPendiente = $postulante->pagos
            ->contains(fn (Pago $pago) => $pago->estado_pago === 'PENDIENTE');

        if ($tienePagoPendiente) {
            return back()
                ->withErrors(['postulante_id' => 'El postulante ya tiene un pago pendiente registrado.'])
                ->withInput();
        }

        $stripeSecret = (string) config('services.stripe.secret');

        if ($stripeSecret === '') {
            return back()
                ->withErrors(['stripe' => 'No existe configuracion de Stripe disponible para generar el enlace de pago.'])
                ->withInput();
        }

        try {
            $stripe = new StripeClient($stripeSecret);

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'success_url' => route('gestion-postulantes-admision.pagos.index') . '?stripe_status=success&session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('gestion-postulantes-admision.pagos.create') . '?postulante_id=' . $postulante->id,
                'line_items' => [[
                    'quantity' => 1,
                    'price_data' => [
                        'currency' => Str::lower($validated['moneda']),
                        'unit_amount' => (int) round(((float) $validated['monto']) * 100),
                        'product_data' => [
                            'name' => 'Inscripcion Curso Preuniversitario FICCT',
                            'description' => 'Pago de inscripcion para postulante ' . trim($postulante->nombres . ' ' . $postulante->apellidos),
                        ],
                    ],
                ]],
                'metadata' => [
                    'postulante_id' => (string) $postulante->id,
                    'ci' => (string) $postulante->ci,
                ],
            ]);
        } catch (ApiErrorException $exception) {
            Log::error('Stripe error al crear checkout session', [
                'postulante_id' => $postulante->id,
                'moneda' => $validated['moneda'],
                'monto' => $validated['monto'],
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors(['stripe' => 'No se pudo procesar el pago. Verifica la información o inténtalo nuevamente.'])
                ->withInput();
        }

        try {
            $pago = Pago::create([
                'postulante_id' => $postulante->id,
                'monto' => $validated['monto'],
                'moneda' => $validated['moneda'],
                'stripe_payment_link' => $session->url,
                'stripe_payment_id' => $session->id,
                'estado_pago' => 'PENDIENTE',
                'fecha_pago' => null,
                'observacion' => $validated['observacion'] ?? null,
            ]);
        } catch (Throwable $exception) {
            Log::error('Error BD al guardar pago pendiente', [
                'postulante_id' => $postulante->id,
                'stripe_payment_id' => $session->id ?? null,
                'error' => $exception->getMessage(),
            ]);

            return back()
                ->withErrors(['pago' => 'No se pudo actualizar la información del pago. Inténtalo nuevamente.'])
                ->withInput();
        }

        try {
            $postulante->update([
                'estado_inscripcion' => 'PAGO_PENDIENTE',
            ]);
        } catch (Throwable $exception) {
            Log::error('Error al actualizar estado del postulante tras crear pago', [
                'postulante_id' => $postulante->id,
                'pago_id' => $pago->id ?? null,
                'error' => $exception->getMessage(),
            ]);

            $pago->delete();

            return back()
                ->withErrors(['postulante' => 'No se pudo actualizar la información del pago. Inténtalo nuevamente.'])
                ->withInput();
        }

        BitacoraHelper::registrar(
            'GENERAR_ENLACE_PAGO',
            'Pagos',
            'Se genero un enlace de pago para el postulante CI ' . $postulante->ci . '.'
        );

        BitacoraHelper::registrar(
            'REGISTRAR_PAGO',
            'Pagos',
            'Se registro un pago pendiente para el postulante CI ' . $postulante->ci . '.'
        );

        return redirect()
            ->route('gestion-postulantes-admision.pagos.show', $pago)
            ->with('success', 'Enlace de pago generado correctamente en modo prueba.');
    }

    public function show(Pago $pago): View
    {
        $pago->load('postulante');
        $this->authorizePago($pago);

        return view('gestion_postulantes_admision.pagos.show', [
            'pago' => $pago,
            'isPostulante' => $this->isPostulante(),
            'canManagePayments' => $this->canManagePayments(),
        ]);
    }

    public function edit(Pago $pago): View
    {
        $this->authorizePaymentManagement();
        $pago->load('postulante');

        return view('gestion_postulantes_admision.pagos.edit', compact('pago'));
    }

    public function update(Request $request, Pago $pago): RedirectResponse
    {
        $this->authorizePaymentManagement();

        $validated = $request->validate([
            'observacion' => ['nullable', 'string'],
        ]);

        $pago->update([
            'observacion' => $validated['observacion'] ?? null,
        ]);

        return redirect()
            ->route('gestion-postulantes-admision.pagos.show', $pago)
            ->with('success', 'Observacion del pago actualizada correctamente.');
    }

    public function verificar(Pago $pago): RedirectResponse
    {
        $this->authorizePaymentManagement();
        $pago->load('postulante');

        if ($pago->estado_pago === 'CONFIRMADO') {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->with('info', 'El pago ya fue confirmado anteriormente.');
        }

        if ($pago->estado_pago !== 'PENDIENTE') {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->withErrors(['verificacion' => 'Solo se pueden verificar pagos en estado PENDIENTE.']);
        }

        if (blank($pago->stripe_payment_id)) {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->withErrors(['verificacion' => 'El pago no tiene stripe_payment_id para consultar en Stripe.']);
        }

        if (blank($pago->postulante_id)) {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->withErrors(['verificacion' => 'El pago no tiene postulante_id asociado para confirmar la inscripcion.']);
        }

        $stripeSecret = (string) config('services.stripe.secret');

        if ($stripeSecret === '') {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->withErrors(['verificacion' => 'No existe configuracion de Stripe disponible para verificar el pago.']);
        }

        try {
            $stripe = new StripeClient($stripeSecret);
            $session = $stripe->checkout->sessions->retrieve($pago->stripe_payment_id, []);
        } catch (ApiErrorException $exception) {
            Log::error('Stripe error al verificar checkout session', [
                'pago_id' => $pago->id,
                'postulante_id' => $pago->postulante_id,
                'stripe_payment_id' => $pago->stripe_payment_id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->withErrors(['verificacion' => 'No se pudo procesar el pago. Verifica la información o inténtalo nuevamente.']);
        }

        $paymentStatus = (string) ($session->payment_status ?? '');

        if ($paymentStatus === 'paid') {
            try {
                $timestamp = now();

                $updatedPago = DB::table('pagos')
                    ->where('id', $pago->id)
                    ->where('estado_pago', 'PENDIENTE')
                    ->update([
                        'estado_pago' => 'CONFIRMADO',
                        'fecha_pago' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);
            } catch (Throwable $exception) {
                Log::error('Error al actualizar pago confirmado con update directo', [
                    'pago_id' => $pago->id,
                    'postulante_id' => $pago->postulante_id,
                    'error' => $exception->getMessage(),
                ]);

                return redirect()
                    ->route('gestion-postulantes-admision.pagos.show', $pago)
                    ->withErrors(['verificacion' => 'No se pudo actualizar la información del pago. Inténtalo nuevamente.']);
            }

            if ($updatedPago !== 1) {
                return redirect()
                    ->route('gestion-postulantes-admision.pagos.show', $pago)
                    ->withErrors(['verificacion' => 'No se pudo actualizar el pago pendiente. Puede que ya haya sido confirmado o anulado.']);
            }

            try {
                $updatedPostulante = DB::table('postulantes')
                    ->where('id', $pago->postulante_id)
                    ->update([
                        'estado_inscripcion' => 'INSCRITO',
                        'updated_at' => $timestamp,
                    ]);
            } catch (Throwable $exception) {
                Log::error('Error al actualizar postulante tras confirmar pago con update directo', [
                    'pago_id' => $pago->id,
                    'postulante_id' => $pago->postulante_id,
                    'error' => $exception->getMessage(),
                ]);

                return redirect()
                    ->route('gestion-postulantes-admision.pagos.show', $pago)
                    ->withErrors(['verificacion' => 'No se pudo actualizar la información del pago. Inténtalo nuevamente.']);
            }

            if ($updatedPostulante !== 1) {
                Log::error('No se actualizo el postulante tras confirmar pago con update directo', [
                    'pago_id' => $pago->id,
                    'postulante_id' => $pago->postulante_id,
                    'updated_postulante' => $updatedPostulante,
                ]);

                return redirect()
                    ->route('gestion-postulantes-admision.pagos.show', $pago)
                    ->withErrors(['verificacion' => 'El pago fue confirmado, pero no se pudo actualizar el estado del postulante. Revisar manualmente.']);
            }

            BitacoraHelper::registrar(
                'CONFIRMAR_PAGO',
                'Pagos',
                'Se confirmo el pago del postulante CI ' . (string) $pago->postulante?->ci . '.'
            );

            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->with('success', 'Pago confirmado correctamente. El postulante fue inscrito.');
        }

        if ($paymentStatus === 'unpaid') {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->with('info', 'El pago aun no fue confirmado por Stripe.');
        }

        if ($paymentStatus === 'no_payment_required') {
            return redirect()
                ->route('gestion-postulantes-admision.pagos.show', $pago)
                ->with('warning', 'Stripe devolvio no_payment_required. Se requiere revision manual antes de inscribir al postulante.');
        }

        return redirect()
            ->route('gestion-postulantes-admision.pagos.show', $pago)
            ->with('warning', 'Stripe devolvio el estado ' . $paymentStatus . '. No se realizaron cambios en el pago.');
    }

    public function destroy(Pago $pago): RedirectResponse
    {
        $this->authorizePaymentManagement();
        $pago->loadMissing('postulante');

        DB::transaction(function () use ($pago): void {
            $pago->update([
                'estado_pago' => 'ANULADO',
            ]);

            $pago->loadMissing('postulante');

            if ($pago->postulante && $pago->postulante->estado_inscripcion !== 'INSCRITO') {
                $pago->postulante->update([
                    'estado_inscripcion' => 'REQUISITOS_APROBADOS',
                ]);
            }
        });

        BitacoraHelper::registrar(
            'RECHAZAR_PAGO',
            'Pagos',
            'Se anulo el pago del postulante CI ' . (string) $pago->postulante?->ci . '.'
        );

        return redirect()
            ->route('gestion-postulantes-admision.pagos.index')
            ->with('success', 'Pago anulado correctamente.');
    }

    protected function authorizePago(Pago $pago): void
    {
        if (! $this->isPostulante()) {
            return;
        }

        abort_unless($pago->postulante?->usuario_id === auth()->id(), 403);
    }

    protected function authorizePaymentManagement(): void
    {
        abort_unless($this->canManagePayments(), 403);
    }

    protected function isPostulante(): bool
    {
        return $this->roleName() === 'postulante';
    }

    protected function canManagePayments(): bool
    {
        return $this->roleName() === 'administrador';
    }

    protected function roleName(): string
    {
        return Str::of((string) (auth()->user()?->rol?->nombre ?? ''))
            ->lower()
            ->ascii()
            ->toString();
    }
}
