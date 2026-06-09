@extends('layouts.app')

@section('title', 'CU15 Gestionar Resultados | Editar resultado')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Editar resultado de admision" subtitle="Actualiza observaciones del resultado y, si es necesario, recalcula con una justificacion obligatoria." />
        <a href="{{ route('gestion-academica-cup.resultados.show', $resultado) }}" class="btn btn-outline">Volver</a>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de actualizacion">
        <form method="POST" action="{{ route('gestion-academica-cup.resultados.update', $resultado) }}" class="app-form">
            @csrf
            @method('PUT')

            <section class="app-form-section">
                <h2 class="app-section-title">Resultado actual</h2>
                <div class="rounded-2xl border border-blue-400/10 bg-slate-900/40 p-4 text-sm text-base-content/80">
                    <p>Postulante: <span class="font-semibold text-white">{{ trim(($resultado->postulante?->nombres ?? '') . ' ' . ($resultado->postulante?->apellidos ?? '')) }}</span></p>
                    <p>Promedio final: <span class="font-semibold text-white">{{ number_format((float) $resultado->promedio_final, 2, '.', '') }}</span></p>
                    <p>Estado actual: <span class="font-semibold text-white">{{ $resultado->estado_resultado }}</span></p>
                    <p>Carrera asignada: <span class="font-semibold text-white">{{ $resultado->carreraAsignada?->nombre ?: 'Sin carrera asignada' }}</span></p>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Observacion y justificacion</h2>
                <div class="app-form-grid cols-2">
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Observacion</span>
                        <textarea name="observacion" class="textarea textarea-bordered">{{ old('observacion', $resultado->observacion) }}</textarea>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text">Justificacion de modificacion</span>
                        <textarea name="justificacion_modificacion" class="textarea textarea-bordered">{{ old('justificacion_modificacion', $resultado->justificacion_modificacion) }}</textarea>
                    </label>
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" name="accion" value="actualizar" class="btn btn-primary">Actualizar</button>
                <button type="submit" name="accion" value="recalcular" class="btn btn-info">Recalcular resultado</button>
                <a href="{{ route('gestion-academica-cup.resultados.show', $resultado) }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
