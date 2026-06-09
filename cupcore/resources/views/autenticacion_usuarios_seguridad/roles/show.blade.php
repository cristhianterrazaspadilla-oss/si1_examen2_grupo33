@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Detalle de Rol')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <x-page-title title="Detalle de Rol" subtitle="CU3 Administrar Usuarios y Roles" />
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('autenticacion-usuarios-seguridad.roles.edit', $rol) }}" class="btn btn-info w-full sm:w-auto">Editar</a>
            <a href="{{ route('autenticacion-usuarios-seguridad.roles.index') }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion del rol">
        <div class="detail-grid cols-2">
            <div class="detail-item">
                <p class="detail-item-label">Nombre del rol</p>
                <p class="detail-item-value">{{ $rol->nombre }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Estado</p>
                <div class="detail-item-value">
                    <span class="badge {{ $rol->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                        {{ $rol->estado }}
                    </span>
                </div>
            </div>
            <div class="detail-item md:col-span-2">
                <p class="detail-item-label">Descripcion</p>
                <p class="detail-item-value">{{ $rol->descripcion ?: 'Sin descripcion' }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Usuarios asociados</p>
                <p class="detail-item-value">{{ $rol->usuarios_count }}</p>
            </div>
        </div>
    </x-card>

    <x-card title="Usuarios asociados">
        <div class="overflow-x-auto">
            <table class="table min-w-[800px]">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CI</th>
                        <th>Correo</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($usuarios as $usuario)
                        <tr>
                            <td>{{ $usuario->nombre }} {{ $usuario->apellido }}</td>
                            <td>{{ $usuario->ci }}</td>
                            <td>{{ $usuario->correo }}</td>
                            <td>
                                <span class="badge {{ $usuario->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $usuario->estado }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.show', $usuario) }}" class="btn btn-sm btn-outline">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>Este rol no tiene usuarios asociados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $usuarios->links() }}
        </div>
    </x-card>
@endsection
