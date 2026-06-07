@extends('layouts.app')

@section('title', 'CU3 Administrar Usuarios y Roles | Nuevo Usuario')

@section('content')
    <x-page-title title="Nuevo Usuario" subtitle="CU3 Administrar Usuarios y Roles" />

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

    <x-card title="Formulario de usuario">
        <form method="POST" action="{{ route('autenticacion-usuarios-seguridad.usuarios.store') }}" class="app-form">
            @csrf

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Nombre</span>
                        <input type="text" name="nombre" value="{{ old('nombre') }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Apellido</span>
                        <input type="text" name="apellido" value="{{ old('apellido') }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">CI</span>
                        <input type="text" name="ci" value="{{ old('ci') }}" class="input input-bordered" maxlength="30" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Correo</span>
                        <input type="email" name="correo" value="{{ old('correo') }}" class="input input-bordered" maxlength="150" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Telefono</span>
                        <input type="text" name="telefono" value="{{ old('telefono') }}" class="input input-bordered" maxlength="30">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Rol</span>
                        <select name="rol_id" class="select select-bordered" required>
                            <option value="">Seleccione un rol</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->id }}" @selected(old('rol_id') == $rol->id)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Seguridad y estado</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Contrasena</span>
                        <input type="password" name="password" class="input input-bordered" minlength="8" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', 'ACTIVO') === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado') === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Guardar</button>
                <a href="{{ route('autenticacion-usuarios-seguridad.usuarios.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </x-card>
@endsection
