@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Carreras')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Carreras" subtitle="CU8 Administrar Carreras y Cupos" />
        <a href="{{ route('gestion-postulantes-admision.carreras.create') }}" class="btn btn-primary">Nuevo</a>
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

    <x-card title="Listado de carreras">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Código</th>
                        <th>Estado</th>
                        <th>Cantidad de cupos</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($carreras as $carrera)
                        <tr>
                            <td>{{ $carrera->nombre }}</td>
                            <td>{{ $carrera->codigo }}</td>
                            <td>
                                <span class="badge {{ $carrera->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $carrera->estado }}
                                </span>
                            </td>
                            <td>{{ $carrera->cupos_carrera_count }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-postulantes-admision.carreras.show', $carrera) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-postulantes-admision.carreras.edit', $carrera) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-postulantes-admision.carreras.destroy', $carrera) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas desactivar esta carrera?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>No existen carreras registradas.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $carreras->links() }}
        </div>
    </x-card>
@endsection
