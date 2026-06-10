@extends('layouts.app')

@section('title', 'CU10 Organizar Grupos Academicos | Detalle de grupo')

@section('content')
    <div class="flex flex-wrap items-start justify-between gap-3">
        <x-page-title title="Detalle de grupo" subtitle="Consulta la informacion general del grupo y los postulantes asignados en la gestion academica." />
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('gestion-academica-cup.grupos.edit', $grupo) }}" class="btn btn-info">Editar</a>
            <a href="{{ route('gestion-academica-cup.grupos.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr]">
        <x-card title="Informacion general">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Nombre</p>
                    <p class="mt-2 text-base text-white">{{ $grupo->nombre }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Codigo</p>
                    <p class="mt-2 text-base text-white">{{ $grupo->codigo }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Gestion</p>
                    <p class="mt-2 text-base text-white">{{ $grupo->gestion }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estado</p>
                    <p class="mt-2">
                        <span class="badge {{ $grupo->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $grupo->estado }}</span>
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Capacidad del grupo">
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estudiantes / capacidad maxima</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $grupo->cantidad_estudiantes }} / {{ $grupo->capacidad_maxima }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Porcentaje de ocupacion</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ rtrim(rtrim(number_format($ocupacion, 2, '.', ''), '0'), '.') }}%</p>
                </div>
                <form method="POST" action="{{ route('gestion-academica-cup.grupos.destroy', $grupo) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning w-full" onclick="return confirm('Deseas desactivar este grupo y sus asignaciones?')">Desactivar</button>
                </form>
            </div>
        </x-card>
    </div>

    <x-card title="Postulantes asignados">
        <div class="overflow-x-auto">
            <table class="table min-w-[600px]">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombres</th>
                        <th>Apellidos</th>
                        <th>Correo</th>
                        <th>Estado inscripcion</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grupo->grupoPostulantes as $asignacion)
                        <tr>
                            <td>{{ $asignacion->postulante?->ci ?: 'Sin CI' }}</td>
                            <td>{{ $asignacion->postulante?->nombres ?: 'Sin registro' }}</td>
                            <td>{{ $asignacion->postulante?->apellidos ?: 'Sin registro' }}</td>
                            <td>{{ $asignacion->postulante?->correo ?: 'Sin correo' }}</td>
                            <td>{{ $asignacion->postulante?->estado_inscripcion ?: 'Sin estado' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">
                                <div class="alert">
                                    <span>Este grupo no tiene postulantes activos asignados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
