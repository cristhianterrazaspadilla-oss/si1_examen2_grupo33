@extends('layouts.app')

@section('title', 'CU6 Gestionar Requisitos de Admision | Detalle de Requisito')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de Requisito" subtitle="CU6 Gestionar Requisitos de Admision" />
        <div class="flex gap-2">
            <a href="{{ route('gestion-postulantes-admision.requisitos.edit', $requisito) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-postulantes-admision.requisitos.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion del requisito">
        <div class="detail-grid cols-2">
            <div class="detail-item"><p class="detail-item-label">Nombre</p><p class="detail-item-value">{{ $requisito->nombre }}</p></div>
            <div class="detail-item">
                <p class="detail-item-label">Obligatorio</p>
                <div class="detail-item-value">
                    <span class="badge {{ $requisito->obligatorio ? 'badge-warning' : 'badge-info' }}">{{ $requisito->obligatorio ? 'Si' : 'No' }}</span>
                </div>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Estado</p>
                <div class="detail-item-value">
                    <span class="badge {{ $requisito->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $requisito->estado }}</span>
                </div>
            </div>
            <div class="detail-item"><p class="detail-item-label">Registros asociados</p><p class="detail-item-value">{{ $requisito->postulante_requisitos_count }}</p></div>
            <div class="detail-item md:col-span-2"><p class="detail-item-label">Descripcion</p><p class="detail-item-value">{{ $requisito->descripcion ?: 'Sin descripcion' }}</p></div>
        </div>
    </x-card>
@endsection
