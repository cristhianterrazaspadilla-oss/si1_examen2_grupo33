<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Support\BitacoraHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Paquete: Autenticación, Usuarios y Seguridad
 * Casos de Uso: CU1 (Iniciar Sesión) y CU2 (Cerrar Sesión)
 * 
 * Implementa la autenticación nativa de Laravel para credenciales de usuarios con estado ACTIVO.
 * Aplica regeneración de ID de sesión tras el login exitoso para prevenir ataques de Session Fixation.
 */
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

        if (auth()->user()?->rol?->estado !== 'ACTIVO') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()
                ->withInput($request->only('correo'))
                ->withErrors(['correo' => 'El rol de esta cuenta se encuentra inactivo.']);
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
