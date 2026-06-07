<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('autenticacion_usuarios_seguridad.auth.index');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        $loginOk = Auth::attempt([
            'correo' => $credentials['correo'],
            'password' => $credentials['password'],
            'estado' => 'ACTIVO',
        ], $remember);

        if (! $loginOk) {
            return back()
                ->withErrors([
                    'correo' => 'Las credenciales ingresadas no son correctas o el usuario no esta activo.',
                ])
                ->onlyInput('correo');
        }

        $request->session()->regenerate();

        return redirect()
            ->route('dashboard')
            ->with('success', 'Inicio de sesion correcto.');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Sesion cerrada correctamente.');
    }
}
