@extends('layouts.app')

@section('title', 'CU6 Gestionar Requisitos de Admisión | Requisitos')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Requisitos de Admisión" subtitle="CU6 Gestionar Requisitos de Admisión" />
        <div class="flex gap-2">
            <a href="{{ route('gestion-postulantes-admision.requisitos-postulantes.index') }}" class="btn btn-outline">Validar requisitos</a>
            <a href="{{ route('gestion-postulantes-admision.requisitos.create') }}" class="btn btn-primary">Nuevo requisito</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Búsqueda de requisitos">
        <form method="GET" action="{{ route('gestion-postulantes-admision.requisitos.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ $search }}" class="input input-bordered w-full max-w-xl" placeholder="Buscar por nombre o descripción">
            <button type="submit" class="btn btn-primary">Buscar</button>
            <a href="{{ route('gestion-postulantes-admision.requisitos.index') }}" class="btn btn-outline">Limpiar</a>
        </form>
    </x-card>

    <x-card title="Catálogo de requisitos">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Obligatorio</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requisitos as $requisito)
                        <tr>
                            <td>{{ $requisito->nombre }}</td>
                            <td>
                                <span class="badge {{ $requisito->obligatorio ? 'badge-warning' : 'badge-info' }}">
                                    {{ $requisito->obligatorio ? 'Sí' : 'No' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $requisito->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $requisito->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-postulantes-admision.requisitos.show', $requisito) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-postulantes-admision.requisitos.edit', $requisito) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-postulantes-admision.requisitos.destroy', $requisito) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas desactivar este requisito?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="alert">
                                    <span>No existen requisitos registrados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $requisitos->links() }}</div>
    </x-card>
@endsection
