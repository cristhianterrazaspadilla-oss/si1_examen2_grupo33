@extends('layouts.app')

@section('title', 'CU9 Administrar Materias y Evaluaciones | Nueva materia')

@section('content')
    <x-page-title title="Nueva Materia" subtitle="Registra una materia del Curso Preuniversitario FICCT antes de configurar sus tres evaluaciones obligatorias." />

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

    <x-card title="Formulario de materia">
        <form method="POST" action="{{ route('gestion-academica-cup.materias.store') }}" class="app-form">
            @csrf

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Codigo</span>
                        <input type="text" name="codigo" value="{{ old('codigo') }}" class="input input-bordered" maxlength="30">
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Descripcion</span>
                        <textarea name="descripcion" class="textarea textarea-bordered" rows="4">{{ old('descripcion') }}</textarea>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado inicial</span>
                        <select name="estado" class="select select-bordered">
                            <option value="ACTIVO" @selected(old('estado', 'ACTIVO') === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado') === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('gestion-academica-cup.materias.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
