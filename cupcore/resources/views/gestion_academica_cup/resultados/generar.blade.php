@extends('layouts.app')

@section('title', 'CU15 Gestionar Resultados | Generar resultado')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Generar resultado de admision" subtitle="Selecciona un postulante inscrito y genera su resultado solo si tiene notas completas en todas las materias activas requeridas." />
        <a href="{{ route('gestion-academica-cup.resultados.index') }}" class="btn btn-outline">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Reglas del calculo">
        <div class="space-y-3 text-sm text-base-content/80">
            <p>Promedio minimo de aprobacion: <span class="font-semibold text-white">{{ $notaMinimaAprobacion }}</span></p>
            <p>No se generara resultado si existen notas incompletas.</p>
            <p>La carrera se asigna segun primera opcion, segunda opcion y cupos disponibles en la gestion del grupo activo del postulante.</p>
        </div>
    </x-card>

    <x-card title="Formulario de generacion">
        <form method="POST" action="{{ route('gestion-academica-cup.resultados.store') }}" class="app-form">
            @csrf
            <section class="app-form-section">
                <h2 class="app-section-title">Postulante</h2>
                <label class="form-control">
                    <span class="label-text">Postulante inscrito</span>
                    <select name="postulante_id" class="select select-bordered" required>
                        <option value="">Selecciona un postulante</option>
                        @foreach ($postulantes as $postulante)
                            <option value="{{ $postulante->id }}" @selected((string) old('postulante_id') === (string) $postulante->id)>{{ $postulante->apellidos }} {{ $postulante->nombres }} - {{ $postulante->ci }}</option>
                        @endforeach
                    </select>
                </label>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Calcular / Generar resultado</button>
                <a href="{{ route('gestion-academica-cup.resultados.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
