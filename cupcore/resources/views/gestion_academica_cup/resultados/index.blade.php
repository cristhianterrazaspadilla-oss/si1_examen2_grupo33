@extends('layouts.app')

@section('title', 'CU15 Gestionar Resultados de Admision | CUPCore')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="CU15 Gestionar Resultados de Admision" subtitle="Calcula, consulta y administra resultados finales de admision a partir de notas completas, opciones de carrera y cupos disponibles." />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-academica-cup.resultados.pendientes') }}" class="btn btn-outline">Pendientes</a>
            <form method="POST" action="{{ route('gestion-academica-cup.resultados.masivo') }}">
                @csrf
                <button type="submit" class="btn btn-info">Generacion masiva</button>
            </form>
            <a href="{{ route('gestion-academica-cup.resultados.generar') }}" class="btn btn-primary">Generar resultado</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    @if (session('batch_errors'))
        <div class="mb-6 space-y-2">
            @foreach (session('batch_errors') as $batchError)
                <x-alert type="error" :message="$batchError" />
            @endforeach
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="mb-6 grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
        <x-card title="Total resultados"><p class="text-3xl font-semibold text-white">{{ $totales['total'] }}</p></x-card>
        <x-card title="Aprobados"><p class="text-3xl font-semibold text-white">{{ $totales['aprobados'] }}</p></x-card>
        <x-card title="Reprobados"><p class="text-3xl font-semibold text-white">{{ $totales['reprobados'] }}</p></x-card>
        <x-card title="Sin asignacion"><p class="text-3xl font-semibold text-white">{{ $totales['sin_asignacion'] }}</p></x-card>
    </div>

    <x-card title="Filtros de resultados">
        <form method="GET" action="{{ route('gestion-academica-cup.resultados.index') }}" class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($formOptions['gestiones'] as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($filters['gestion'] === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado resultado</span>
                <select name="estado_resultado" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach (['PENDIENTE', 'APROBADO', 'REPROBADO'] as $estadoOption)
                        <option value="{{ $estadoOption }}" @selected($filters['estado_resultado'] === $estadoOption)>{{ $estadoOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Tipo asignacion</span>
                <select name="tipo_asignacion" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach (['PRIMERA_OPCION', 'SEGUNDA_OPCION', 'SIN_ASIGNACION'] as $tipoOption)
                        <option value="{{ $tipoOption }}" @selected($filters['tipo_asignacion'] === $tipoOption)>{{ $tipoOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Carrera asignada</span>
                <select name="carrera_asignada_id" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($formOptions['carreras'] as $carrera)
                        <option value="{{ $carrera->id }}" @selected((string) $filters['carrera_asignada_id'] === (string) $carrera->id)>{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Postulante o CI</span>
                <input type="text" name="search" value="{{ $filters['search'] }}" class="input input-bordered w-full" placeholder="CI, nombres o apellidos">
            </label>
            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-5 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.resultados.index') }}" class="btn btn-outline w-full sm:w-auto">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de resultados">
        <div class="overflow-x-auto w-full">
            <table class="table min-w-[1200px]">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Grupo</th>
                        <th>Gestion</th>
                        <th>Promedio final</th>
                        <th>Estado</th>
                        <th>Carrera asignada</th>
                        <th>Tipo asignacion</th>
                        <th>Fecha</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resultados as $resultado)
                        @php
                            $grupoActivo = $grupoMap[$resultado->postulante_id] ?? null;
                        @endphp
                        <tr>
                            <td>{{ trim(($resultado->postulante?->nombres ?? '') . ' ' . ($resultado->postulante?->apellidos ?? '')) ?: 'Sin postulante' }}</td>
                            <td>{{ $resultado->postulante?->ci ?: 'Sin CI' }}</td>
                            <td>{{ $grupoActivo?->grupo?->nombre ?: 'Sin grupo activo' }}</td>
                            <td>{{ $grupoActivo?->grupo?->gestion ?: 'Sin gestion' }}</td>
                            <td><span class="badge badge-info">{{ $resultado->promedio_final !== null ? number_format((float) $resultado->promedio_final, 2, '.', '') : 'Sin promedio' }}</span></td>
                            <td><span class="badge {{ $resultado->estado_resultado === 'APROBADO' ? 'badge-success' : ($resultado->estado_resultado === 'REPROBADO' ? 'badge-error' : 'badge-warning') }}">{{ $resultado->estado_resultado }}</span></td>
                            <td>{{ $resultado->carreraAsignada?->nombre ?: 'Sin carrera asignada' }}</td>
                            <td>{{ $resultado->tipo_asignacion }}</td>
                            <td>{{ $resultado->fecha_resultado?->format('Y-m-d H:i') ?: 'Sin fecha' }}</td>
                            <td>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.resultados.show', $resultado) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.resultados.edit', $resultado) }}" class="btn btn-sm btn-info">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="alert">
                                    <span>No existen resultados con los filtros actuales.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $resultados->links() }}</div>
    </x-card>
@endsection
