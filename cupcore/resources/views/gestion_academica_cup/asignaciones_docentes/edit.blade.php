@extends('layouts.app')

@section('title', 'CU12 Gestionar Docentes y Asignaciones | Editar asignacion')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Editar asignacion docente" subtitle="Actualiza el grupo, la materia, la gestion y el estado de una asignacion academica existente." />
        <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <x-card title="Docente y asignacion actual">
            <div class="detail-grid cols-2">
                <div class="detail-item">
                    <p class="detail-item-label">Docente</p>
                    <p class="detail-item-value">{{ trim($docente->nombres . ' ' . $docente->apellidos) }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Estado actual</p>
                    <p class="detail-item-value">
                        <span class="badge {{ $asignacion->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $asignacion->estado }}</span>
                    </p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Grupo actual</p>
                    <p class="detail-item-value">{{ $asignacion->grupo?->nombre ?: 'Sin grupo' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Materia actual</p>
                    <p class="detail-item-value">{{ $asignacion->materia?->nombre ?: 'Sin materia' }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Formulario de edicion">
            <form method="POST" action="{{ route('gestion-academica-cup.docente-asignaciones.update', $asignacion) }}" class="app-form">
                @csrf
                @method('PUT')
                <input type="hidden" name="docente_id" value="{{ old('docente_id', $docente->id) }}">

                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Grupo</span>
                        <select name="grupo_id" class="select select-bordered" required>
                            <option value="">Selecciona un grupo</option>
                            @foreach ($grupos as $grupo)
                                <option value="{{ $grupo->id }}" @selected((string) old('grupo_id', $asignacion->grupo_id) === (string) $grupo->id)>
                                    {{ $grupo->nombre }} — {{ $grupo->gestion }}
                                </option>
                            @endforeach
                        </select>
                        <span class="label-text-alt">El codigo del grupo queda fuera del option para reducir ruido visual.</span>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Materia</span>
                        <select name="materia_id" class="select select-bordered" required>
                            <option value="">Selecciona una materia</option>
                            @foreach ($materias as $materia)
                                <option value="{{ $materia->id }}" @selected((string) old('materia_id', $asignacion->materia_id) === (string) $materia->id)>
                                    {{ $materia->nombre }} | {{ $materia->codigo ?: 'Sin codigo' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Gestion</span>
                        <select name="gestion" class="select select-bordered" required>
                            @foreach ($gestionesAcademicas as $gestionOption)
                                <option value="{{ $gestionOption }}" @selected(old('gestion', $asignacion->gestion) === $gestionOption)>{{ $gestionOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered">
                            @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado', $asignacion->estado) === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>

                <div class="app-form-actions">
                    <button type="submit" class="btn btn-primary">Actualizar</button>
                    <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline">Volver</a>
                </div>
            </form>
        </x-card>
    </div>
@endsection
