<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\View\View;

/**
 * Permite consultar los roles institucionales fijos y sus usuarios asociados.
 */
class RolController extends Controller
{
    public function index(): View
    {
        $roles = Role::query()
            ->institutional()
            ->withCount('usuarios')
            ->orderBy('nombre')
            ->paginate(10);

        return view('autenticacion_usuarios_seguridad.roles.index', compact('roles'));
    }

    public function show(Role $role): View
    {
        abort_unless(in_array($role->nombre, Role::INSTITUTIONAL_NAMES, true), 404);

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
}
