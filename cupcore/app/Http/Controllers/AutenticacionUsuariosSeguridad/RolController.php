<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Paquete: Autenticación, Usuarios y Seguridad
 * Caso de Uso: CU3 (Administrar Usuarios y Roles - Sección Roles)
 * 
 * Modela los roles y alcances del sistema (Administrador, Coordinador, Docente, Postulante, Autoridad Académica).
 * Los roles inactivos restringen el acceso a los usuarios que los posean.
 */
class RolController extends Controller
{
    // Controlador del caso de uso: CU3 Administrar Usuarios y Roles
    public function index(): View
    {
        $roles = Role::query()
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->paginate(10);

        return view('autenticacion_usuarios_seguridad.roles.index', compact('roles'));
    }

    public function create(): View
    {
        return view('autenticacion_usuarios_seguridad.roles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', 'unique:roles,nombre'],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $rol = Role::create($validated);

        return redirect()
            ->route('autenticacion-usuarios-seguridad.roles.show', $rol)
            ->with('success', 'Rol creado correctamente.');
    }

    public function show(Role $role): View
    {
        $role->loadCount('usuarios');
        $usuarios = $role->usuarios()
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->paginate(10);

        return view('autenticacion_usuarios_seguridad.roles.show', [
            'rol' => $role,
            'usuarios' => $usuarios,
        ]);
    }

    public function edit(Role $role): View
    {
        return view('autenticacion_usuarios_seguridad.roles.edit', ['rol' => $role]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100', Rule::unique('roles', 'nombre')->ignore($role->id)],
            'descripcion' => ['nullable', 'string', 'max:255'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $role->update($validated);

        return redirect()
            ->route('autenticacion-usuarios-seguridad.roles.show', $role)
            ->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $role->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('autenticacion-usuarios-seguridad.roles.index')
            ->with('success', 'Rol desactivado correctamente.');
    }
}
