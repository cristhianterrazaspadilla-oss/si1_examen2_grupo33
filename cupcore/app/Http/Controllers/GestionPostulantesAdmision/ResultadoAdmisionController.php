<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ResultadoAdmisionController extends Controller
{
    // Controlador base del caso de uso: CU15 Gestionar Resultados de Admisión
    public function index(): View
    {
        return view('gestion_postulantes_admision.resultados.index');
    }

    public function create(): View
    {
        return view('gestion_postulantes_admision.resultados.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.resultados.index');
    }

    public function show(string $id): View
    {
        return view('gestion_postulantes_admision.resultados.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_postulantes_admision.resultados.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.resultados.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-postulantes-admision.resultados.index');
    }
}