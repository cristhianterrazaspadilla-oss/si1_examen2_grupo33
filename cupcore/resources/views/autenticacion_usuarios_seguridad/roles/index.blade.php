@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Roles')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Roles" subtitle="CU3 Administrar Usuarios y Roles" />
        <a href="{{ route('autenticacion-usuarios-seguridad.roles.create') }}" class="btn btn-primary">
            Nuevo
        </a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron errores de validación.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <x-card title="Listado de roles">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Usuarios</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($roles as $rol)
                        <tr>
                            <td>{{ $rol->nombre }}</td>
                            <td>{{ $rol->descripcion ?: 'Sin descripción' }}</td>
                            <td>{{ $rol->usuarios_count }}</td>
                            <td>
                                <span class="badge {{ $rol->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $rol->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('autenticacion-usuarios-seguridad.roles.show', $rol) }}" class="btn btn-sm btn-outline">
                                        Ver
                                    </a>
                                    <a href="{{ route('autenticacion-usuarios-seguridad.roles.edit', $rol) }}" class="btn btn-sm btn-info">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('autenticacion-usuarios-seguridad.roles.destroy', $rol) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas desactivar este rol?')">
                                            Desactivar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>No existen roles registrados.</span>
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
