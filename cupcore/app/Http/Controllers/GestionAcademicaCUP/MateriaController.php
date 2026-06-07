<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MateriaController extends Controller
{
    // Controlador del caso de uso: CU9 Administrar Materias y Evaluaciones
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estado = $request->string('estado')->toString();

        $materias = Materia::query()
            ->withCount([
                'evaluaciones as evaluaciones_activas_count' => fn ($query) => $query->where('estado', 'ACTIVO'),
            ])
            ->withSum([
                'evaluaciones as porcentaje_activo_total' => fn ($query) => $query->where('estado', 'ACTIVO'),
            ], 'porcentaje')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_academica_cup.materias.index', [
            'materias' => $materias,
            'search' => $search,
            'estado' => $estado,
        ]);
    }

    public function create(): View
    {
        return view('gestion_academica_cup.materias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'max:30', 'unique:materias,codigo'],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $materia = Materia::create([
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'] ?? null,
            'descripcion' => $validated['descripcion'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);

        return redirect()
            ->route('gestion-academica-cup.materias.show', $materia)
            ->with('success', 'Materia creada correctamente.');
    }

    public function show(Materia $materia): View
    {
        $materia->load([
            'evaluaciones' => fn ($query) => $query->orderBy('numero_evaluacion')->orderBy('id'),
        ]);

        [$evaluacionesActivasCount, $porcentajeActivoTotal, $configurada] = $this->buildResumen($materia);

        return view('gestion_academica_cup.materias.show', [
            'materia' => $materia,
            'evaluacionesActivasCount' => $evaluacionesActivasCount,
            'porcentajeActivoTotal' => $porcentajeActivoTotal,
            'configurada' => $configurada,
        ]);
    }

    public function edit(Materia $materia): View
    {
        return view('gestion_academica_cup.materias.edit', compact('materia'));
    }

    public function update(Request $request, Materia $materia): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'max:30', Rule::unique('materias', 'codigo')->ignore($materia->id)],
            'descripcion' => ['nullable', 'string'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $materia->update($validated);

        return redirect()
            ->route('gestion-academica-cup.materias.show', $materia)
            ->with('success', 'Materia actualizada correctamente.');
    }

    public function destroy(Materia $materia): RedirectResponse
    {
        $materia->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('gestion-academica-cup.materias.index')
            ->with('success', 'Materia desactivada correctamente.');
    }

    protected function buildResumen(Materia $materia): array
    {
        $evaluacionesActivas = $materia->evaluaciones->where('estado', 'ACTIVO');
        $evaluacionesActivasCount = $evaluacionesActivas->count();
        $porcentajeActivoTotal = (float) $evaluacionesActivas->sum('porcentaje');
        $configurada = $evaluacionesActivasCount === 3 && abs($porcentajeActivoTotal - 100.0) < 0.0001;

        return [$evaluacionesActivasCount, $porcentajeActivoTotal, $configurada];
    }
}
