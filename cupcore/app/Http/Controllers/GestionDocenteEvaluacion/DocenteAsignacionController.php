<?php

namespace App\Http\Controllers\GestionDocenteEvaluacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteAsignacionController extends Controller
{
    // Controlador base del caso de uso: CU12 Gestionar Docentes y Asignaciones
    public function index(): View
    {
        return view('gestion_docente_evaluacion.asignaciones.index');
    }

    public function create(): View
    {
        return view('gestion_docente_evaluacion.asignaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asignaciones.index');
    }

    public function show(string $id): View
    {
        return view('gestion_docente_evaluacion.asignaciones.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_docente_evaluacion.asignaciones.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asignaciones.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asignaciones.index');
    }
}