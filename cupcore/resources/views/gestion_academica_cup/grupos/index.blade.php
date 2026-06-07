@extends('layouts.app')

@section('title', 'CU10 Organizar Grupos Academicos | CUPCore')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Organizar Grupos Academicos" subtitle="CU10 del paquete Gestion Academica del CUP. Distribuye automaticamente postulantes INSCRITOS en grupos con capacidad maxima controlada." />
        <a href="{{ route('gestion-academica-cup.grupos.organizar') }}" class="btn btn-primary">Organizar grupos</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info mb-6">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron observaciones.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card title="Total grupos activos">
            <p class="text-3xl font-semibold text-white">{{ $totalGruposActivos }}</p>
            <p class="text-sm text-base-content/70">Gestion de referencia: {{ $gestionResumen }}</p>
        </x-card>
        <x-card title="Estudiantes asignados">
            <p class="text-3xl font-semibold text-white">{{ $totalEstudiantesAsignados }}</p>
            <p class="text-sm text-base-content/70">Asignaciones activas en grupos activos</p>
        </x-card>
        <x-card title="Capacidad total">
            <p class="text-3xl font-semibold text-white">{{ $capacidadTotal }}</p>
            <p class="text-sm text-base-content/70">Suma de capacidad maxima</p>
        </x-card>
        <x-card title="Inscritos sin grupo">
            <p class="text-3xl font-semibold text-white">{{ $postulantesSinGrupo }}</p>
            <p class="text-sm text-base-content/70">Disponibles para asignar en {{ $gestionResumen }}</p>
        </x-card>
    </div>

    <x-card title="Busqueda y filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.grupos.index') }}" class="grid gap-4 md:grid-cols-4">
            <label class="form-control md:col-span-2">
                <span class="label-text">Buscar por nombre, codigo o gestion</span>
                <input type="text" name="search" value="{{ $search }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($gestionesAcademicas as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($gestion === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
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
                <a href="{{ route('gestion-academica-cup.grupos.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de grupos">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Gestion</th>
                        <th>Capacidad maxima</th>
                        <th>Estudiantes</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($grupos as $grupo)
                        <tr>
                            <td>{{ $grupo->codigo }}</td>
                            <td>{{ $grupo->nombre }}</td>
                            <td>{{ $grupo->gestion }}</td>
                            <td>{{ $grupo->capacidad_maxima }}</td>
                            <td>{{ $grupo->cantidad_estudiantes }}</td>
                            <td>
                                <span class="badge {{ $grupo->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $grupo->estado }}</span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.grupos.show', $grupo) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.grupos.edit', $grupo) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-academica-cup.grupos.destroy', $grupo) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar este grupo?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="alert">
                                    <span>No existen grupos registrados con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $grupos->links() }}
        </div>
    </x-card>
@endsection
