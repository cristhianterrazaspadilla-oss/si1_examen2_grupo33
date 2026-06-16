@extends('layouts.app')

@section('title', 'CU14 Gestionar Notas | Nueva nota')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Registrar nota academica" subtitle="Registra una nota por postulante y evaluacion. La materia se valida automaticamente contra la evaluacion seleccionada." />
        <a href="{{ route('gestion-academica-cup.notas.index') }}" class="btn btn-outline shrink-0">Volver</a>
    </div>

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

    <x-card title="Formulario de nota">
        <form method="POST" action="{{ route('gestion-academica-cup.notas.store') }}" class="app-form">
            @csrf

            <section class="app-form-section">
                <h2 class="app-section-title">Contexto academico</h2>
                <p class="app-section-subtitle">Selecciona un grupo activo, una materia activa y una evaluacion activa compatible.</p>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Grupo</span>
                        <select name="grupo_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach ($formOptions['grupos'] as $grupo)
                                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Materia</span>
                        <select name="materia_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona una materia</option>
                            @foreach ($formOptions['materias'] as $materia)
                                <option value="{{ $materia->id }}" @selected((string) old('materia_id') === (string) $materia->id)>{{ $materia->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Postulante y evaluacion</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Postulante</span>
                        <select name="postulante_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona un postulante</option>
                            @foreach ($formOptions['postulantes'] as $postulante)
                                <option value="{{ $postulante->id }}" @selected((string) old('postulante_id') === (string) $postulante->id)>{{ $postulante->apellidos }} {{ $postulante->nombres }} - {{ $postulante->ci }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Evaluacion</span>
                        <select name="evaluacion_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona una evaluacion</option>
                            @foreach ($formOptions['evaluaciones'] as $evaluacion)
                                <option value="{{ $evaluacion->id }}" @selected((string) old('evaluacion_id') === (string) $evaluacion->id)>{{ $evaluacion->materia?->nombre }} - Evaluacion {{ $evaluacion->numero_evaluacion }} ({{ rtrim(rtrim(number_format((float) $evaluacion->porcentaje, 2, '.', ''), '0'), '.') }}%)</option>
                            @endforeach
                        </select>
                    </label>
                </div>
                <div class="mt-4 rounded-2xl border border-blue-400/15 bg-blue-500/10 px-4 py-3 text-sm text-blue-100/80">
                    La nota debe estar entre 0 y 100. La materia se valida contra la evaluacion seleccionada.
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Calificacion</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nota</span>
                        <input type="number" name="nota" value="{{ old('nota') }}" min="0" max="100" step="0.01" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Observacion</span>
                        <textarea name="observacion" class="textarea textarea-bordered w-full">{{ old('observacion') }}</textarea>
                    </label>
                </div>
            </section>

            <div class="app-form-actions flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Guardar</button>
                <a href="{{ route('gestion-academica-cup.notas.index') }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
