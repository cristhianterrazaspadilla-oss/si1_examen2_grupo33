<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GrupoPostulanteController extends Controller
{
    // Controlador base del caso de uso: CU10 Organizar Grupos Académicos
    public function index(): View
    {
        return view('gestion_academica_cup.grupos.index');
    }

    public function create(): View
    {
        return view('gestion_academica_cup.grupos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('gestion-academica-cup.grupo-postulantes.index');
    }

    public function show(string $id): View
    {
        return view('gestion_academica_cup.grupos.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('gestion_academica_cup.grupos.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('gestion-academica-cup.grupo-postulantes.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('gestion-academica-cup.grupo-postulantes.index');
    }
}