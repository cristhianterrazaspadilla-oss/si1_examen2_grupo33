@extends('layouts.app')

@section('title', 'CU10 Organizar Grupos Academicos | Editar grupo')

@section('content')
    <x-page-title title="Editar grupo" subtitle="Actualiza los datos del grupo sin redistribuir automaticamente a los postulantes ya asignados." />

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron errores de validacion.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <x-card title="Formulario de grupo">
        <form method="POST" action="{{ route('gestion-academica-cup.grupos.update', $grupo) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $grupo->nombre) }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Codigo</span>
                        <input type="text" name="codigo" value="{{ old('codigo', $grupo->codigo) }}" class="input input-bordered" maxlength="50" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Gestion</span>
                        <select name="gestion" class="select select-bordered" required>
                            @foreach ($gestionesAcademicas as $gestionOption)
                                <option value="{{ $gestionOption }}" @selected(old('gestion', $grupo->gestion) === $gestionOption)>{{ $gestionOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Capacidad maxima</span>
                        <input type="number" name="capacidad_maxima" value="{{ old('capacidad_maxima', $grupo->capacidad_maxima) }}" class="input input-bordered" min="{{ max(1, $grupo->cantidad_estudiantes) }}" max="70" required>
                        <span class="label-text-alt">No puede ser menor a {{ $grupo->cantidad_estudiantes }} estudiantes actuales.</span>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', $grupo->estado) === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado', $grupo->estado) === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-academica-cup.grupos.show', $grupo) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
