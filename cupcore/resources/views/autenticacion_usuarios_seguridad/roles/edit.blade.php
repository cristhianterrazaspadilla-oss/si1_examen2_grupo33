@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Editar Rol')

@section('content')
    <x-page-title title="Editar Rol" subtitle="CU3 Administrar Usuarios y Roles" />

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
        <form method="POST" action="{{ route('autenticacion-usuarios-seguridad.roles.update', $rol) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre', $rol->nombre) }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Descripcion</span>
                        <textarea name="descripcion" class="textarea textarea-bordered" maxlength="255">{{ old('descripcion', $rol->descripcion) }}</textarea>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Estado</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', $rol->estado) === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado', $rol->estado) === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('autenticacion-usuarios-seguridad.roles.show', $rol) }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </x-card>
@endsection
