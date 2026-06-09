@extends('layouts.app')

@section('title', 'CU15 Gestionar Resultados | Detalle resultado')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de resultado de admision" subtitle="Consulta el calculo final, la asignacion de carrera y el detalle academico utilizado para generar el resultado." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.resultados.edit', $resultado) }}" class="btn btn-info">Editar observacion</a>
            <a href="{{ route('gestion-academica-cup.resultados.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-card title="Datos del postulante">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Postulante</p><p class="detail-item-value">{{ trim(($resultado->postulante?->nombres ?? '') . ' ' . ($resultado->postulante?->apellidos ?? '')) ?: 'Sin postulante' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">CI</p><p class="detail-item-value">{{ $resultado->postulante?->ci ?: 'Sin CI' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Grupo</p><p class="detail-item-value">{{ $grupoActivo?->grupo?->nombre ?: 'Sin grupo activo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Gestion</p><p class="detail-item-value">{{ $grupoActivo?->grupo?->gestion ?: 'Sin gestion' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Primera opcion</p><p class="detail-item-value">{{ $resultado->postulante?->carreraPrimeraOpcion?->nombre ?: 'Sin primera opcion' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Segunda opcion</p><p class="detail-item-value">{{ $resultado->postulante?->carreraSegundaOpcion?->nombre ?: 'Sin segunda opcion' }}</p></div>
            </div>
        </x-card>

        <x-card title="Resultado final">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Promedio final</p><p class="detail-item-value"><span class="badge badge-info">{{ number_format((float) $resultado->promedio_final, 2, '.', '') }}</span></p></div>
                <div class="detail-item"><p class="detail-item-label">Estado resultado</p><p class="detail-item-value"><span class="badge {{ $resultado->estado_resultado === 'APROBADO' ? 'badge-success' : ($resultado->estado_resultado === 'REPROBADO' ? 'badge-error' : 'badge-warning') }}">{{ $resultado->estado_resultado }}</span></p></div>
                <div class="detail-item"><p class="detail-item-label">Carrera asignada</p><p class="detail-item-value">{{ $resultado->carreraAsignada?->nombre ?: 'Sin carrera asignada' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Tipo asignacion</p><p class="detail-item-value">{{ $resultado->tipo_asignacion }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Fecha resultado</p><p class="detail-item-value">{{ $resultado->fecha_resultado?->format('Y-m-d H:i') ?: 'Sin fecha' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Modificado por</p><p class="detail-item-value">{{ $resultado->modificadoPor ? trim($resultado->modificadoPor->nombre . ' ' . $resultado->modificadoPor->apellido) : 'Sin usuario' }}</p></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-card title="Observaciones">
            <div class="detail-grid">
                <div class="detail-item"><p class="detail-item-label">Observacion</p><p class="detail-item-value">{{ $resultado->observacion ?: 'Sin observacion registrada' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Justificacion de modificacion</p><p class="detail-item-value">{{ $resultado->justificacion_modificacion ?: 'Sin justificacion registrada' }}</p></div>
            </div>
        </x-card>

        <x-card title="Promedios por materia">
            <div class="space-y-4">
                @foreach ($calculo['materias'] as $materiaCalculo)
                    <div class="rounded-2xl border border-blue-400/10 bg-slate-900/40 p-4">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-200/75">{{ $materiaCalculo['materia']->nombre }}</p>
                                <p class="mt-2 text-sm text-base-content/80">
                                    Estado:
                                    <span class="badge {{ $materiaCalculo['estado'] === 'Completo' ? 'badge-success' : 'badge-warning' }}">{{ $materiaCalculo['estado'] }}</span>
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Promedio</p>
                                <p class="mt-2 text-lg font-semibold text-white">{{ $materiaCalculo['promedio'] ?? 'Incompleto' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
@endsection
