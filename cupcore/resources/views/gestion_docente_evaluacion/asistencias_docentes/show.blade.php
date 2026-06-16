@extends('layouts.app')

@section('title', 'CU13 Registrar Asistencia Docente | Detalle asistencia')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <x-page-title title="Detalle de asistencia docente" subtitle="Consulta el contexto academico y el registro de asistencia asociado al horario seleccionado." />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-academica-cup.asistencias-docentes.edit', $asistencia) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.asistencias-docentes.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Informacion academica">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Fecha</p><p class="detail-item-value">{{ optional($asistencia->fecha)->format('Y-m-d') ?? $asistencia->fecha }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Gestion</p><p class="detail-item-value">{{ $asistencia->horario?->grupo?->gestion ?: 'Sin gestion' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Dia</p><p class="detail-item-value">{{ $asistencia->horario?->dia_semana ?: 'Sin dia' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Grupo</p><p class="detail-item-value">{{ $asistencia->horario?->grupo?->nombre ?: 'Sin grupo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Materia</p><p class="detail-item-value">{{ $asistencia->horario?->materia?->nombre ?: 'Sin materia' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Aula</p><p class="detail-item-value">{{ $asistencia->horario?->aula?->nombre ?: 'Sin aula' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Hora inicio</p><p class="detail-item-value">{{ substr((string) $asistencia->horario?->hora_inicio, 0, 5) ?: 'Sin hora inicio' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Hora fin</p><p class="detail-item-value">{{ substr((string) $asistencia->horario?->hora_fin, 0, 5) ?: 'Sin hora fin' }}</p></div>
            </div>
        </x-card>

        <x-card title="Informacion del docente">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Docente</p><p class="detail-item-value">{{ trim(($asistencia->docente?->nombres ?? '') . ' ' . ($asistencia->docente?->apellidos ?? '')) ?: 'Sin docente' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">CI</p><p class="detail-item-value">{{ $asistencia->docente?->ci ?: 'Sin CI' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Correo</p><p class="detail-item-value">{{ $asistencia->docente?->correo ?: 'Sin correo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Estado docente</p><p class="detail-item-value">{{ $asistencia->docente?->estado ?: 'Sin estado' }}</p></div>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Registro de asistencia">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Estado asistencia</p><p class="detail-item-value"><span class="badge badge-info">{{ $asistencia->estado_asistencia }}</span></p></div>
                <div class="detail-item"><p class="detail-item-label">Hora registrada</p><p class="detail-item-value">{{ $asistencia->hora_registro ?: 'Sin registro' }}</p></div>
                <div class="detail-item md:col-span-2"><p class="detail-item-label">Observacion</p><p class="detail-item-value">{{ $asistencia->observacion ?: 'Sin observacion registrada' }}</p></div>
            </div>
        </x-card>
    </div>
@endsection
