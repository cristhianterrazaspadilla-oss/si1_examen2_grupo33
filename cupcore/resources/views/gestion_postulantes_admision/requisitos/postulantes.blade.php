@extends('layouts.app')

@section('title', 'CU6 Gestionar Requisitos de Admisión | Validación de Postulantes')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <x-page-title title="Validación de Requisitos por Postulante" subtitle="CU6 Gestionar Requisitos de Admisión" />
        <a href="{{ route('gestion-postulantes-admision.requisitos.index') }}" class="btn btn-outline w-full sm:w-auto">Volver al catálogo</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Búsqueda de postulantes">
        <form method="GET" action="{{ route('gestion-postulantes-admision.requisitos-postulantes.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <label class="form-control sm:col-span-2">
                <span class="label-text">Buscar por CI, nombres, apellidos o correo</span>
                <input type="text" name="search" value="{{ $search }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Estado inscripción</span>
                <select name="estado_inscripcion" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach (['PRE_REGISTRADO', 'REQUISITOS_APROBADOS', 'PAGO_PENDIENTE', 'INSCRITO', 'OBSERVADO'] as $estado)
                        <option value="{{ $estado }}" @selected($estadoInscripcion === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>
            <div class="flex items-end gap-2 w-full">
                <button type="submit" class="btn btn-primary flex-1">Buscar</button>
                <a href="{{ route('gestion-postulantes-admision.requisitos-postulantes.index') }}" class="btn btn-outline flex-1">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Postulantes para validación">
        <div class="overflow-x-auto">
            <table class="table min-w-[1000px]">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombres y apellidos</th>
                        <th>Correo</th>
                        <th>Estado inscripción</th>
                        <th>Resumen de requisitos</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($postulantes as $postulante)
                        @php
                            $obligatorios = $postulante->postulanteRequisitos->filter(fn ($item) => $item->requisito && $item->requisito->estado === 'ACTIVO' && $item->requisito->obligatorio);
                            $aprobados = $obligatorios->where('estado', 'APROBADO')->count();
                            $pendientes = $obligatorios->where('estado', 'PENDIENTE')->count();
                            $observados = $obligatorios->where('estado', 'OBSERVADO')->count();
                        @endphp
                        <tr>
                            <td>{{ $postulante->ci }}</td>
                            <td>{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                            <td>{{ $postulante->correo ?: 'Sin correo' }}</td>
                            <td>
                                <span class="badge {{ $postulante->estado_inscripcion === 'REQUISITOS_APROBADOS' ? 'badge-success' : ($postulante->estado_inscripcion === 'OBSERVADO' ? 'badge-error' : 'badge-warning') }}">
                                    {{ $postulante->estado_inscripcion }}
                                </span>
                            </td>
                            <td>
                                <div class="text-sm">
                                    <p>Total obligatorios: {{ $totalObligatorios }}</p>
                                    <p>Aprobados: {{ $aprobados }}</p>
                                    <p>Pendientes: {{ $pendientes }}</p>
                                    <p>Observados: {{ $observados }}</p>
                                </div>
                            </td>
                            <td class="text-right">
                                <a href="{{ route('gestion-postulantes-admision.requisitos-postulantes.show', $postulante) }}" class="btn btn-sm btn-primary">Validar requisitos</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="alert">
                                    <span>No existen postulantes para validar con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $postulantes->links() }}</div>
    </x-card>
@endsection
