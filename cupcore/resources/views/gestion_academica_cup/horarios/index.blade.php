@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Horarios')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <x-page-title title="Gestionar horarios" subtitle="Programa horarios academicos usando asignaciones docentes activas y aulas disponibles, con validacion de cruces de aula, docente y grupo." />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-academica-cup.aulas.index') }}" class="btn btn-outline">Aulas</a>
            <a href="{{ route('gestion-academica-cup.horarios.create') }}" class="btn btn-primary">Nuevo horario</a>
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

    <x-card title="Filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.horarios.index') }}" class="grid gap-4 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-6">
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($gestionesAcademicas as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($gestion === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($grupos as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) $grupoId === (string) $grupo->id)>{{ $grupo->nombre }} — {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Docente</span>
                <select name="docente_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($docentes as $docente)
                        <option value="{{ $docente->id }}" @selected((string) $docenteId === (string) $docente->id)>{{ trim($docente->nombres . ' ' . $docente->apellidos) }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Aula</span>
                <select name="aula_id" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($aulas as $aula)
                        <option value="{{ $aula->id }}" @selected((string) $aulaId === (string) $aula->id)>{{ $aula->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Dia</span>
                <select name="dia_semana" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($diasSemana as $diaOption)
                        <option value="{{ $diaOption }}" @selected($dia === $diaOption)>{{ $diaOption }}</option>
                    @endforeach
                </select>
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
            <div class="md:col-span-3 xl:col-span-6 flex gap-2">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.horarios.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de horarios">
        <div class="overflow-x-auto">
            <table class="table min-w-[1100px]">
                <thead>
                    <tr>
                        <th>Gestion</th>
                        <th>Grupo</th>
                        <th>Materia</th>
                        <th>Docente</th>
                        <th>Aula</th>
                        <th>Dia</th>
                        <th>Inicio</th>
                        <th>Fin</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($horarios as $horario)
                        <tr>
                            <td>{{ $horario->grupo?->gestion ?: 'Sin gestion' }}</td>
                            <td>{{ $horario->grupo?->nombre ?: 'Sin grupo' }}</td>
                            <td>{{ $horario->materia?->nombre ?: 'Sin materia' }}</td>
                            <td>{{ trim(($horario->docente?->nombres ?? '') . ' ' . ($horario->docente?->apellidos ?? '')) ?: 'Sin docente' }}</td>
                            <td>{{ $horario->aula?->nombre ?: 'Sin aula' }}</td>
                            <td>{{ $horario->dia_semana }}</td>
                            <td>{{ substr((string) $horario->hora_inicio, 0, 5) }}</td>
                            <td>{{ substr((string) $horario->hora_fin, 0, 5) }}</td>
                            <td><span class="badge {{ $horario->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $horario->estado }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.horarios.show', $horario) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.horarios.edit', $horario) }}" class="btn btn-sm btn-info">Editar</a>
                                    @if ($horario->estado === 'ACTIVO')
                                        <form method="POST" action="{{ route('gestion-academica-cup.horarios.destroy', $horario) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar este horario?')">Desactivar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('gestion-academica-cup.horarios.activar', $horario) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm btn-primary">Activar</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10"><div class="alert"><span>No existen horarios registrados con esos criterios.</span></div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $horarios->links() }}</div>
    </x-card>
@endsection
