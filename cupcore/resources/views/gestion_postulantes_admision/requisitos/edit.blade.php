@extends('layouts.app')

@section('title', 'CU6 Gestionar Requisitos de Admision | Editar Requisito')

@section('content')
    <x-page-title title="Editar Requisito" subtitle="CU6 Gestionar Requisitos de Admision" />

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
        <form method="POST" action="{{ route('gestion-postulantes-admision.requisitos.update', $requisito) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $requisito->nombre) }}" class="input input-bordered" maxlength="150" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Descripcion</span>
                        <textarea name="descripcion" class="textarea textarea-bordered">{{ old('descripcion', $requisito->descripcion) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Configuracion</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Obligatorio</span>
                        <select name="obligatorio" class="select select-bordered">
                            <option value="1" @selected((string) old('obligatorio', (int) $requisito->obligatorio) === '1')>Si</option>
                            <option value="0" @selected((string) old('obligatorio', (int) $requisito->obligatorio) === '0')>No</option>
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', $requisito->estado) === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado', $requisito->estado) === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-postulantes-admision.requisitos.show', $requisito) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
