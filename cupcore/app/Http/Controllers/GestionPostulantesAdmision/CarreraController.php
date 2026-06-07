<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CarreraController extends Controller
{
    // Controlador del caso de uso: CU8 Administrar Carreras y Cupos
    public function index(): View
    {
        $carreras = Carrera::query()
            ->withCount('cuposCarrera')
            ->orderBy('nombre')
            ->paginate(10);

        return view('gestion_postulantes_admision.carreras.index', compact('carreras'));
    }

    public function create(): View
    {
        return view('gestion_postulantes_admision.carreras.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150', 'unique:carreras,nombre'],
            'codigo' => ['required', 'string', 'max:30', 'unique:carreras,codigo'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $carrera = Carrera::create($validated);

        return redirect()
            ->route('gestion-postulantes-admision.carreras.show', $carrera)
            ->with('success', 'Carrera creada correctamente.');
    }

    public function show(Carrera $carrera): View
    {
        $carrera->loadCount('cuposCarrera');
        $cupos = $carrera->cuposCarrera()
            ->orderByDesc('gestion')
            ->paginate(10);

        return view('gestion_postulantes_admision.carreras.show', compact('carrera', 'cupos'));
    }

    public function edit(Carrera $carrera): View
    {
        return view('gestion_postulantes_admision.carreras.edit', compact('carrera'));
    }

    public function update(Request $request, Carrera $carrera): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('carreras', 'nombre')->ignore($carrera->id)],
            'codigo' => ['required', 'string', 'max:30', Rule::unique('carreras', 'codigo')->ignore($carrera->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $carrera->update($validated);

        return redirect()
            ->route('gestion-postulantes-admision.carreras.show', $carrera)
            ->with('success', 'Carrera actualizada correctamente.');
    }

    public function destroy(Carrera $carrera): RedirectResponse
    {
        $carrera->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('gestion-postulantes-admision.carreras.index')
            ->with('success', 'Carrera desactivada correctamente.');
    }
}
