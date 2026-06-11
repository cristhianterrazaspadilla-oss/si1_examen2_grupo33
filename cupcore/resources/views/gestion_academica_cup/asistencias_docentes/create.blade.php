@extends('layouts.app')

@section('title', 'CU13 Registrar Asistencia Docente | Nueva asistencia')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Registrar asistencia docente" subtitle="Registra asistencia sobre horarios activos. El docente y la gestion academica se obtienen automaticamente desde el horario." />
        <a href="{{ route('gestion-academica-cup.asistencias-docentes.index') }}" class="btn btn-outline shrink-0">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de asistencia">
        <form method="POST" action="{{ route('gestion-academica-cup.asistencias-docentes.store') }}" class="app-form">
            @csrf
            <section class="app-form-section">
                <h2 class="app-section-title">Fecha y horario</h2>
                <p class="app-section-subtitle">Seleccione un horario activo. El docente se obtiene automaticamente desde el horario.</p>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Fecha</span>
                        <input type="date" name="fecha" value="{{ old('fecha', now()->toDateString()) }}" max="{{ now()->toDateString() }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Horario</span>
                        <select name="horario_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona un horario activo</option>
                            @foreach ($horarios as $horario)
                                <option value="{{ $horario->id }}" @selected((string) old('horario_id') === (string) $horario->id)>
                                    {{ $horario->dia_semana }} {{ substr((string) $horario->hora_inicio, 0, 5) }}-{{ substr((string) $horario->hora_fin, 0, 5) }} | {{ $horario->grupo?->nombre }} | {{ $horario->materia?->nombre }} | {{ trim(($horario->docente?->nombres ?? '') . ' ' . ($horario->docente?->apellidos ?? '')) }} | {{ $horario->aula?->nombre }} | Gestion {{ $horario->grupo?->gestion }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Estado de asistencia</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Estado asistencia</span>
                        <select name="estado_asistencia" class="select select-bordered w-full" required>
                            @foreach ($estadosAsistencia as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado_asistencia') === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="rounded-2xl border border-blue-300/15 bg-blue-500/8 p-4 text-sm text-blue-100">
                        La hora de registro se tomará automáticamente del servidor.
                    </div>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Observacion</h2>
                <label class="form-control">
                    <span class="label-text">Observacion</span>
                    <textarea name="observacion" class="textarea textarea-bordered w-full">{{ old('observacion') }}</textarea>
                </label>
            </section>

            <div class="app-form-actions flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Guardar</button>
                <a href="{{ route('gestion-academica-cup.asistencias-docentes.index') }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
