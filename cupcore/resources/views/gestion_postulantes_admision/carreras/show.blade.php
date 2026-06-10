@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Detalle de Carrera')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <x-page-title title="Detalle de Carrera" subtitle="CU8 Administrar Carreras y Cupos" />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-postulantes-admision.carreras.edit', $carrera) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-postulantes-admision.carreras.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion de la carrera">
        <div class="detail-grid cols-2">
            <div class="detail-item"><p class="detail-item-label">Nombre</p><p class="detail-item-value">{{ $carrera->nombre }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Codigo</p><p class="detail-item-value">{{ $carrera->codigo }}</p></div>
            <div class="detail-item">
                <p class="detail-item-label">Estado</p>
                <div class="detail-item-value">
                    <span class="badge {{ $carrera->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                        {{ $carrera->estado }}
                    </span>
                </div>
            </div>
            <div class="detail-item"><p class="detail-item-label">Cantidad de cupos</p><p class="detail-item-value">{{ $carrera->cupos_carrera_count }}</p></div>
            <div class="detail-item md:col-span-2"><p class="detail-item-label">Descripcion</p><p class="detail-item-value">{{ $carrera->descripcion ?: 'Sin descripcion' }}</p></div>
        </div>
    </x-card>

    <x-card title="Cupos asociados">
        <div class="overflow-x-auto">
            <table class="table min-w-[580px]">
                <thead>
                    <tr>
                        <th>Gestion</th>
                        <th>Cupo total</th>
                        <th>Cupo disponible</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cupos as $cupo)
                        <tr>
                            <td>{{ $cupo->gestion }}</td>
                            <td>{{ $cupo->cupo_maximo }}</td>
                            <td>{{ $cupo->cupos_disponibles }}</td>
                            <td>
                                <span class="badge {{ $cupo->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $cupo->estado }}
                                </span>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('gestion-postulantes-admision.cupos.show', $cupo) }}" class="btn btn-sm btn-outline">Ver</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>No existen cupos asociados a esta carrera.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $cupos->links() }}
        </div>
    </x-card>
@endsection
