<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Evaluacion;
use App\Models\Materia;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EvaluacionController extends Controller
{
    // Controlador del caso de uso: CU9 Administrar Materias y Evaluaciones
    public function create(Materia $materia): View|RedirectResponse
    {
        $materia->loadCount([
            'evaluaciones as evaluaciones_activas_count' => fn ($query) => $query->where('estado', 'ACTIVO'),
        ]);

        if ($materia->evaluaciones_activas_count >= 3) {
            return redirect()
                ->route('gestion-academica-cup.materias.show', $materia)
                ->withErrors(['evaluacion' => 'La materia ya tiene las tres evaluaciones activas requeridas.']);
        }

        return view('gestion_academica_cup.evaluaciones.create', [
            'materia' => $materia,
            'porcentajesEsperados' => $this->porcentajesEsperados(),
        ]);
    }

    public function store(Request $request, Materia $materia): RedirectResponse
    {
        $validated = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'numero_evaluacion' => ['required', 'integer', Rule::in([1, 2, 3])],
            'porcentaje' => ['required', 'numeric'],
            'fecha_evaluacion' => ['nullable', 'date'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        if ((int) $validated['materia_id'] !== (int) $materia->id) {
            throw ValidationException::withMessages([
                'materia_id' => ['La materia seleccionada no coincide con la ruta actual.'],
            ]);
        }

        $this->validateEvaluacionRules(
            materia: $materia,
            numeroEvaluacion: (int) $validated['numero_evaluacion'],
            porcentaje: (float) $validated['porcentaje'],
            estado: $validated['estado'] ?? 'ACTIVO',
        );

        $evaluacion = Evaluacion::create([
            'materia_id' => $materia->id,
            'nombre' => $validated['nombre'],
            'numero_evaluacion' => $validated['numero_evaluacion'],
            'porcentaje' => $validated['porcentaje'],
            'fecha_evaluacion' => $validated['fecha_evaluacion'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);
        BitacoraHelper::registrar(
            'CREAR_EVALUACION',
            'Evaluaciones',
            'Se creo la evaluacion ' . $evaluacion->nombre . ' para la materia ' . $materia->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.materias.show', $materia)
            ->with('success', 'Evaluacion creada correctamente.');
    }

    public function edit(Evaluacion $evaluacion): View
    {
        $evaluacion->load('materia');

        return view('gestion_academica_cup.evaluaciones.edit', [
            'evaluacion' => $evaluacion,
            'materia' => $evaluacion->materia,
            'porcentajesEsperados' => $this->porcentajesEsperados(),
        ]);
    }

    public function update(Request $request, Evaluacion $evaluacion): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'numero_evaluacion' => ['required', 'integer', Rule::in([1, 2, 3])],
            'porcentaje' => ['required', 'numeric'],
            'fecha_evaluacion' => ['nullable', 'date'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $evaluacion->load('materia');

        $this->validateEvaluacionRules(
            materia: $evaluacion->materia,
            numeroEvaluacion: (int) $validated['numero_evaluacion'],
            porcentaje: (float) $validated['porcentaje'],
            estado: $validated['estado'],
            currentEvaluacion: $evaluacion,
        );

        $evaluacion->update($validated);
        BitacoraHelper::registrar(
            'ACTUALIZAR_EVALUACION',
            'Evaluaciones',
            'Se actualizo la evaluacion ' . $evaluacion->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.materias.show', $evaluacion->materia)
            ->with('success', 'Evaluacion actualizada correctamente.');
    }

    public function destroy(Evaluacion $evaluacion): RedirectResponse
    {
        $evaluacion->load('materia');
        $evaluacion->update(['estado' => 'INACTIVO']);
        BitacoraHelper::registrar(
            'DESACTIVAR_EVALUACION',
            'Evaluaciones',
            'Se desactivo la evaluacion ' . $evaluacion->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.materias.show', $evaluacion->materia)
            ->with('success', 'Evaluacion desactivada correctamente.');
    }

    protected function porcentajesEsperados(): array
    {
        return [
            1 => 30.0,
            2 => 30.0,
            3 => 40.0,
        ];
    }

    protected function validateEvaluacionRules(
        Materia $materia,
        int $numeroEvaluacion,
        float $porcentaje,
        string $estado,
        ?Evaluacion $currentEvaluacion = null,
    ): void {
        $porcentajesEsperados = $this->porcentajesEsperados();
        $expectedPercentage = $porcentajesEsperados[$numeroEvaluacion] ?? null;

        if ($expectedPercentage === null || abs($porcentaje - $expectedPercentage) > 0.0001) {
            throw ValidationException::withMessages([
                'porcentaje' => ['El porcentaje debe ser ' . rtrim(rtrim(number_format($expectedPercentage ?? 0, 2, '.', ''), '0'), '.') . '% para la evaluacion ' . $numeroEvaluacion . '.'],
            ]);
        }

        $activeEvaluaciones = Evaluacion::query()
            ->where('materia_id', $materia->id)
            ->where('estado', 'ACTIVO')
            ->when($currentEvaluacion, fn ($query) => $query->where('id', '!=', $currentEvaluacion->id))
            ->get();

        if ($estado === 'ACTIVO' && $activeEvaluaciones->count() >= 3) {
            throw ValidationException::withMessages([
                'numero_evaluacion' => ['No se permiten mas de tres evaluaciones activas por materia.'],
            ]);
        }

        $numeroDuplicado = $activeEvaluaciones->contains(fn (Evaluacion $evaluacion) => (int) $evaluacion->numero_evaluacion === $numeroEvaluacion);

        if ($estado === 'ACTIVO' && $numeroDuplicado) {
            throw ValidationException::withMessages([
                'numero_evaluacion' => ['No se permite repetir numero_evaluacion en evaluaciones activas de la misma materia.'],
            ]);
        }
    }
}
