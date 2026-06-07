@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Detalle horario')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle del horario" subtitle="Consulta la programacion academica registrada y administra su estado sin borrar historico." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.horarios.edit', $horario) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.horarios.index') }}" class="btn btn-outline">Volver</a>
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

    <div class="grid gap-6 xl:grid-cols-[1fr_0.9fr]">
        <x-card title="Informacion academica">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Gestion</p><p class="detail-item-value">{{ $horario->grupo?->gestion ?: 'Sin gestion' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Estado</p><p class="detail-item-value"><span class="badge {{ $horario->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $horario->estado }}</span></p></div>
                <div class="detail-item"><p class="detail-item-label">Grupo</p><p class="detail-item-value">{{ $horario->grupo?->nombre ?: 'Sin grupo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Materia</p><p class="detail-item-value">{{ $horario->materia?->nombre ?: 'Sin materia' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Docente</p><p class="detail-item-value">{{ trim(($horario->docente?->nombres ?? '') . ' ' . ($horario->docente?->apellidos ?? '')) ?: 'Sin docente' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Aula</p><p class="detail-item-value">{{ $horario->aula?->nombre ?: 'Sin aula' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Dia</p><p class="detail-item-value">{{ $horario->dia_semana }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Bloque horario</p><p class="detail-item-value">{{ substr((string) $horario->hora_inicio, 0, 5) }} - {{ substr((string) $horario->hora_fin, 0, 5) }}</p></div>
            </div>
        </x-card>

        <x-card title="Acciones">
            <div class="space-y-4">
                <div class="detail-item">
                    <p class="detail-item-label">Asignacion academica</p>
                    <p class="detail-item-value">
                        @if ($asignacionActual)
                            {{ $asignacionActual->grupo?->nombre }} — {{ $asignacionActual->materia?->nombre }} — {{ trim(($asignacionActual->docente?->nombres ?? '') . ' ' . ($asignacionActual->docente?->apellidos ?? '')) }} — {{ $asignacionActual->gestion }}
                        @else
                            No se encontro una asignacion docente exacta para este horario.
                        @endif
                    </p>
                </div>
                @if ($horario->estado === 'ACTIVO')
                    <form method="POST" action="{{ route('gestion-academica-cup.horarios.destroy', $horario) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning w-full" onclick="return confirm('Deseas desactivar este horario?')">Desactivar</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('gestion-academica-cup.horarios.activar', $horario) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary w-full">Activar</button>
                    </form>
                @endif
            </div>
        </x-card>
    </div>
@endsection
