@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Editar Cupo')

@section('content')
    <x-page-title title="Editar Cupo por Carrera" subtitle="CU8 Administrar Carreras y Cupos" />

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
        <form method="POST" action="{{ route('gestion-postulantes-admision.cupos.update', $cupo) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Datos academicos</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Carrera</span>
                        <select name="carrera_id" class="select select-bordered" required>
                            <option value="">Seleccione una carrera</option>
                            @foreach ($carreras as $carrera)
                                <option value="{{ $carrera->id }}" @selected((string) old('carrera_id', $cupo->carrera_id) === (string) $carrera->id)>{{ $carrera->nombre }} ({{ $carrera->codigo }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Gestion academica</span>
                        <select name="gestion" class="select select-bordered" required>
                            <option value="">Seleccione una gestion academica</option>
                            @foreach ($gestionesAcademicas as $gestion)
                                <option value="{{ $gestion }}" @selected(old('gestion', $cupo->gestion) === $gestion)>{{ $gestion }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Estado</span>
                        <select name="estado" class="select select-bordered" required>
                            <option value="ACTIVO" @selected(old('estado', $cupo->estado) === 'ACTIVO')>ACTIVO</option>
                            <option value="INACTIVO" @selected(old('estado', $cupo->estado) === 'INACTIVO')>INACTIVO</option>
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Capacidad</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Cupo total</span>
                        <input type="number" name="cupo_total" value="{{ old('cupo_total', $cupo->cupo_maximo) }}" class="input input-bordered" min="0" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Cupo disponible</span>
                        <input type="number" name="cupo_disponible" value="{{ old('cupo_disponible', $cupo->cupos_disponibles) }}" class="input input-bordered" min="0" required>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Actualizar</button>
                <a href="{{ route('gestion-postulantes-admision.cupos.show', $cupo) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
