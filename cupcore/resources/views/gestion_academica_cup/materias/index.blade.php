@extends('layouts.app')

@section('title', 'CU9 Administrar Materias y Evaluaciones | CUPCore')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <x-page-title title="Administrar Materias y Evaluaciones" subtitle="CU9 del paquete Gestion Academica del CUP. Cada materia debe quedar configurada con tres evaluaciones activas 30/30/40." />
        <a href="{{ route('gestion-academica-cup.materias.create') }}" class="btn btn-primary shrink-0">Nueva materia</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Busqueda y filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.materias.index') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-4">
            <label class="form-control md:col-span-3">
                <span class="label-text">Buscar por nombre, codigo o descripcion</span>
                <input type="text" name="search" value="{{ $search }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Estado</span>
                <select name="estado" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                        <option value="{{ $estadoOption }}" @selected($estado === $estadoOption)>{{ $estadoOption }}</option>
                    @endforeach
                </select>
            </label>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('gestion-academica-cup.materias.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de materias">
        <div class="overflow-x-auto">
            <table class="table min-w-[900px]">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Materia</th>
                        <th>Estado</th>
                        <th>Evaluaciones activas</th>
                        <th>Configuracion</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materias as $materia)
                        @php
                            $evaluacionesActivas = (int) ($materia->evaluaciones_activas_count ?? 0);
                            $porcentajeTotal = (float) ($materia->porcentaje_activo_total ?? 0);
                            $configurada = $evaluacionesActivas === 3 && abs($porcentajeTotal - 100.0) < 0.0001;
                        @endphp
                        <tr>
                            <td>{{ $materia->codigo ?: 'Sin codigo' }}</td>
                            <td>
                                <div class="font-medium text-white">{{ $materia->nombre }}</div>
                                <div class="text-xs text-base-content/70">{{ $materia->descripcion ?: 'Sin descripcion' }}</div>
                            </td>
                            <td>
                                <span class="badge {{ $materia->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $materia->estado }}</span>
                            </td>
                            <td>{{ $evaluacionesActivas }} / 3</td>
                            <td>
                                <span class="badge {{ $configurada ? 'badge-success' : 'badge-warning' }}">
                                    {{ $configurada ? 'Configurada' : 'Pendiente' }}
                                </span>
                                <div class="mt-2 text-xs text-base-content/70">Suma activa: {{ rtrim(rtrim(number_format($porcentajeTotal, 2, '.', ''), '0'), '.') }}%</div>
                            </td>
                            <td>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.materias.show', $materia) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.materias.edit', $materia) }}" class="btn btn-sm btn-info">Editar</a>
                                    <a href="{{ route('gestion-academica-cup.materias.evaluaciones.create', $materia) }}" class="btn btn-sm btn-primary">Evaluaciones</a>
                                    <form method="POST" action="{{ route('gestion-academica-cup.materias.destroy', $materia) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar esta materia?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="alert">
                                    <span>No existen materias registradas con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $materias->links() }}
        </div>
    </x-card>
@endsection
