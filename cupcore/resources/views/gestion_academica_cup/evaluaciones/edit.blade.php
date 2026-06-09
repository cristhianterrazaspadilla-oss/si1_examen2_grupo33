@extends('layouts.app')

@section('title', 'CU9 Administrar Materias y Evaluaciones | Editar evaluacion')

@section('content')
    <x-page-title title="Editar Evaluacion" subtitle="Actualiza la evaluacion de {{ $materia->nombre }} manteniendo las reglas 30/30/40 y la unicidad por numero." />

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

    <div class="grid grid-cols-1 lg:grid-cols-[1.1fr_0.9fr] gap-6">
        <x-card title="Formulario de evaluacion">
            <form method="POST" action="{{ route('gestion-academica-cup.evaluaciones.update', $evaluacion) }}" class="app-form">
                @csrf
                @method('PUT')

                <section class="app-form-section">
                    <h2 class="app-section-title">Datos de la evaluacion</h2>
                    <div class="app-form-grid cols-2">
                        <label class="form-control md:col-span-2">
                            <span class="label-text">Materia</span>
                            <input type="text" value="{{ $materia->nombre }}{{ $materia->codigo ? ' - ' . $materia->codigo : '' }}" class="input input-bordered w-full" readonly>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Nombre</span>
                            <input type="text" name="nombre" value="{{ old('nombre', $evaluacion->nombre) }}" class="input input-bordered w-full" maxlength="100" required>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Numero de evaluacion</span>
                            <select name="numero_evaluacion" class="select select-bordered w-full" required>
                                @foreach ([1, 2, 3] as $numero)
                                    <option value="{{ $numero }}" @selected((string) old('numero_evaluacion', $evaluacion->numero_evaluacion) === (string) $numero)>Evaluacion {{ $numero }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Porcentaje</span>
                            <input type="number" name="porcentaje" value="{{ old('porcentaje', $evaluacion->porcentaje) }}" class="input input-bordered w-full" min="0" step="0.01" required>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Fecha de evaluacion</span>
                            <input type="date" name="fecha_evaluacion" value="{{ old('fecha_evaluacion', $evaluacion->fecha_evaluacion?->format('Y-m-d')) }}" class="input input-bordered w-full">
                        </label>
                        <label class="form-control">
                            <span class="label-text">Estado</span>
                            <select name="estado" class="select select-bordered w-full" required>
                                <option value="ACTIVO" @selected(old('estado', $evaluacion->estado) === 'ACTIVO')>ACTIVO</option>
                                <option value="INACTIVO" @selected(old('estado', $evaluacion->estado) === 'INACTIVO')>INACTIVO</option>
                            </select>
                        </label>
                    </div>
                </section>

                <div class="app-form-actions flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Actualizar evaluacion</button>
                    <a href="{{ route('gestion-academica-cup.materias.show', $materia) }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
                </div>
            </form>
        </x-card>

        <x-card title="Reglas obligatorias">
            <div class="space-y-4 text-sm leading-7 text-base-content/75">
                <div class="alert alert-info">
                    <span>La validacion seguira exigiendo 30% para evaluacion 1, 30% para evaluacion 2 y 40% para evaluacion 3.</span>
                </div>
                <ul class="list-disc space-y-2 pl-5">
                    <li>Numero 1 corresponde a 30%.</li>
                    <li>Numero 2 corresponde a 30%.</li>
                    <li>Numero 3 corresponde a 40%.</li>
                    <li>Si una evaluacion se desactiva, la materia vuelve a quedar pendiente de configuracion.</li>
                </ul>
            </div>
        </x-card>
    </div>
@endsection
