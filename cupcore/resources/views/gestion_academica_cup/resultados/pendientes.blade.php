@extends('layouts.app')

@section('title', 'CU15 Resultados Pendientes | CUPCore')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Postulantes pendientes de resultado" subtitle="Consulta postulantes inscritos sin resultado generado y revisa si tienen notas completas antes de calcular su admision final." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.resultados.index') }}" class="btn btn-outline">Volver a resultados</a>
            <a href="{{ route('gestion-academica-cup.resultados.generar') }}" class="btn btn-primary">Generar resultado</a>
        </div>
    </div>

    <div class="mb-6 rounded-2xl border border-blue-400/15 bg-blue-500/10 px-4 py-3 text-sm text-blue-100/80">
        Promedio minimo de aprobacion: {{ $notaMinimaAprobacion }}. Solo se podran generar resultados cuando todas las materias activas tengan sus tres notas registradas.
    </div>

    <x-card title="Pendientes">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Grupo</th>
                        <th>Gestion</th>
                        <th>Estado de notas</th>
                        <th>Evaluaciones faltantes</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pendientes as $item)
                        <tr>
                            <td>{{ trim($item['postulante']->nombres . ' ' . $item['postulante']->apellidos) }}</td>
                            <td>{{ $item['postulante']->ci }}</td>
                            <td>{{ $item['grupoActivo']?->grupo?->nombre ?: 'Sin grupo activo' }}</td>
                            <td>{{ $item['grupoActivo']?->grupo?->gestion ?: 'Sin gestion' }}</td>
                            <td><span class="badge {{ $item['estado_notas'] === 'Completo' ? 'badge-success' : 'badge-warning' }}">{{ $item['estado_notas'] }}</span></td>
                            <td>{{ $item['faltantes'] !== [] ? implode(', ', $item['faltantes']) : 'Ninguna' }}</td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    @if ($item['estado_notas'] === 'Completo')
                                        <form method="POST" action="{{ route('gestion-academica-cup.resultados.store') }}">
                                            @csrf
                                            <input type="hidden" name="postulante_id" value="{{ $item['postulante']->id }}">
                                            <button type="submit" class="btn btn-sm btn-primary">Generar</button>
                                        </form>
                                    @else
                                        <span class="badge badge-ghost">Pendiente</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="alert">
                                    <span>No existen postulantes pendientes sin resultado en este momento.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
@endsection
