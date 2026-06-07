@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Editar Carrera')

@section('content')
    <x-page-title title="Editar Carrera" subtitle="CU8 Administrar Carreras y Cupos" />

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

    <x-card title="Formulario de edicion">
        <form method="POST" action="{{ route('gestion-postulantes-admision.carreras.update', $carrera) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $carrera->nombre) }}" class="input input-bordered" maxlength="150" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Codigo</span>
                        <input type="text" name="codigo" value="{{ old('codigo', $carrera->codigo) }}" class="input input-bordered" maxlength="30" required>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Descripcion</span>
                        <textarea name="descripcion" class="textarea textarea-bordered" maxlength="255">{{ old('descripcion', $carrera->descripcion) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Estado</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', $carrera->estado) === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado', $carrera->estado) === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-postulantes-admision.carreras.show', $carrera) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
