<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\CupoCarrera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CupoCarreraController extends Controller
{
    // Controlador del caso de uso: CU8 Administrar Carreras y Cupos
    protected function getGestionesAcademicas(): array
    {
        return [
            '1-2026',
            '2-2026',
            '1-2027',
            '2-2027',
            '1-2028',
            '2-2028',
        ];
    }

    public function index(): View
    {
        $cupos = CupoCarrera::with('carrera')
            ->orderByDesc('gestion')
            ->paginate(10);

        return view('gestion_postulantes_admision.cupos.index', compact('cupos'));
    }

    public function create(): View
    {
        $carreras = Carrera::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        $gestionesAcademicas = $this->getGestionesAcademicas();

        return view('gestion_postulantes_admision.cupos.create', compact('carreras', 'gestionesAcademicas'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'carrera_id' => ['required', 'exists:carreras,id'],
            'gestion' => [
                'required',
                'string',
                'max:10',
                'regex:/^[12]-[0-9]{4}$/',
                Rule::unique('cupos_carrera')->where(fn ($query) => $query
                    ->where('carrera_id', $request->input('carrera_id'))
                    ->where('gestion', $request->input('gestion'))),
            ],
            'cupo_total' => ['required', 'integer', 'min:0'],
            'cupo_disponible' => ['required', 'integer', 'min:0', 'lte:cupo_total'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $cupo = CupoCarrera::create([
            'carrera_id' => $validated['carrera_id'],
            'gestion' => $validated['gestion'],
            'cupo_maximo' => $validated['cupo_total'],
            'cupos_disponibles' => $validated['cupo_disponible'],
            'cupos_ocupados' => max(0, $validated['cupo_total'] - $validated['cupo_disponible']),
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('gestion-postulantes-admision.cupos.show', $cupo)
            ->with('success', 'Cupo creado correctamente.');
    }

    public function show(CupoCarrera $cupo): View
    {
        $cupo->load('carrera');

        return view('gestion_postulantes_admision.cupos.show', compact('cupo'));
    }

    public function edit(CupoCarrera $cupo): View
    {
        $carreras = Carrera::query()
            ->where(fn ($query) => $query
                ->where('estado', 'ACTIVO')
                ->orWhere('id', $cupo->carrera_id))
            ->orderBy('nombre')
            ->get();

        $gestionesAcademicas = $this->getGestionesAcademicas();

        return view('gestion_postulantes_admision.cupos.edit', compact('cupo', 'carreras', 'gestionesAcademicas'));
    }

    public function update(Request $request, CupoCarrera $cupo): RedirectResponse
    {
        $validated = $request->validate([
            'carrera_id' => ['required', 'exists:carreras,id'],
            'gestion' => [
                'required',
                'string',
                'max:10',
                'regex:/^[12]-[0-9]{4}$/',
                Rule::unique('cupos_carrera')
                    ->ignore($cupo->id)
                    ->where(fn ($query) => $query
                        ->where('carrera_id', $request->input('carrera_id'))
                        ->where('gestion', $request->input('gestion'))),
            ],
            'cupo_total' => ['required', 'integer', 'min:0'],
            'cupo_disponible' => ['required', 'integer', 'min:0', 'lte:cupo_total'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $cupo->update([
            'carrera_id' => $validated['carrera_id'],
            'gestion' => $validated['gestion'],
            'cupo_maximo' => $validated['cupo_total'],
            'cupos_disponibles' => $validated['cupo_disponible'],
            'cupos_ocupados' => max(0, $validated['cupo_total'] - $validated['cupo_disponible']),
            'estado' => $validated['estado'],
        ]);

        return redirect()
            ->route('gestion-postulantes-admision.cupos.show', $cupo)
            ->with('success', 'Cupo actualizado correctamente.');
    }

    public function destroy(CupoCarrera $cupo): RedirectResponse
    {
        $cupo->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('gestion-postulantes-admision.cupos.index')
            ->with('success', 'Cupo desactivado correctamente.');
    }
}
