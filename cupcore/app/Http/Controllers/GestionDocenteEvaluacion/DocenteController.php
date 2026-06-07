<?php

namespace App\Http\Controllers\GestionDocenteEvaluacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocenteController extends Controller
{
    // Controlador base del caso de uso: CU12 Gestionar Docentes y Asignaciones
    public function index(): View
    {
        return view('gestion_docente_evaluacion.docentes.index');
    }

    public function create(): View
    {
        return view('gestion_docente_evaluacion.docentes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.docentes.index');
    }

    public function show(string $id): View
    {
        return view('gestion_docente_evaluacion.docentes.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_docente_evaluacion.docentes.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.docentes.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.docentes.index');
    }
}