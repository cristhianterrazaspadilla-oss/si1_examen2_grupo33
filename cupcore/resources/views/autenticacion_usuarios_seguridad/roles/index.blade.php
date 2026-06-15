@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Consulta de Roles')

@section('content')
    <x-page-title title="Roles institucionales" subtitle="Consulta de roles fijos del sistema" />

    <div class="alert alert-info mb-6">
        <span>Los roles del sistema son institucionales y no pueden crearse, editarse ni desactivarse desde el panel.</span>
    </div>

    <x-card title="Listado de roles">
        <div class="overflow-x-auto">
            <table class="table min-w-[800px]">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $rol)
                        <tr>
                            <td>{{ $rol->nombre }}</td>
                            <td>{{ $rol->descripcion ?: 'Sin descripcion' }}</td>
                            <td>{{ $rol->usuarios_count }}</td>
                            <td>
                                <span class="badge {{ $rol->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $rol->estado }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('autenticacion-usuarios-seguridad.roles.show', $rol) }}" class="btn btn-sm btn-outline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>No existen roles institucionales registrados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $roles->links() }}
        </div>
    </x-card>
@endsection
