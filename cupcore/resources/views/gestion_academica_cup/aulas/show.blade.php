@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Detalle aula')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle del aula" subtitle="Consulta informacion general del aula y los horarios activos actualmente asociados." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.aulas.edit', $aula) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.aulas.index') }}" class="btn btn-outline">Volver</a>
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
        <x-card title="Informacion general">
            <div class="detail-grid cols-2">
                <div class="detail-item"><p class="detail-item-label">Nombre</p><p class="detail-item-value">{{ $aula->nombre }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Codigo</p><p class="detail-item-value">{{ $aula->codigo ?: 'Sin codigo' }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Capacidad</p><p class="detail-item-value">{{ $aula->capacidad }}</p></div>
                <div class="detail-item"><p class="detail-item-label">Estado</p><p class="detail-item-value"><span class="badge {{ $aula->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $aula->estado }}</span></p></div>
                <div class="detail-item md:col-span-2"><p class="detail-item-label">Ubicacion</p><p class="detail-item-value">{{ $aula->ubicacion ?: 'Sin ubicacion registrada' }}</p></div>
            </div>
        </x-card>

        <x-card title="Acciones">
            <div class="space-y-4">
                @if ($aula->estado === 'ACTIVO')
                    <form method="POST" action="{{ route('gestion-academica-cup.aulas.destroy', $aula) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning w-full" onclick="return confirm('Deseas desactivar esta aula?')">Desactivar</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('gestion-academica-cup.aulas.activar', $aula) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary w-full">Activar</button>
                    </form>
                @endif
                <a href="{{ route('gestion-academica-cup.horarios.create') }}" class="btn btn-outline w-full">Nuevo horario</a>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Horarios activos asociados">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Gestion</th>
                            <th>Grupo</th>
                            <th>Materia</th>
                            <th>Docente</th>
                            <th>Dia</th>
                            <th>Bloque horario</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($horariosActivos as $horario)
                            <tr>
                                <td>{{ $horario->gestion }}</td>
                                <td>{{ $horario->grupo_nombre }}</td>
                                <td>{{ $horario->materia_nombre }}</td>
                                <td>{{ trim($horario->docente_nombres . ' ' . $horario->docente_apellidos) }}</td>
                                <td>{{ $horario->dia_semana }}</td>
                                <td>{{ substr($horario->hora_inicio, 0, 5) }} - {{ substr($horario->hora_fin, 0, 5) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6"><div class="alert"><span>Esta aula no tiene horarios activos asociados.</span></div></td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
