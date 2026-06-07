@extends('layouts.app')

@section('title', 'CU9 Administrar Materias y Evaluaciones | Detalle de materia')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de Materia" subtitle="Consulta la materia, sus evaluaciones activas y el estado de configuracion academica." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.materias.edit', $materia) }}" class="btn btn-info">Editar materia</a>
            <a href="{{ route('gestion-academica-cup.materias.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron observaciones.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <x-card title="Datos de la materia">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Nombre</p>
                    <p class="mt-2 text-base text-white">{{ $materia->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Codigo</p>
                    <p class="mt-2 text-base text-white">{{ $materia->codigo ?: 'Sin codigo' }}</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Descripcion</p>
                    <p class="mt-2 text-base text-white">{{ $materia->descripcion ?: 'Sin descripcion registrada' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estado</p>
                    <p class="mt-2">
                        <span class="badge {{ $materia->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $materia->estado }}</span>
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Resumen academico">
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Evaluaciones activas</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $evaluacionesActivasCount }} / 3</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Suma de porcentajes</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ rtrim(rtrim(number_format($porcentajeActivoTotal, 2, '.', ''), '0'), '.') }}%</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estado de configuracion</p>
                    <p class="mt-2">
                        <span class="badge {{ $configurada ? 'badge-success' : 'badge-warning' }}">{{ $configurada ? 'Configurada' : 'Pendiente' }}</span>
                    </p>
                </div>

                @if ($evaluacionesActivasCount < 3)
                    <a href="{{ route('gestion-academica-cup.materias.evaluaciones.create', $materia) }}" class="btn btn-primary w-full">Agregar evaluacion</a>
                @else
                    <div class="alert alert-info">
                        <span>La materia ya tiene las tres evaluaciones activas requeridas.</span>
                    </div>
                @endif
            </div>
        </x-card>
    </div>

    <x-card title="Evaluaciones de la materia">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Numero</th>
                        <th>Nombre</th>
                        <th>Porcentaje</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($materia->evaluaciones as $evaluacion)
                        <tr>
                            <td>{{ $evaluacion->numero_evaluacion }}</td>
                            <td>{{ $evaluacion->nombre }}</td>
                            <td>{{ rtrim(rtrim(number_format((float) $evaluacion->porcentaje, 2, '.', ''), '0'), '.') }}%</td>
                            <td>{{ $evaluacion->fecha_evaluacion?->format('d/m/Y') ?: 'Sin fecha' }}</td>
                            <td>
                                <span class="badge {{ $evaluacion->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $evaluacion->estado }}</span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.evaluaciones.edit', $evaluacion) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-academica-cup.evaluaciones.destroy', $evaluacion) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar esta evaluacion?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="alert">
                                    <span>La materia aun no tiene evaluaciones registradas.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
