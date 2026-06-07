@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Detalle de Cupo')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de Cupo" subtitle="CU8 Administrar Carreras y Cupos" />
        <div class="flex gap-2">
            <a href="{{ route('gestion-postulantes-admision.cupos.edit', $cupo) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-postulantes-admision.cupos.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion del cupo">
        <div class="detail-grid cols-2">
            <div class="detail-item"><p class="detail-item-label">Carrera</p><p class="detail-item-value">{{ $cupo->carrera?->nombre }} ({{ $cupo->carrera?->codigo }})</p></div>
            <div class="detail-item"><p class="detail-item-label">Gestion academica</p><p class="detail-item-value">{{ $cupo->gestion }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Cupo total</p><p class="detail-item-value">{{ $cupo->cupo_maximo }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Cupo disponible</p><p class="detail-item-value">{{ $cupo->cupos_disponibles }}</p></div>
            <div class="detail-item">
                <p class="detail-item-label">Estado</p>
                <div class="detail-item-value">
                    <span class="badge {{ $cupo->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                        {{ $cupo->estado }}
                    </span>
                </div>
            </div>
        </div>
    </x-card>
@endsection
