@extends('layouts.app')

@section('title', 'CU12 Gestionar Docentes y Asignaciones | CUPCore')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="CU12 Gestionar Docentes y Asignaciones" subtitle="Registra, consulta, modifica y desactiva docentes del Curso Preuniversitario FICCT, junto con sus asignaciones academicas activas." />
        <a href="{{ route('gestion-academica-cup.docentes.create') }}" class="btn btn-primary">Nuevo docente</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if (session('info'))
        <div class="mb-6">
            <x-alert type="info" :message="session('info')" />
        </div>
    @endif

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card title="Docentes activos">
            <p class="text-3xl font-semibold text-white">{{ $totalDocentesActivos }}</p>
        </x-card>
        <x-card title="Docentes inactivos">
            <p class="text-3xl font-semibold text-white">{{ $docentesInactivos }}</p>
        </x-card>
        <x-card title="Asignaciones activas">
            <p class="text-3xl font-semibold text-white">{{ $asignacionesActivas }}</p>
        </x-card>
        <x-card title="Docentes sin asignacion">
            <p class="text-3xl font-semibold text-white">{{ $docentesSinAsignacion }}</p>
        </x-card>
    </div>

    <x-card title="Busqueda y filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.docentes.index') }}" class="grid gap-4 md:grid-cols-4">
            <label class="form-control md:col-span-3">
                <span class="label-text">Buscar por CI, nombres, apellidos, correo, profesion o especialidad</span>
                <input type="text" name="search" value="{{ $search }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Estado</span>
                <select name="estado" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach (['ACTIVO', 'INACTIVO'] as $estadoOption)
                        <option value="{{ $estadoOption }}" @selected($estado === $estadoOption)>{{ $estadoOption }}</option>
                    @endforeach
                </select>
            </label>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('gestion-academica-cup.docentes.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de docentes">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Docente</th>
                        <th>Correo</th>
                        <th>Profesion</th>
                        <th>Especialidad</th>
                        <th>Asignaciones activas</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($docentes as $docente)
                        <tr>
                            <td>{{ $docente->ci }}</td>
                            <td>
                                <div class="font-medium text-white">{{ trim($docente->nombres . ' ' . $docente->apellidos) }}</div>
                                <div class="text-xs text-base-content/70">{{ $docente->usuario?->correo ?: 'Sin usuario asociado' }}</div>
                            </td>
                            <td>{{ $docente->correo ?: 'Sin correo' }}</td>
                            <td>{{ $docente->profesion ?: 'Sin registro' }}</td>
                            <td>{{ $docente->especialidad ?: 'Sin registro' }}</td>
                            <td>{{ $docente->asignaciones_activas_count }}</td>
                            <td>
                                <span class="badge {{ $docente->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $docente->estado }}</span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.docentes.show', $docente) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.docentes.edit', $docente) }}" class="btn btn-sm btn-info">Editar</a>
                                    <a href="{{ route('gestion-academica-cup.docentes.asignaciones.create', $docente) }}" class="btn btn-sm btn-primary">Asignar</a>
                                    @if ($docente->estado === 'ACTIVO')
                                        <form method="POST" action="{{ route('gestion-academica-cup.docentes.destroy', $docente) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar este docente y sus asignaciones activas?')">Desactivar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('gestion-academica-cup.docentes.activar', $docente) }}">
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
                            <td colspan="8">
                                <div class="alert">
                                    <span>No existen docentes registrados con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $docentes->links() }}
        </div>
    </x-card>
@endsection
