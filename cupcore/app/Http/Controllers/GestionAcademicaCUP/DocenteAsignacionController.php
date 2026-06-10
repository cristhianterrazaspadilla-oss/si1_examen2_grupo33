<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\DocenteAsignacion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * Paquete: Gestión Docente y Evaluación Académica
 * Caso de Uso: CU12 (Gestionar Docentes y Asignaciones - Sección Asignaciones)
 * 
 * NOTA DE ARQUITECTURA: Por motivos de alta cohesión del dominio, este controlador reside en el 
 * namespace de Gestión Académica, favoreciendo las consultas y cruces con el calendario de horarios.
 * Modela la relación N:M entre docentes, materias, grupos y gestiones.
 */
class DocenteAsignacionController extends Controller
{
    public function create(Docente $docente): View
    {
        return view('gestion_academica_cup.docentes.asignar', [
            'docente' => $docente,
            'grupos' => $this->gruposActivos(),
            'materias' => $this->materiasActivas(),
            'gestionesAcademicas' => $this->gestionesAcademicas(),
        ]);
    }

    public function store(Request $request, Docente $docente): RedirectResponse
    {
        $validated = $this->validateAsignacion($request, $docente);

        $asignacion = DocenteAsignacion::create([
            'docente_id' => $docente->id,
            'grupo_id' => $validated['grupo_id'],
            'materia_id' => $validated['materia_id'],
            'gestion' => $validated['gestion'],
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);
        $asignacion->load(['grupo', 'materia']);
        BitacoraHelper::registrar(
            'ASIGNAR_DOCENTE',
            'Asignaciones Docentes',
            'Se asigno docente ' . $docente->nombres . ' ' . $docente->apellidos . ' a ' . ($asignacion->materia?->nombre ?? 'N/D') . ' en ' . ($asignacion->grupo?->nombre ?? 'N/D') . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Asignacion docente creada correctamente.');
    }

    public function edit(DocenteAsignacion $asignacion): View
    {
        $asignacion->load(['docente', 'grupo', 'materia']);

        return view('gestion_academica_cup.asignaciones_docentes.edit', [
            'asignacion' => $asignacion,
            'docente' => $asignacion->docente,
            'grupos' => $this->gruposActivos($asignacion->grupo_id),
            'materias' => $this->materiasActivas($asignacion->materia_id),
            'gestionesAcademicas' => $this->gestionesAcademicas(),
        ]);
    }

    public function update(Request $request, DocenteAsignacion $asignacion): RedirectResponse
    {
        $validated = $this->validateAsignacion($request, $asignacion->docente, $asignacion);

        $asignacion->update([
            'grupo_id' => $validated['grupo_id'],
            'materia_id' => $validated['materia_id'],
            'gestion' => $validated['gestion'],
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);
        $asignacion->load(['grupo', 'materia']);
        BitacoraHelper::registrar(
            'ACTUALIZAR_ASIGNACION_DOCENTE',
            'Asignaciones Docentes',
            'Se actualizo la asignacion docente ' . $asignacion->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $asignacion->docente)
            ->with('success', 'Asignacion docente actualizada correctamente.');
    }

    public function destroy(DocenteAsignacion $asignacion): RedirectResponse
    {
        $docente = $asignacion->docente;

        $asignacion->update(['estado' => 'INACTIVO']);
        BitacoraHelper::registrar(
            'DESACTIVAR_ASIGNACION_DOCENTE',
            'Asignaciones Docentes',
            'Se desactivo la asignacion docente ' . $asignacion->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Asignacion docente desactivada correctamente.');
    }

    public function activar(DocenteAsignacion $asignacion): RedirectResponse
    {
        $docente = $asignacion->docente;
        $grupo = $asignacion->grupo;
        $materia = $asignacion->materia;

        $errors = [];

        if (! $docente || $docente->estado !== 'ACTIVO') {
            $errors['asignacion'] = 'No se puede activar la asignacion porque el docente asociado esta inactivo.';
        } elseif (! $grupo || $grupo->estado !== 'ACTIVO') {
            $errors['asignacion'] = 'No se puede activar la asignacion porque el grupo asociado esta inactivo.';
        } elseif (! $materia || $materia->estado !== 'ACTIVO') {
            $errors['asignacion'] = 'No se puede activar la asignacion porque la materia asociada esta inactiva.';
        } else {
            $duplicada = DocenteAsignacion::query()
                ->where('docente_id', $asignacion->docente_id)
                ->where('grupo_id', $asignacion->grupo_id)
                ->where('materia_id', $asignacion->materia_id)
                ->where('gestion', $asignacion->gestion)
                ->where('estado', 'ACTIVO')
                ->where('id', '!=', $asignacion->id)
                ->exists();

            if ($duplicada) {
                $errors['asignacion'] = 'Ya existe otra asignacion activa con el mismo docente, grupo, materia y gestion.';
            }
        }

        if ($errors !== []) {
            return redirect()
                ->route('gestion-academica-cup.docentes.show', $docente)
                ->withErrors($errors);
        }

        try {
            $updated = DB::table('docente_asignaciones')
                ->where('id', $asignacion->id)
                ->update([
                    'estado' => 'ACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al activar asignacion docente', [
                'asignacion_id' => $asignacion->id,
                'docente_id' => $asignacion->docente_id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.docentes.show', $docente)
                ->withErrors(['asignacion' => 'No se pudo activar la asignación docente. Inténtalo nuevamente.']);
        }

        if ($updated !== 1) {
            return redirect()
                ->route('gestion-academica-cup.docentes.show', $docente)
                ->withErrors(['asignacion' => 'No se pudo activar la asignacion seleccionada.']);
        }

        BitacoraHelper::registrar(
            'ACTIVAR_ASIGNACION_DOCENTE',
            'Asignaciones Docentes',
            'Se activo la asignacion docente ' . $asignacion->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.docentes.show', $docente)
            ->with('success', 'Asignacion docente activada correctamente.');
    }

    protected function validateAsignacion(Request $request, Docente $docente, ?DocenteAsignacion $asignacion = null): array
    {
        $validated = $request->validate([
            'docente_id' => ['required', 'exists:docentes,id'],
            'grupo_id' => ['required', 'exists:grupos,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'gestion' => ['required', 'string', 'max:20', 'regex:/^[12]-[0-9]{4}$/'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $errors = [];

        if ((int) $validated['docente_id'] !== (int) $docente->id) {
            $errors['docente_id'] = 'El docente seleccionado no coincide con la ruta actual.';
        }

        if ($docente->estado !== 'ACTIVO') {
            $errors['docente_id'] = 'Solo se pueden asignar docentes activos.';
        }

        $grupo = Grupo::find($validated['grupo_id']);
        if (! $grupo || $grupo->estado !== 'ACTIVO') {
            $errors['grupo_id'] = 'Solo se pueden asignar grupos activos.';
        }

        $materia = Materia::find($validated['materia_id']);
        if (! $materia || $materia->estado !== 'ACTIVO') {
            $errors['materia_id'] = 'Solo se pueden asignar materias activas.';
        }

        $estadoFinal = $validated['estado'] ?? 'ACTIVO';
        if ($estadoFinal === 'ACTIVO') {
            $duplicada = DocenteAsignacion::query()
                ->where('docente_id', $docente->id)
                ->where('grupo_id', $validated['grupo_id'])
                ->where('materia_id', $validated['materia_id'])
                ->where('gestion', $validated['gestion'])
                ->where('estado', 'ACTIVO')
                ->when($asignacion, fn ($query) => $query->where('id', '!=', $asignacion->id))
                ->exists();

            if ($duplicada) {
                $errors['grupo_id'] = 'Ya existe una asignacion activa con el mismo docente, grupo, materia y gestion.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    protected function gruposActivos(?int $currentGroupId = null)
    {
        return Grupo::query()
            ->where(function ($query) use ($currentGroupId): void {
                $query->where('estado', 'ACTIVO');

                if ($currentGroupId) {
                    $query->orWhere('id', $currentGroupId);
                }
            })
            ->orderByDesc('gestion')
            ->orderBy('nombre')
            ->get();
    }

    protected function materiasActivas(?int $currentMateriaId = null)
    {
        return Materia::query()
            ->where(function ($query) use ($currentMateriaId): void {
                $query->where('estado', 'ACTIVO');

                if ($currentMateriaId) {
                    $query->orWhere('id', $currentMateriaId);
                }
            })
            ->orderBy('nombre')
            ->get();
    }

    protected function gestionesAcademicas(): array
    {
        $currentYear = now()->year;
        $gestiones = [];

        for ($year = $currentYear; $year <= $currentYear + 5; $year++) {
            $gestiones[] = '1-' . $year;
            $gestiones[] = '2-' . $year;
        }

        return $gestiones;
    }
}
