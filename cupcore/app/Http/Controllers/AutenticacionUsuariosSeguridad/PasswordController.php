<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PasswordController extends Controller
{
    // Controlador base del caso de uso: CU18 Recuperar Contraseña
    public function index(): View
    {
        return view('autenticacion_usuarios_seguridad.password.index');
    }

    public function create(): View
    {
        return view('autenticacion_usuarios_seguridad.password.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.password.index');
    }

    public function show(string $id): View
    {
        return view('autenticacion_usuarios_seguridad.password.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('autenticacion_usuarios_seguridad.password.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.password.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.password.index');
    }
}