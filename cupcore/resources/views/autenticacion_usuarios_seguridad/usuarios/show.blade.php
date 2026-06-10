@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Detalle de Usuario')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <x-page-title title="Detalle de Usuario" subtitle="CU3 Administrar Usuarios y Roles" />
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.edit', $usuario) }}" class="btn btn-info w-full sm:w-auto">Editar</a>
            <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.index') }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion del usuario">
        <div class="detail-grid cols-2">
            <div class="detail-item">
                <p class="detail-item-label">Nombre completo</p>
                <p class="detail-item-value">{{ $usuario->nombre }} {{ $usuario->apellido }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Rol</p>
                <p class="detail-item-value">{{ $usuario->rol?->nombre ?? 'Sin rol' }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">CI</p>
                <p class="detail-item-value">{{ $usuario->ci }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Correo</p>
                <p class="detail-item-value">{{ $usuario->correo }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Telefono</p>
                <p class="detail-item-value">{{ $usuario->telefono ?: 'Sin registro' }}</p>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Estado</p>
                <div class="detail-item-value">
                    <span class="badge {{ $usuario->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                        {{ $usuario->estado }}
                    </span>
                </div>
            </div>
        </div>
    </x-card>
@endsection
