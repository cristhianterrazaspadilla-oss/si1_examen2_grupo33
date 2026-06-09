<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Paquete: Autenticación, Usuarios y Seguridad
 * Caso de Uso: CU3 (Administrar Usuarios y Roles - Sección Usuarios)
 * 
 * Gestiona el ciclo de vida de los usuarios institucionales (Administradores, Coordinadores, Docentes y Postulantes).
 * Registra auditorías automáticas a través de BitacoraHelper en la creación, edición, asignación de roles 
 * y cambios de estado (activar/desactivar).
 */
class UsuarioController extends Controller
{
    // Controlador del caso de uso: CU3 Administrar Usuarios y Roles
    public function index(): View
    {
        $usuarios = User::with('rol')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(10);

        return view('autenticacion_usuarios_seguridad.usuarios.index', compact('usuarios'));
    }

    public function create(): View
    {
        $roles = Role::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        return view('autenticacion_usuarios_seguridad.usuarios.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'rol_id' => ['required', 'exists:roles,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'ci' => ['required', 'string', 'max:30', 'unique:usuarios,ci'],
            'correo' => ['required', 'email', 'max:150', 'unique:usuarios,correo'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $usuario = User::create($validated);

        BitacoraHelper::registrar(
            'CREAR_USUARIO',
            'Usuarios',
            'Se creo el usuario ' . $usuario->correo . '.'
        );

        return redirect()
            ->route('autenticacion-usuarios-seguridad.usuarios.show', $usuario)
            ->with('success', 'Usuario creado correctamente.');
    }

    public function show(User $usuario): View
    {
        $usuario->load('rol');

        return view('autenticacion_usuarios_seguridad.usuarios.show', compact('usuario'));
    }

    public function edit(User $usuario): View
    {
        $roles = Role::query()
            ->where(function ($query) use ($usuario): void {
                $query->where('estado', 'ACTIVO')
                    ->orWhere('id', $usuario->rol_id);
            })
            ->orderBy('nombre')
            ->get();

        return view('autenticacion_usuarios_seguridad.usuarios.edit', compact('usuario', 'roles'));
    }

    public function update(Request $request, User $usuario): RedirectResponse
    {
        $estadoAnterior = $usuario->estado;
        $rolAnterior = $usuario->rol_id;
        $correoAnterior = $usuario->correo;

        $validated = $request->validate([
            'rol_id' => ['required', 'exists:roles,id'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'ci' => ['required', 'string', 'max:30', Rule::unique('usuarios', 'ci')->ignore($usuario->id)],
            'correo' => ['required', 'email', 'max:150', Rule::unique('usuarios', 'correo')->ignore($usuario->id)],
            'telefono' => ['nullable', 'string', 'max:30'],
            'password' => ['nullable', 'string', 'min:8'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $usuario->update($validated);

        BitacoraHelper::registrar(
            'ACTUALIZAR_USUARIO',
            'Usuarios',
            'Se actualizo el usuario ' . $usuario->correo . '.'
        );

        if ((int) $rolAnterior !== (int) $usuario->rol_id) {
            BitacoraHelper::registrar(
                'ASIGNAR_ROL',
                'Usuarios',
                'Se cambio el rol del usuario ' . $usuario->correo . '.'
            );
        }

        if ($estadoAnterior === 'INACTIVO' && $usuario->estado === 'ACTIVO') {
            BitacoraHelper::registrar(
                'ACTIVAR_USUARIO',
                'Usuarios',
                'Se activo el usuario ' . $usuario->correo . '.'
            );
        } elseif ($estadoAnterior === 'ACTIVO' && $usuario->estado === 'INACTIVO') {
            BitacoraHelper::registrar(
                'DESACTIVAR_USUARIO',
                'Usuarios',
                'Se desactivo el usuario ' . $usuario->correo . '.'
            );
        } elseif ($correoAnterior !== $usuario->correo) {
            BitacoraHelper::registrar(
                'ACTUALIZAR_USUARIO',
                'Usuarios',
                'El usuario actualizo su identificacion de correo a ' . $usuario->correo . '.'
            );
        }

        return redirect()
            ->route('autenticacion-usuarios-seguridad.usuarios.show', $usuario)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $usuario->update(['estado' => 'INACTIVO']);

        BitacoraHelper::registrar(
            'DESACTIVAR_USUARIO',
            'Usuarios',
            'Se desactivo el usuario ' . $usuario->correo . '.'
        );

        return redirect()
            ->route('autenticacion-usuarios-seguridad.usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }
}
