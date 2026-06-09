@extends('layouts.app')

@section('title', 'CU12 Gestionar Docentes y Asignaciones | Nueva asignacion')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Asignar docente a materia y grupo" subtitle="Relaciona un docente activo con una materia y un grupo academico en una gestion especifica." />
        <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline shrink-0">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-[0.95fr_1.05fr] gap-6">
        <x-card title="Docente seleccionado">
            <div class="detail-grid cols-2">
                <div class="detail-item">
                    <p class="detail-item-label">Docente</p>
                    <p class="detail-item-value">{{ trim($docente->nombres . ' ' . $docente->apellidos) }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">CI</p>
                    <p class="detail-item-value">{{ $docente->ci }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Correo</p>
                    <p class="detail-item-value">{{ $docente->correo ?: 'Sin correo registrado' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Estado</p>
                    <p class="detail-item-value">
                        <span class="badge {{ $docente->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $docente->estado }}</span>
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Formulario de asignacion">
            <p class="app-section-subtitle">La validacion de cruces de horario se realizara en CU11 Horarios y Aulas.</p>

            <form method="POST" action="{{ route('gestion-academica-cup.docentes.asignaciones.store', $docente) }}" class="app-form">
                @csrf
                <input type="hidden" name="docente_id" value="{{ old('docente_id', $docente->id) }}">

                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Grupo</span>
                        <select name="grupo_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id') === (string) $grupo->id)>
                                    {{ $grupo->nombre }} — {{ $grupo->gestion }}
                                </option>
                            @endforeach
                        </select>
                        <span class="label-text-alt">La lista prioriza una lectura clara por grupo y gestion academica.</span>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Materia</span>
                        <select name="materia_id" class="select select-bordered w-full" required>
                            <option value="">Selecciona una materia</option>
                            @foreach ($materias as $materia)
                                <option value="{{ $materia->id }}" @selected((string) old('materia_id') === (string) $materia->id)>
                                    {{ $materia->nombre }} | {{ $materia->codigo ?: 'Sin codigo' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Gestion</span>
                        <select name="gestion" class="select select-bordered w-full" required>
                            @foreach ($gestionesAcademicas as $gestionOption)
                                <option value="{{ $gestionOption }}" @selected(old('gestion', '1-' . now()->year) === $gestionOption)>{{ $gestionOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered w-full">
                            @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado', 'ACTIVO') === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="app-form-actions flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Guardar</button>
                    <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
                </div>
            </form>
        </x-card>
    </div>
@endsection
