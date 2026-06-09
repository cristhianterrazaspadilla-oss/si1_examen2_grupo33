@extends('layouts.app')

@section('title', 'CU12 Gestionar Docentes y Asignaciones | Editar docente')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Editar docente" subtitle="Actualiza los datos personales, profesionales y el usuario asociado del docente." />
        <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline shrink-0">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de edicion">
        <form method="POST" action="{{ route('gestion-academica-cup.docentes.update', $docente) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Usuario asociado opcional</h2>
                <p class="app-section-subtitle">Puedes mantener el usuario actual o reasignarlo a otro usuario activo con rol Docente no utilizado por otro docente.</p>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Usuario del sistema</span>
                        <select name="usuario_id" class="select select-bordered w-full">
                            <option value="">Sin asociar</option>
                            @foreach ($usuariosDisponibles as $usuario)
                                <option value="{{ $usuario->id }}" @selected((string) old('usuario_id', $docente->usuario_id) === (string) $usuario->id)>
                                    {{ trim($usuario->nombre . ' ' . $usuario->apellido) }} | {{ $usuario->correo }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Datos personales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">CI</span>
                        <input type="text" name="ci" value="{{ old('ci', $docente->ci) }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Correo</span>
                        <input type="email" name="correo" value="{{ old('correo', $docente->correo) }}" class="input input-bordered w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Nombres</span>
                        <input type="text" name="nombres" value="{{ old('nombres', $docente->nombres) }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Apellidos</span>
                        <input type="text" name="apellidos" value="{{ old('apellidos', $docente->apellidos) }}" class="input input-bordered w-full" required>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Telefono</span>
                        <input type="text" name="telefono" value="{{ old('telefono', $docente->telefono) }}" class="input input-bordered w-full">
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Datos profesionales</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Profesion</span>
                        <input type="text" name="profesion" value="{{ old('profesion', $docente->profesion) }}" class="input input-bordered w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Especialidad</span>
                        <input type="text" name="especialidad" value="{{ old('especialidad', $docente->especialidad) }}" class="input input-bordered w-full">
                    </label>
                    <label class="label cursor-pointer justify-start gap-3 rounded-2xl border border-blue-300/10 bg-slate-950/35 px-4 py-4">
                        <input type="checkbox" name="tiene_maestria" value="1" class="checkbox checkbox-primary" @checked(old('tiene_maestria', $docente->tiene_maestria))>
                        <span class="label-text font-medium text-white">Tiene maestria</span>
                    </label>
                    <label class="label cursor-pointer justify-start gap-3 rounded-2xl border border-blue-300/10 bg-slate-950/35 px-4 py-4">
                        <input type="checkbox" name="tiene_diplomado" value="1" class="checkbox checkbox-primary" @checked(old('tiene_diplomado', $docente->tiene_diplomado))>
                        <span class="label-text font-medium text-white">Tiene diplomado</span>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Estado</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Estado del docente</span>
                        <select name="estado" class="select select-bordered w-full">
                            @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                                <option value="{{ $estadoOption }}" @selected(old('estado', $docente->estado) === $estadoOption)>{{ $estadoOption }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Actualizar</button>
                <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
