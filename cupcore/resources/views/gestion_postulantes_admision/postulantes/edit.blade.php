@extends('layouts.app')

@section('title', 'CU5 Gestionar Inscripcion de Postulantes | Editar Pre-registro')

@section('content')
    <x-page-title title="Editar Pre-registro de Postulante" subtitle="CU5 Gestionar Inscripcion de Postulantes" />

    <div class="alert alert-info mb-6">
        <span>La edicion de este modulo no confirma la inscripcion oficial ni cambia automaticamente el estado a INSCRITO.</span>
    </div>

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
        <form method="POST" action="{{ route('gestion-postulantes-admision.postulantes.update', $postulante) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos principales</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    @if ($normalizedRole === 'administrador')
                        <label class="form-control">
                            <span class="label-text">Usuario asociado</span>
                            <select name="usuario_id" class="select select-bordered">
                                <option value="">Sin asociar</option>
                                @foreach ($usuarios as $usuario)
                                    <option value="{{ $usuario->id }}" @selected((string) old('usuario_id', $postulante->usuario_id) === (string) $usuario->id)>{{ $usuario->nombre }} {{ $usuario->apellido }} - {{ $usuario->correo }}</option>
                                @endforeach
                            </select>
                        </label>
                    @endif
                    <label class="form-control">
                        <span class="label-text">CI</span>
                        <input type="text" name="ci" value="{{ old('ci', $postulante->ci) }}" class="input input-bordered" maxlength="20" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Nombres</span>
                        <input type="text" name="nombres" value="{{ old('nombres', $postulante->nombres) }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Apellidos</span>
                        <input type="text" name="apellidos" value="{{ old('apellidos', $postulante->apellidos) }}" class="input input-bordered" maxlength="100" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Fecha de nacimiento</span>
                        <input type="date" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($postulante->fecha_nacimiento)->format('Y-m-d')) }}" class="input input-bordered">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Sexo</span>
                        <select name="sexo" class="select select-bordered">
                            <option value="">Seleccione</option>
                            @foreach (['Masculino', 'Femenino', 'Otro'] as $sexo)
                                <option value="{{ $sexo }}" @selected(old('sexo', $postulante->sexo) === $sexo)>{{ $sexo }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Tipo de sangre</span>
                        <input type="text" name="tipo_sangre" value="{{ old('tipo_sangre', $postulante->tipo_sangre) }}" class="input input-bordered" maxlength="5">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Titulo de bachiller</span>
                        <select name="titulo_bachiller" class="select select-bordered">
                            <option value="">No especificado</option>
                            <option value="1" @selected((string) old('titulo_bachiller', (int) $postulante->titulo_bachiller) === '1')>Si</option>
                            <option value="0" @selected((string) old('titulo_bachiller', (int) $postulante->titulo_bachiller) === '0')>No</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Datos de contacto</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-control">
                        <span class="label-text">Correo</span>
                        <input type="email" name="correo" value="{{ old('correo', $postulante->correo) }}" class="input input-bordered" maxlength="150">
                    </label>
                    <label class="form-control">
                        <span class="label-text">Telefono</span>
                        <input type="text" name="telefono" value="{{ old('telefono', $postulante->telefono) }}" class="input input-bordered" maxlength="30">
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Direccion</span>
                        <textarea name="direccion" class="textarea textarea-bordered">{{ old('direccion', $postulante->direccion) }}</textarea>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Ciudad</span>
                        <input type="text" name="ciudad" value="{{ old('ciudad', $postulante->ciudad) }}" class="input input-bordered" maxlength="100">
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Datos academicos</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Colegio de procedencia</span>
                        <input type="text" name="colegio_procedencia" value="{{ old('colegio_procedencia', $postulante->colegio_procedencia) }}" class="input input-bordered" maxlength="150">
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Opciones de carrera</h2>
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="form-control">
                        <span class="label-text">Primera opcion</span>
                        <select name="carrera_primera_opcion_id" class="select select-bordered">
                            <option value="">Seleccione una carrera</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" @selected((string) old('carrera_primera_opcion_id', $postulante->carrera_primera_opcion_id) === (string) $carrera->id)>{{ $carrera->nombre }} ({{ $carrera->codigo }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Segunda opcion</span>
                        <select name="carrera_segunda_opcion_id" class="select select-bordered">
                            <option value="">Seleccione una carrera</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" @selected((string) old('carrera_segunda_opcion_id', $postulante->carrera_segunda_opcion_id) === (string) $carrera->id)>{{ $carrera->nombre }} ({{ $carrera->codigo }})</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-postulantes-admision.postulantes.show', $postulante) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
