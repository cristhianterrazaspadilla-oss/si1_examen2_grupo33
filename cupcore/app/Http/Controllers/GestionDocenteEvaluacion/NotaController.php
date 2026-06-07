<?php

namespace App\Http\Controllers\GestionDocenteEvaluacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotaController extends Controller
{
    // Controlador base del caso de uso: CU14 Gestionar Notas y Seguimiento Académico
    public function index(): View
    {
        return view('gestion_docente_evaluacion.notas.index');
    }

    public function create(): View
    {
        return view('gestion_docente_evaluacion.notas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.notas.index');
    }

    public function show(string $id): View
    {
        return view('gestion_docente_evaluacion.notas.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_docente_evaluacion.notas.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.notas.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-docente-evaluacion.notas.index');
    }
}