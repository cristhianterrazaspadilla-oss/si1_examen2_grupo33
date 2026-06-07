@extends('layouts.app')

@section('title', 'CU14 Gestionar Notas | Detalle de nota')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de nota academica" subtitle="Consulta el contexto del postulante, la evaluacion aplicada y la calificacion registrada." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.notas.edit', $nota) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.notas.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-card title="Informacion del postulante">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Postulante</p><p class="detail-item-value">{{ trim(($nota->postulante?->nombres ?? '') . ' ' . ($nota->postulante?->apellidos ?? '')) ?: 'Sin postulante' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">CI</p><p class="detail-item-value">{{ $nota->postulante?->ci ?: 'Sin CI' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Grupo actual</p><p class="detail-item-value">{{ $grupoActual?->grupo?->nombre ?: 'Sin grupo activo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Gestion</p><p class="detail-item-value">{{ $grupoActual?->grupo?->gestion ?: 'Sin gestion' }}</p></div>
            </div>
        </x-card>

        <x-card title="Informacion academica">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Materia</p><p class="detail-item-value">{{ $nota->materia?->nombre ?: 'Sin materia' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Evaluacion</p><p class="detail-item-value">{{ $nota->evaluacion?->nombre ?: ('Evaluacion ' . ($nota->evaluacion?->numero_evaluacion ?? '-')) }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Numero de evaluacion</p><p class="detail-item-value">{{ $nota->evaluacion?->numero_evaluacion ?? 'Sin numero' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Porcentaje</p><p class="detail-item-value">{{ $nota->evaluacion ? rtrim(rtrim(number_format((float) $nota->evaluacion->porcentaje, 2, '.', ''), '0'), '.') . '%' : 'Sin porcentaje' }}</p></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-card title="Calificacion">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Nota</p><p class="detail-item-value"><span class="badge badge-info">{{ rtrim(rtrim(number_format((float) $nota->nota, 2, '.', ''), '0'), '.') }}</span></p></div>
                <div class="detail-item"><p class="detail-item-label">Observacion</p><p class="detail-item-value">{{ $nota->observacion ?: 'Sin observacion registrada' }}</p></div>
            </div>
        </x-card>

        <x-card title="Registro">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Registrado por</p><p class="detail-item-value">{{ $nota->registradoPor ? trim($nota->registradoPor->nombre . ' ' . $nota->registradoPor->apellido) : 'Sin usuario asociado' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Fecha de creacion</p><p class="detail-item-value">{{ $nota->created_at?->format('Y-m-d H:i') ?: 'Sin fecha' }}</p></div>
            </div>
        </x-card>
    </div>
@endsection
