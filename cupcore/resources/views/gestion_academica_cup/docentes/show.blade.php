@extends('layouts.app')

@section('title', 'CU12 Gestionar Docentes y Asignaciones | Detalle docente')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle del docente" subtitle="Consulta datos personales, datos profesionales, usuario asociado y la carga academica del docente." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.docentes.edit', $docente) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.docentes.asignaciones.create', $docente) }}" class="btn btn-primary">Nueva asignacion</a>
            <a href="{{ route('gestion-academica-cup.docentes.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.1fr_0.9fr]">
        <x-card title="Datos personales">
            <div class="detail-grid cols-2">
                <div class="detail-item">
                    <p class="detail-item-label">CI</p>
                    <p class="detail-item-value">{{ $docente->ci }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Estado</p>
                    <p class="detail-item-value">
                        <span class="badge {{ $docente->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $docente->estado }}</span>
                    </p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Nombres</p>
                    <p class="detail-item-value">{{ $docente->nombres }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Apellidos</p>
                    <p class="detail-item-value">{{ $docente->apellidos }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Correo</p>
                    <p class="detail-item-value">{{ $docente->correo ?: 'Sin correo registrado' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Telefono</p>
                    <p class="detail-item-value">{{ $docente->telefono ?: 'Sin telefono registrado' }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Resumen de carga academica">
            <div class="space-y-5">
                <div class="detail-item">
                    <p class="detail-item-label">Asignaciones activas</p>
                    <p class="detail-item-value">{{ $asignacionesActivasCount }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Usuario asociado</p>
                    <p class="detail-item-value">
                        @if ($docente->usuario)
                            {{ trim($docente->usuario->nombre . ' ' . $docente->usuario->apellido) }}<br>
                            <span class="text-sm font-normal text-base-content/70">{{ $docente->usuario->correo }}</span>
                        @else
                            Sin usuario asociado
                        @endif
                    </p>
                </div>
                @if ($docente->estado === 'ACTIVO')
                    <form method="POST" action="{{ route('gestion-academica-cup.docentes.destroy', $docente) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-warning w-full" onclick="return confirm('Deseas desactivar este docente y sus asignaciones activas?')">Desactivar</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('gestion-academica-cup.docentes.activar', $docente) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-primary w-full">Activar</button>
                    </form>
                @endif
            </div>
        </x-card>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_1fr]">
        <x-card title="Datos profesionales">
            <div class="detail-grid cols-2">
                <div class="detail-item">
                    <p class="detail-item-label">Profesion</p>
                    <p class="detail-item-value">{{ $docente->profesion ?: 'Sin profesion registrada' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Especialidad</p>
                    <p class="detail-item-value">{{ $docente->especialidad ?: 'Sin especialidad registrada' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Tiene maestria</p>
                    <p class="detail-item-value">{{ $docente->tiene_maestria ? 'Si' : 'No' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Tiene diplomado</p>
                    <p class="detail-item-value">{{ $docente->tiene_diplomado ? 'Si' : 'No' }}</p>
                </div>
            </div>
        </x-card>

        <x-card title="Usuario asociado">
            <div class="detail-grid">
                <div class="detail-item">
                    <p class="detail-item-label">Cuenta institucional</p>
                    <p class="detail-item-value">{{ $docente->usuario?->correo ?: 'No existe usuario vinculado' }}</p>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Rol del usuario</p>
                    <p class="detail-item-value">{{ $docente->usuario?->rol?->nombre ?: 'Sin rol asociado' }}</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="mt-6">
        <x-card title="Asignaciones del docente">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Grupo</th>
                            <th>Materia</th>
                            <th>Gestion</th>
                            <th>Estado</th>
                            <th class="text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($docente->asignaciones as $asignacion)
                            <tr>
                                <td>
                                    <div class="font-medium text-white">{{ $asignacion->grupo?->nombre ?: 'Sin grupo' }}</div>
                                    <div class="text-xs text-base-content/70">{{ $asignacion->grupo?->codigo ?: 'Sin codigo' }}</div>
                                </td>
                                <td>
                                    <div class="font-medium text-white">{{ $asignacion->materia?->nombre ?: 'Sin materia' }}</div>
                                    <div class="text-xs text-base-content/70">{{ $asignacion->materia?->codigo ?: 'Sin codigo' }}</div>
                                </td>
                                <td>{{ $asignacion->gestion }}</td>
                                <td>
                                    <span class="badge {{ $asignacion->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $asignacion->estado }}</span>
                                </td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('gestion-academica-cup.docente-asignaciones.edit', $asignacion) }}" class="btn btn-sm btn-info">Editar</a>
                                        @if ($asignacion->estado === 'ACTIVO')
                                            <form method="POST" action="{{ route('gestion-academica-cup.docente-asignaciones.destroy', $asignacion) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar esta asignacion docente?')">Desactivar</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('gestion-academica-cup.docente-asignaciones.activar', $asignacion) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-primary">Activar</button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="alert">
                                        <span>Este docente todavia no tiene asignaciones registradas.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
