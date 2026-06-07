@extends('layouts.app')

@section('title', 'CU11 Gestionar Horarios y Aulas | Aulas')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Gestionar aulas" subtitle="Administra aulas activas e inactivas del Curso Preuniversitario FICCT. Las aulas inactivas no podran recibir nuevas asignaciones horarias." />
        <div class="flex gap-2">
            <a href="{{ route('gestion-academica-cup.horarios.index') }}" class="btn btn-outline">Horarios</a>
            <a href="{{ route('gestion-academica-cup.aulas.create') }}" class="btn btn-primary">Nueva aula</a>
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

    <x-card title="Busqueda y filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.aulas.index') }}" class="grid gap-4 md:grid-cols-4">
            <label class="form-control md:col-span-3">
                <span class="label-text">Buscar por nombre, codigo o ubicacion</span>
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
                <a href="{{ route('gestion-academica-cup.aulas.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de aulas">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Codigo</th>
                        <th>Nombre</th>
                        <th>Capacidad</th>
                        <th>Ubicacion</th>
                        <th>Horarios activos</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($aulas as $aula)
                        <tr>
                            <td>{{ $aula->codigo ?: 'Sin codigo' }}</td>
                            <td>{{ $aula->nombre }}</td>
                            <td>{{ $aula->capacidad }}</td>
                            <td>{{ $aula->ubicacion ?: 'Sin ubicacion' }}</td>
                            <td>{{ $aula->horarios_activos_count }}</td>
                            <td><span class="badge {{ $aula->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">{{ $aula->estado }}</span></td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-academica-cup.aulas.show', $aula) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-academica-cup.aulas.edit', $aula) }}" class="btn btn-sm btn-info">Editar</a>
                                    @if ($aula->estado === 'ACTIVO')
                                        <form method="POST" action="{{ route('gestion-academica-cup.aulas.destroy', $aula) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas desactivar esta aula?')">Desactivar</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('gestion-academica-cup.aulas.activar', $aula) }}">
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
                            <td colspan="7"><div class="alert"><span>No existen aulas registradas con esos criterios.</span></div></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $aulas->links() }}</div>
    </x-card>
@endsection
