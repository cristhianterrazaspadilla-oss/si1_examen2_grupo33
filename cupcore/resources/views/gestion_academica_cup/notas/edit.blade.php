@extends('layouts.app')

@section('title', 'CU14 Gestionar Notas | Editar nota')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Editar nota academica" subtitle="Actualiza el grupo, el postulante, la evaluacion y la calificacion sin modificar resultados de admision." />
        <a href="{{ route('gestion-academica-cup.notas.show', $nota) }}" class="btn btn-outline">Volver</a>
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

    <x-card title="Formulario de edicion">
        <form method="POST" action="{{ route('gestion-academica-cup.notas.update', $nota) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Contexto academico</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Grupo</span>
                        <select name="grupo_id" class="select select-bordered" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach ($formOptions['grupos'] as $grupo)
                                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id', $grupoActual?->grupo_id) === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Materia</span>
                        <select name="materia_id" class="select select-bordered" required>
                            <option value="">Selecciona una materia</option>
                            @foreach ($formOptions['materias'] as $materia)
                                <option value="{{ $materia->id }}" @selected((string) old('materia_id', $nota->materia_id) === (string) $materia->id)>{{ $materia->nombre }}</option>
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
                        <select name="postulante_id" class="select select-bordered" required>
                            <option value="">Selecciona un postulante</option>
                            @foreach ($formOptions['postulantes'] as $postulante)
                                <option value="{{ $postulante->id }}" @selected((string) old('postulante_id', $nota->postulante_id) === (string) $postulante->id)>{{ $postulante->apellidos }} {{ $postulante->nombres }} - {{ $postulante->ci }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Evaluacion</span>
                        <select name="evaluacion_id" class="select select-bordered" required>
                            <option value="">Selecciona una evaluacion</option>
                            @foreach ($formOptions['evaluaciones'] as $evaluacion)
                                <option value="{{ $evaluacion->id }}" @selected((string) old('evaluacion_id', $nota->evaluacion_id) === (string) $evaluacion->id)>{{ $evaluacion->materia?->nombre }} - Evaluacion {{ $evaluacion->numero_evaluacion }} ({{ rtrim(rtrim(number_format((float) $evaluacion->porcentaje, 2, '.', ''), '0'), '.') }}%)</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Calificacion</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nota</span>
                        <input type="number" name="nota" value="{{ old('nota', $nota->nota) }}" min="0" max="100" step="0.01" class="input input-bordered" required>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Observacion</span>
                        <textarea name="observacion" class="textarea textarea-bordered">{{ old('observacion', $nota->observacion) }}</textarea>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-academica-cup.notas.show', $nota) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
