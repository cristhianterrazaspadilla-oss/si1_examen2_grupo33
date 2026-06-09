@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Usuarios')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <x-page-title title="Usuarios" subtitle="CU3 Administrar Usuarios y Roles" />
        <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.create') }}" class="btn btn-primary w-full sm:w-auto">
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

    <x-card title="Listado de usuarios">
        <div class="overflow-x-auto">
            <table class="table min-w-[900px]">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>CI</th>
                        <th>Correo</th>
                        <th>Rol</th>
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
                            <td>{{ $usuario->rol?->nombre ?? 'Sin rol' }}</td>
                            <td>
                                <span class="badge {{ $usuario->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $usuario->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.show', $usuario) }}" class="btn btn-sm btn-outline">
                                        Ver
                                    </a>
                                    <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.edit', $usuario) }}" class="btn btn-sm btn-info">
                                        Editar
                                    </a>
                                    <form method="POST" action="{{ route('autenticacion-usuarios-seguridad.usuarios.destroy', $usuario) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas desactivar este usuario?')">
                                            Desactivar
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="alert">
                                    <span>No existen usuarios registrados.</span>
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
