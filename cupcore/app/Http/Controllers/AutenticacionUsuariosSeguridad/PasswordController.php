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

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        // Flujo demostrativo solicitado: no revela si el correo existe ni envía mensajes reales.
        return redirect()
            ->route('password.demo')
            ->with('success', 'Si el correo está registrado, recibirás instrucciones para recuperar tu contraseña. Esta versión es demostrativa y no envía correos.');
    }

}
