@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Editar aula')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Editar aula" subtitle="Actualiza nombre, codigo, capacidad, ubicacion y estado del aula." />
        <a href="{{ route('gestion-academica-cup.aulas.show', $aula) }}" class="btn btn-outline shrink-0">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de edicion">
        <form method="POST" action="{{ route('gestion-academica-cup.aulas.update', $aula) }}" class="app-form">
            @csrf
            @method('PUT')
            <section class="app-form-section">
                <h2 class="app-section-title">Datos del aula</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $aula->nombre) }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Codigo</span>
                        <input type="text" name="codigo" value="{{ old('codigo', $aula->codigo) }}" class="input input-bordered w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Capacidad</span>
                        <input type="number" min="1" name="capacidad" value="{{ old('capacidad', $aula->capacidad) }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered w-full">
                            @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado', $aula->estado) === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Ubicacion</span>
                        <input type="text" name="ubicacion" value="{{ old('ubicacion', $aula->ubicacion) }}" class="input input-bordered w-full">
                    </label>
                </div>
            </section>
            <div class="app-form-actions flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Actualizar</button>
                <a href="{{ route('gestion-academica-cup.aulas.show', $aula) }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
