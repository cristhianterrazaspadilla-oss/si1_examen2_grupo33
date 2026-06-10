@extends('layouts.app')

@section('title', 'CU13 Registrar Asistencia Docente | CUPCore')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="CU13 Registrar Asistencia Docente" subtitle="Registra y controla la asistencia de docentes en base a los horarios academicos activos del CUP." />
        <a href="{{ route('gestion-academica-cup.asistencias-docentes.create') }}" class="btn btn-primary shrink-0">Registrar asistencia</a>
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

    <div class="mb-6 grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <x-card title="Total registradas"><p class="text-3xl font-semibold text-white">{{ $totalAsistencias }}</p></x-card>
        <x-card title="Presentes"><p class="text-3xl font-semibold text-white">{{ $presentesCount }}</p></x-card>
        <x-card title="Ausentes"><p class="text-3xl font-semibold text-white">{{ $ausentesCount }}</p></x-card>
        <x-card title="Retrasos"><p class="text-3xl font-semibold text-white">{{ $retrasosCount }}</p></x-card>
        <x-card title="Justificadas"><p class="text-3xl font-semibold text-white">{{ $justificadasCount }}</p></x-card>
    </div>

    <x-card title="Filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.asistencias-docentes.index') }}" class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
            <label class="form-control">
                <span class="label-text">Fecha</span>
                <input type="date" name="fecha" value="{{ $fecha }}" class="input input-bordered w-full">
            </label>
            <label class="form-control">
                <span class="label-text">Docente</span>
                <select name="docente_id" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($docentes as $docente)
                        <option value="{{ $docente->id }}" @selected((string) $docenteId === (string) $docente->id)>{{ trim($docente->nombres . ' ' . $docente->apellidos) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado asistencia</span>
                <select name="estado_asistencia" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($estadosAsistencia as $estadoOption)
                        <option value="{{ $estadoOption }}" @selected($estadoAsistencia === $estadoOption)>{{ $estadoOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" class="select select-bordered w-full">
                    <option value="">Todos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) $grupoId === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Materia</span>
                <select name="materia_id" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($materias as $materia)
                        <option value="{{ $materia->id }}" @selected((string) $materiaId === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Aula</span>
                <select name="aula_id" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($aulas as $aula)
                        <option value="{{ $aula->id }}" @selected((string) $aulaId === (string) $aula->id)>{{ $aula->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Gestion academica</span>
                <select name="gestion" class="select select-bordered w-full">
                    <option value="">Todas</option>
                    @foreach ($gestionesAcademicas as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($gestion === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <div class="sm:col-span-2 lg:col-span-3 xl:col-span-4 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.asistencias-docentes.index') }}" class="btn btn-outline w-full sm:w-auto">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de asistencias">
        <div class="overflow-x-auto w-full">
            <table class="table min-w-[1100px]">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Docente</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Aula</th>
                        <th>Gestion</th>
                        <th>Horario</th>
                        <th>Asistencia</th>
                        <th>Hora registrada</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistencias as $asistencia)
                        <tr>
                            <td>{{ optional($asistencia->fecha)->format('Y-m-d') ?? $asistencia->fecha }}</td>
                            <td>{{ trim(($asistencia->docente?->nombres ?? '') . ' ' . ($asistencia->docente?->apellidos ?? '')) ?: 'Sin docente' }}</td>
                            <td>{{ $asistencia->horario?->grupo?->nombre ?: 'Sin grupo' }}</td>
                            <td>{{ $asistencia->horario?->materia?->nombre ?: 'Sin materia' }}</td>
                            <td>{{ $asistencia->horario?->aula?->nombre ?: 'Sin aula' }}</td>
                            <td>{{ $asistencia->horario?->grupo?->gestion ?: 'Sin gestion' }}</td>
                            <td>{{ $asistencia->horario?->dia_semana }} {{ substr((string) $asistencia->horario?->hora_inicio, 0, 5) }}-{{ substr((string) $asistencia->horario?->hora_fin, 0, 5) }}</td>
                            <td><span class="badge badge-info">{{ $asistencia->estado_asistencia }}</span></td>
                            <td>{{ $asistencia->hora_registro ?: 'Sin registro' }}</td>
                            <td>
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.asistencias-docentes.show', $asistencia) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.asistencias-docentes.edit', $asistencia) }}" class="btn btn-sm btn-info">Editar</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10"><div class="alert"><span>No existen asistencias registradas con esos criterios.</span></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $asistencias->links() }}</div>
    </x-card>
@endsection
