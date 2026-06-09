<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Support\BitacoraHelper;
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
            BitacoraHelper::registrar(
                'LOGIN_FALLIDO',
                'Autenticacion',
                'Intento fallido de inicio de sesion para correo ' . $credentials['correo'] . '.',
                null
            );

            return back()
                ->withErrors([
                    'correo' => 'Las credenciales ingresadas no son correctas o el usuario no esta activo.',
                ])
                ->onlyInput('correo');
        }

        $request->session()->regenerate();

        BitacoraHelper::registrar(
            'INICIAR_SESION',
            'Autenticacion',
            'Inicio de sesion correcto para el usuario ' . (string) auth()->user()?->correo . '.'
        );

        return redirect()
            ->route('dashboard')
            ->with('success', 'Inicio de sesion correcto.');
    }

    public function logout(Request $request)
    {
        $correo = (string) auth()->user()?->correo;
        $usuarioId = auth()->id();

        BitacoraHelper::registrar(
            'CERRAR_SESION',
            'Autenticacion',
            'Cierre de sesion del usuario ' . $correo . '.',
            $usuarioId
        );

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Sesion cerrada correctamente.');
    }
}
