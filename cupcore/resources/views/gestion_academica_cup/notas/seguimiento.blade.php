@extends('layouts.app')

@section('title', 'CU14 Seguimiento Academico | CUPCore')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Seguimiento Academico" subtitle="Consulta notas por postulante y materia, identifica evaluaciones faltantes y calcula el promedio ponderado cuando las tres evaluaciones estan completas." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.notas.index', request()->query()) }}" class="btn btn-outline">Volver a notas</a>
            <a href="{{ route('gestion-academica-cup.notas.create') }}" class="btn btn-primary">Nueva nota</a>
        </div>
    </div>

    @if ($scopeMessage)
        <div class="mb-6"><x-alert type="info" :message="$scopeMessage" /></div>
    @endif

    <x-card title="Filtros de seguimiento">
        <form method="GET" action="{{ route('gestion-academica-cup.notas.seguimiento') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['grupos']->pluck('gestion')->unique() as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($filters['gestion'] === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['grupos'] as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) $filters['grupo_id'] === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Postulante</span>
                <select name="postulante_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['postulantes'] as $postulante)
                        <option value="{{ $postulante->id }}" @selected((string) $filters['postulante_id'] === (string) $postulante->id)>{{ $postulante->apellidos }} {{ $postulante->nombres }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Materia</span>
                <select name="materia_id" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['materias'] as $materia)
                        <option value="{{ $materia->id }}" @selected((string) $filters['materia_id'] === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <div class="md:col-span-2 xl:col-span-4 flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.notas.seguimiento') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Seguimiento por postulante y materia">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Grupo</th>
                        <th>Gestion</th>
                        <th>Materia</th>
                        <th>Evaluacion 1</th>
                        <th>Evaluacion 2</th>
                        <th>Evaluacion 3</th>
                        <th>Promedio</th>
                        <th>Estado</th>
                        <th>Faltantes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($rows as $row)
                        <tr>
                            <td>{{ $row['postulante_nombre'] }}</td>
                            <td>{{ $row['postulante_ci'] }}</td>
                            <td>{{ $row['grupo_nombre'] }}</td>
                            <td>{{ $row['gestion'] }}</td>
                            <td>{{ $row['materia_nombre'] }}</td>
                            @foreach ([1, 2, 3] as $numero)
                                @php
                                    $entry = $row['evaluaciones'][$numero];
                                @endphp
                                <td>
                                    @if ($entry['evaluacion'] && $entry['nota'])
                                        <div class="font-medium text-white">{{ rtrim(rtrim(number_format((float) $entry['nota']->nota, 2, '.', ''), '0'), '.') }}</div>
                                        <div class="text-xs text-base-content/70">{{ rtrim(rtrim(number_format((float) $entry['evaluacion']->porcentaje, 2, '.', ''), '0'), '.') }}%</div>
                                    @elseif ($entry['evaluacion'])
                                        <span class="badge badge-warning">Pendiente</span>
                                        <div class="text-xs text-base-content/70">{{ rtrim(rtrim(number_format((float) $entry['evaluacion']->porcentaje, 2, '.', ''), '0'), '.') }}%</div>
                                    @else
                                        <span class="badge badge-ghost">Sin config.</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @if ($row['promedio'] !== null)
                                    <span class="badge badge-info">{{ $row['promedio'] }}</span>
                                @else
                                    <span class="badge badge-ghost">Incompleto</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $row['estado'] === 'Completo' ? 'badge-success' : 'badge-warning' }}">{{ $row['estado'] }}</span>
                            </td>
                            <td>{{ $row['faltantes'] !== [] ? implode(', ', $row['faltantes']) : 'Ninguna' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="alert">
                                    <span>No hay informacion de seguimiento con los filtros actuales.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
