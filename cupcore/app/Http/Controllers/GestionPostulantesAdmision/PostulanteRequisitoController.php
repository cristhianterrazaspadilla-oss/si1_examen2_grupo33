<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Postulante;
use App\Models\PostulanteRequisito;
use App\Models\Requisito;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Paquete: Gestión de Postulantes y Admisión
 * Casos de Uso: CU6 (Cargar Requisitos de Inscripción) y CU7 (Validar Requisitos de Inscripción)
 * 
 * Modela la relación N:M entre postulantes y requisitos de inscripción.
 * Permite a los coordinadores validar, observar o marcar requisitos como aprobados, 
 * actualizando el estado de inscripción del postulante de forma automática.
 */
class PostulanteRequisitoController extends Controller
{
    // Controlador del caso de uso: CU6 Gestionar Requisitos de Admisión
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estadoInscripcion = $request->string('estado_inscripcion')->toString();
        $totalObligatorios = Requisito::query()
            ->where('estado', 'ACTIVO')
            ->where('obligatorio', true)
            ->count();

        $postulantes = Postulante::query()
            ->with(['postulanteRequisitos.requisito'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ci', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%");
                });
            })
            ->when($estadoInscripcion !== '', fn ($query) => $query->where('estado_inscripcion', $estadoInscripcion))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_postulantes_admision.requisitos.postulantes', compact(
            'postulantes',
            'search',
            'estadoInscripcion',
            'totalObligatorios'
        ));
    }

    public function show(Postulante $postulante): View
    {
        $this->ensureRequisitosForPostulante($postulante);

        $postulante->load([
            'usuario.rol',
            'carreraPrimeraOpcion',
            'carreraSegundaOpcion',
            'postulanteRequisitos.requisito',
            'postulanteRequisitos.validadoPor',
        ]);

        $requisitos = $postulante->postulanteRequisitos
            ->filter(fn (PostulanteRequisito $item) => $item->requisito && $item->requisito->estado === 'ACTIVO')
            ->sortBy(fn (PostulanteRequisito $item) => $item->requisito->nombre)
            ->values();

        return view('gestion_postulantes_admision.requisitos.validar', compact('postulante', 'requisitos'));
    }

    public function update(Request $request, Postulante $postulante): RedirectResponse
    {
        $this->ensureRequisitosForPostulante($postulante);

        $validated = $request->validate([
            'requisitos' => ['required', 'array'],
            'requisitos.*.id' => ['required', 'exists:postulante_requisitos,id'],
            'requisitos.*.estado' => ['required', Rule::in(['PENDIENTE', 'APROBADO', 'OBSERVADO'])],
            'requisitos.*.observacion' => ['nullable', 'string'],
        ]);

        foreach ($validated['requisitos'] as $item) {
            $registro = PostulanteRequisito::query()
                ->where('postulante_id', $postulante->id)
                ->findOrFail($item['id']);

            $estadoAnterior = $registro->estado;

            $payload = [
                'estado' => $item['estado'],
                'observacion' => $item['observacion'] ?? null,
            ];

            if (in_array($item['estado'], ['APROBADO', 'OBSERVADO'], true)) {
                $payload['validado_por'] = auth()->id();
                $payload['fecha_validacion'] = Carbon::now();
            } else {
                $payload['validado_por'] = null;
                $payload['fecha_validacion'] = null;
            }

            $registro->update($payload);

            if ($estadoAnterior !== $item['estado']) {
                BitacoraHelper::registrar(
                    $item['estado'] === 'OBSERVADO' ? 'OBSERVAR_REQUISITO' : 'VALIDAR_REQUISITO',
                    'Requisitos',
                    'Se actualizo el requisito ' . (string) $registro->requisito?->nombre . ' del postulante CI ' . $postulante->ci . ' a estado ' . $item['estado'] . '.'
                );
            }
        }

        $this->actualizarEstadoInscripcion($postulante->fresh());

        return redirect()
            ->route('gestion-postulantes-admision.requisitos-postulantes.show', $postulante)
            ->with('success', 'Validación de requisitos guardada correctamente.');
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.requisitos-postulantes.index');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.requisitos-postulantes.index');
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.requisitos-postulantes.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.requisitos-postulantes.index');
    }

    protected function ensureRequisitosForPostulante(Postulante $postulante): void
    {
        $requisitosActivos = Requisito::query()
            ->where('estado', 'ACTIVO')
            ->get();

        foreach ($requisitosActivos as $requisito) {
            PostulanteRequisito::firstOrCreate(
                [
                    'postulante_id' => $postulante->id,
                    'requisito_id' => $requisito->id,
                ],
                [
                    'estado' => 'PENDIENTE',
                ]
            );
        }
    }

    protected function actualizarEstadoInscripcion(Postulante $postulante): void
    {
        if ($postulante->estado_inscripcion === 'INSCRITO') {
            return;
        }

        $obligatorios = PostulanteRequisito::query()
            ->where('postulante_id', $postulante->id)
            ->whereHas('requisito', fn ($query) => $query->where('estado', 'ACTIVO')->where('obligatorio', true))
            ->get();

        if ($obligatorios->contains('estado', 'OBSERVADO')) {
            $postulante->update(['estado_inscripcion' => 'OBSERVADO']);
            return;
        }

        if ($obligatorios->isNotEmpty() && $obligatorios->every(fn (PostulanteRequisito $item) => $item->estado === 'APROBADO')) {
            $postulante->update(['estado_inscripcion' => 'REQUISITOS_APROBADOS']);
            return;
        }

        if ($obligatorios->contains('estado', 'PENDIENTE')) {
            $postulante->update(['estado_inscripcion' => 'PRE_REGISTRADO']);
        }
    }
}
