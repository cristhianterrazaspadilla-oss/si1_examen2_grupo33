<?php

namespace App\Http\Controllers\GestionDocenteEvaluacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AsistenciaDocenteController extends Controller
{
    // Controlador base del caso de uso: CU13 Registrar Asistencia Docente
    public function index(): View
    {
        return view('gestion_docente_evaluacion.asistencias.index');
    }

    public function create(): View
    {
        return view('gestion_docente_evaluacion.asistencias.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asistencias.index');
    }

    public function show(string $id): View
    {
        return view('gestion_docente_evaluacion.asistencias.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_docente_evaluacion.asistencias.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asistencias.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.asistencias.index');
    }
}