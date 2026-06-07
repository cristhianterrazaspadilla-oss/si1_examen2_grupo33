@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Editar horario')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Editar horario" subtitle="Modifica la asignacion academica, aula, gestion, dia y bloque horario del registro seleccionado." />
        <a href="{{ route('gestion-academica-cup.horarios.show', $horario) }}" class="btn btn-outline">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de edicion">
        <p class="app-section-subtitle">El sistema validara cruces de aula, docente y grupo antes de guardar.</p>
        @if (! $asignacionActual)
            <div class="mb-4">
                <x-alert type="warning" message="No se encontro una asignacion activa exacta para el horario actual. Selecciona una asignacion valida antes de guardar." />
            </div>
        @endif
        <form method="POST" action="{{ route('gestion-academica-cup.horarios.update', $horario) }}" class="app-form">
            @csrf
            @method('PUT')
            <section class="app-form-section">
                <h2 class="app-section-title">Programacion academica</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Asignacion docente-grupo-materia</span>
                        <select name="docente_asignacion_id" class="select select-bordered" required>
                            <option value="">Selecciona una asignacion activa</option>
                            @foreach ($asignaciones as $asignacion)
                                <option value="{{ $asignacion->id }}" @selected((string) old('docente_asignacion_id', $asignacionActual?->id) === (string) $asignacion->id)>
                                    {{ $asignacion->grupo?->nombre }} — {{ $asignacion->materia?->nombre }} — {{ trim(($asignacion->docente?->nombres ?? '') . ' ' . ($asignacion->docente?->apellidos ?? '')) }} — {{ $asignacion->gestion }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Aula</span>
                        <select name="aula_id" class="select select-bordered" required>
                            <option value="">Selecciona un aula</option>
                            @foreach ($aulas as $aula)
                                <option value="{{ $aula->id }}" @selected((string) old('aula_id', $horario->aula_id) === (string) $aula->id)>{{ $aula->nombre }} — {{ $aula->capacidad }} personas</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Gestion academica</span>
                        <div class="rounded-2xl border border-blue-300/12 bg-slate-950/45 px-4 py-3 shadow-[inset_0_1px_0_rgba(255,255,255,0.03)]">
                            <p class="text-sm font-semibold text-white">
                                @if ($horario->grupo?->gestion)
                                    Gestion: {{ $horario->grupo->gestion }}
                                @else
                                    Selecciona una asignacion para ver la gestion.
                                @endif
                            </p>
                            <p class="mt-1 text-sm text-base-content/70">Si cambias la asignacion, el horario usara automaticamente la gestion del grupo asociado.</p>
                        </div>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Dia</span>
                        <select name="dia_semana" class="select select-bordered" required>
                            @foreach ($diasSemana as $diaOption)
                                <option value="{{ $diaOption }}" @selected(old('dia_semana', $horario->dia_semana) === $diaOption)>{{ $diaOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered">
                            @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado', $horario->estado) === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Hora inicio</span>
                        <input type="time" step="300" name="hora_inicio" value="{{ old('hora_inicio', substr((string) $horario->hora_inicio, 0, 5)) }}" class="input input-bordered app-time-input" required>
                        <span class="label-text-alt">Puedes escribir horarios intermedios, por ejemplo 08:45 o 09:15.</span>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Hora fin</span>
                        <input type="time" step="300" name="hora_fin" value="{{ old('hora_fin', substr((string) $horario->hora_fin, 0, 5)) }}" class="input input-bordered app-time-input" required>
                        <span class="label-text-alt">Puedes escribir horarios intermedios, por ejemplo 08:45 o 09:15.</span>
                    </label>
                </div>
            </section>
            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-academica-cup.horarios.show', $horario) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
