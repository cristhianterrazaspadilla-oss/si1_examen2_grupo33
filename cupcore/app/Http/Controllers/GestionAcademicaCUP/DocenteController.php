<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\DocenteAsignacion;
use App\Models\User;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class DocenteController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estado = $request->string('estado')->toString();

        $docentes = Docente::query()
            ->with('usuario')
            ->withCount([
                'asignaciones as asignaciones_activas_count' => fn ($query) => $query->where('estado', 'ACTIVO'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ci', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%")
                        ->orWhere('profesion', 'like', "%{$search}%")
                        ->orWhere('especialidad', 'like', "%{$search}%");
                });
            })
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_academica_cup.docentes.index', [
            'docentes' => $docentes,
            'search' => $search,
            'estado' => $estado,
            'totalDocentesActivos' => Docente::where('estado', 'ACTIVO')->count(),
            'docentesInactivos' => Docente::where('estado', 'INACTIVO')->count(),
            'asignacionesActivas' => DocenteAsignacion::where('estado', 'ACTIVO')->count(),
            'docentesSinAsignacion' => Docente::query()
                ->where('estado', 'ACTIVO')
                ->whereDoesntHave('asignaciones', fn ($query) => $query->where('estado', 'ACTIVO'))
                ->count(),
        ]);
    }

    public function create(): View
    {
        return view('gestion_academica_cup.docentes.create', [
            'usuariosDisponibles' => $this->usuariosDocenteDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateDocente($request);

        $docente = Docente::create($this->payloadDocente($request, $validated));
        BitacoraHelper::registrar(
            'CREAR_DOCENTE',
            'Docentes',
            'Se creo el docente CI ' . $docente->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Docente registrado correctamente.');
    }

    public function show(Docente $docente): View
    {
        $docente->load([
            'usuario.rol',
            'asignaciones' => fn ($query) => $query
                ->with(['grupo', 'materia'])
                ->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 ELSE 1 END")
                ->orderByDesc('gestion')
                ->orderBy('id'),
        ]);

        return view('gestion_academica_cup.docentes.show', [
            'docente' => $docente,
            'asignacionesActivasCount' => $docente->asignaciones->where('estado', 'ACTIVO')->count(),
        ]);
    }

    public function edit(Docente $docente): View
    {
        return view('gestion_academica_cup.docentes.edit', [
            'docente' => $docente,
            'usuariosDisponibles' => $this->usuariosDocenteDisponibles($docente),
        ]);
    }

    public function update(Request $request, Docente $docente): RedirectResponse
    {
        $validated = $this->validateDocente($request, $docente);

        $docente->update($this->payloadDocente($request, $validated, $docente));
        BitacoraHelper::registrar(
            'ACTUALIZAR_DOCENTE',
            'Docentes',
            'Se actualizo el docente CI ' . $docente->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Docente actualizado correctamente.');
    }

    public function destroy(Docente $docente): RedirectResponse
    {
        try {
            $updatedDocente = DB::table('docentes')
                ->where('id', $docente->id)
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al desactivar docente', [
                'docente_id' => $docente->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.index')
                ->withErrors(['docente' => 'No se pudo desactivar el docente. Inténtalo nuevamente.']);
        }

        if ($updatedDocente !== 1) {
            Log::error('No se pudo desactivar docente', [
                'docente_id' => $docente->id,
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.index')
                ->withErrors(['docente' => 'No se pudo desactivar el docente seleccionado.']);
        }

        try {
            DB::table('docente_asignaciones')
                ->where('docente_id', $docente->id)
                ->where('estado', 'ACTIVO')
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al desactivar asignaciones del docente', [
                'docente_id' => $docente->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.index')
                ->withErrors(['docente' => 'El docente fue desactivado, pero no se pudieron actualizar sus asignaciones relacionadas.']);
        }

        BitacoraHelper::registrar(
            'DESACTIVAR_DOCENTE',
            'Docentes',
            'Se desactivo el docente CI ' . $docente->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.index')
            ->with('success', 'Docente desactivado correctamente.');
    }

    public function activar(Docente $docente): RedirectResponse
    {
        try {
            $updatedDocente = DB::table('docentes')
                ->where('id', $docente->id)
                ->update([
                    'estado' => 'ACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al activar docente', [
                'docente_id' => $docente->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.index')
                ->withErrors(['docente' => 'No se pudo activar el docente. Inténtalo nuevamente.']);
        }

        if ($updatedDocente !== 1) {
            Log::error('No se pudo activar docente', [
                'docente_id' => $docente->id,
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.index')
                ->withErrors(['docente' => 'No se pudo activar el docente seleccionado.']);
        }

        BitacoraHelper::registrar(
            'ACTIVAR_DOCENTE',
            'Docentes',
            'Se activo el docente CI ' . $docente->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Docente activado correctamente.');
    }

    protected function validateDocente(Request $request, ?Docente $docente = null): array
    {
        $validated = $request->validate([
            'usuario_id' => ['nullable', 'exists:usuarios,id'],
            'ci' => ['required', 'string', 'max:20', Rule::unique('docentes', 'ci')->ignore($docente?->id)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'correo' => ['nullable', 'email', 'max:150', Rule::unique('docentes', 'correo')->ignore($docente?->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'profesion' => ['nullable', 'string', 'max:150'],
            'especialidad' => ['nullable', 'string', 'max:150'],
            'tiene_maestria' => ['nullable', 'boolean'],
            'tiene_diplomado' => ['nullable', 'boolean'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        if (! empty($validated['usuario_id'])) {
            $usuario = User::query()
                ->with('rol')
                ->find($validated['usuario_id']);

            $errors = [];

            if (! $usuario || $usuario->estado !== 'ACTIVO') {
                $errors['usuario_id'] = 'Solo se pueden asociar usuarios activos.';
            } elseif (strtolower((string) $usuario->rol?->nombre) !== 'docente') {
                $errors['usuario_id'] = 'El usuario asociado debe tener el rol Docente.';
            } else {
                $docenteAsociado = Docente::query()
                    ->where('usuario_id', $usuario->id)
                    ->when($docente, fn ($query) => $query->where('id', '!=', $docente->id))
                    ->exists();

                if ($docenteAsociado) {
                    $errors['usuario_id'] = 'El usuario seleccionado ya esta asociado a otro docente.';
                }
            }

            if ($errors !== []) {
                throw ValidationException::withMessages($errors);
            }
        }

        return $validated;
    }

    protected function payloadDocente(Request $request, array $validated, ?Docente $docente = null): array
    {
        return [
            'usuario_id' => $validated['usuario_id'] ?? null,
            'ci' => $validated['ci'],
            'nombres' => $validated['nombres'],
            'apellidos' => $validated['apellidos'],
            'correo' => $validated['correo'] ?? null,
            'telefono' => $validated['telefono'] ?? null,
            'profesion' => $validated['profesion'] ?? null,
            'especialidad' => $validated['especialidad'] ?? null,
            'tiene_maestria' => $request->boolean('tiene_maestria'),
            'tiene_diplomado' => $request->boolean('tiene_diplomado'),
            'estado' => $validated['estado'] ?? $docente?->estado ?? 'ACTIVO',
        ];
    }

    protected function usuariosDocenteDisponibles(?Docente $docente = null)
    {
        return User::query()
            ->with('rol')
            ->where('estado', 'ACTIVO')
            ->whereHas('rol', fn ($query) => $query->whereRaw('LOWER(nombre) = ?', ['docente']))
            ->where(function ($query) use ($docente): void {
                $query->whereDoesntHave('docente');

                if ($docente?->usuario_id) {
                    $query->orWhere('id', $docente->usuario_id);
                }
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
    }
}
