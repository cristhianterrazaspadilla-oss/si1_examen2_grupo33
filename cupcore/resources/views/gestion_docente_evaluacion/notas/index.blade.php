@extends('layouts.app')

@section('title', 'CU14 Gestionar Notas y Seguimiento Academico | CUPCore')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="CU14 Gestionar Notas y Seguimiento Academico" subtitle="Registra, consulta y edita notas academicas usando evaluaciones activas, grupos organizados y asignaciones docentes vigentes." />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-academica-cup.notas.seguimiento', request()->query()) }}" class="btn btn-outline">Seguimiento academico</a>
            <a href="{{ route('gestion-academica-cup.notas.create') }}" class="btn btn-primary">Nueva nota</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    @if ($scopeMessage)
        <div class="mb-6"><x-alert type="info" :message="$scopeMessage" /></div>
    @endif

    <div class="mb-6 grid gap-4 grid-cols-1 sm:grid-cols-2 xl:grid-cols-4">
        <x-card title="Total notas registradas"><p class="text-3xl font-semibold text-white">{{ $totalNotasRegistradas }}</p></x-card>
        <x-card title="Postulantes evaluados"><p class="text-3xl font-semibold text-white">{{ $postulantesEvaluados }}</p></x-card>
        <x-card title="Materias con notas"><p class="text-3xl font-semibold text-white">{{ $materiasConNotas }}</p></x-card>
        <x-card title="Evaluaciones pendientes"><p class="text-3xl font-semibold text-white">{{ $evaluacionesPendientes }}</p></x-card>
    </div>

    <x-card title="Filtros academicos">
        <form method="GET" action="{{ route('gestion-academica-cup.notas.index') }}" class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($formOptions['grupos']->pluck('gestion')->unique() as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($filters['gestion'] === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($formOptions['grupos'] as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) $filters['grupo_id'] === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Materia</span>
                <select name="materia_id" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($formOptions['materias'] as $materia)
                        <option value="{{ $materia->id }}" @selected((string) $filters['materia_id'] === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Evaluacion</span>
                <select name="evaluacion_id" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($formOptions['evaluaciones'] as $evaluacion)
                        <option value="{{ $evaluacion->id }}" @selected((string) $filters['evaluacion_id'] === (string) $evaluacion->id)>{{ $evaluacion->materia?->nombre }} - Evaluacion {{ $evaluacion->numero_evaluacion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Postulante</span>
                <select name="postulante_id" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($formOptions['postulantes'] as $postulante)
                        <option value="{{ $postulante->id }}" @selected((string) $filters['postulante_id'] === (string) $postulante->id)>{{ $postulante->apellidos }} {{ $postulante->nombres }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Docente</span>
                <select name="docente_id" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($formOptions['docentes'] as $docente)
                        <option value="{{ $docente->id }}" @selected((string) $filters['docente_id'] === (string) $docente->id)>{{ $docente->apellidos }} {{ $docente->nombres }}</option>
                    @endforeach
                </select>
            </label>
            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-5 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.notas.index') }}" class="btn btn-outline w-full sm:w-auto">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de notas">
        <div class="overflow-x-auto w-full">
            <table class="table min-w-[1300px]">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Grupo</th>
                        <th>Gestion</th>
                        <th>Materia</th>
                        <th>Evaluacion</th>
                        <th>Porcentaje</th>
                        <th>Nota</th>
                        <th>Observacion</th>
                        <th>Registrado por</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($notas as $nota)
                        @php
                            $grupoActual = $gruposActuales[$nota->postulante_id] ?? null;
                        @endphp
                        <tr>
                            <td>{{ trim(($nota->postulante?->nombres ?? '') . ' ' . ($nota->postulante?->apellidos ?? '')) ?: 'Sin postulante' }}</td>
                            <td>{{ $nota->postulante?->ci ?: 'Sin CI' }}</td>
                            <td>{{ $grupoActual?->grupo?->nombre ?: 'Sin grupo activo' }}</td>
                            <td>{{ $grupoActual?->grupo?->gestion ?: 'Sin gestion' }}</td>
                            <td>{{ $nota->materia?->nombre ?: 'Sin materia' }}</td>
                            <td>{{ $nota->evaluacion?->nombre ?: ('Evaluacion ' . ($nota->evaluacion?->numero_evaluacion ?? '-')) }}</td>
                            <td>{{ $nota->evaluacion ? rtrim(rtrim(number_format((float) $nota->evaluacion->porcentaje, 2, '.', ''), '0'), '.') . '%' : 'Sin porcentaje' }}</td>
                            <td><span class="badge badge-info">{{ rtrim(rtrim(number_format((float) $nota->nota, 2, '.', ''), '0'), '.') }}</span></td>
                            <td>{{ $nota->observacion ?: 'Sin observacion' }}</td>
                            <td>{{ $nota->registradoPor ? trim($nota->registradoPor->nombre . ' ' . $nota->registradoPor->apellido) : 'Sin usuario' }}</td>
                            <td>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.notas.show', $nota) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.notas.edit', $nota) }}" class="btn btn-sm btn-info">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="alert">
                                    <span>No existen notas registradas con los filtros actuales.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $notas->links() }}</div>
    </x-card>
@endsection
