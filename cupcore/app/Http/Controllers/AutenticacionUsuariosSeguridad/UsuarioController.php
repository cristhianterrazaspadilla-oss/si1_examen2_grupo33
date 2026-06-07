<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

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

        return redirect()
            ->route('autenticacion-usuarios-seguridad.usuarios.show', $usuario)
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario): RedirectResponse
    {
        $usuario->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('autenticacion-usuarios-seguridad.usuarios.index')
            ->with('success', 'Usuario desactivado correctamente.');
    }
}
