@extends('layouts.app')

@section('title', 'CU5 Gestionar Inscripción de Postulantes | Pre-registros')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Pre-registro de Postulantes" subtitle="CU5 Gestionar Inscripción de Postulantes. Este módulo no confirma la inscripción oficial." />
        <a href="{{ route('gestion-postulantes-admision.postulantes.create') }}" class="btn btn-primary">Nuevo</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <div class="alert alert-info mb-6">
        <span>CU5 registra pre-registros. La inscripción oficial se completará después con CU6 requisitos y CU7 pagos.</span>
    </div>

    <x-card title="Búsqueda y filtros">
        <form method="GET" action="{{ route('gestion-postulantes-admision.postulantes.index') }}" class="grid gap-4 md:grid-cols-4">
            <label class="form-control md:col-span-2">
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

            <label class="form-control">
                <span class="label-text">Estado admisión</span>
                <select name="estado_admision" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach (['PENDIENTE', 'ADMITIDO', 'RECHAZADO', 'OBSERVADO'] as $estado)
                        <option value="{{ $estado }}" @selected($estadoAdmision === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('gestion-postulantes-admision.postulantes.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de postulantes">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>CI</th>
                        <th>Nombres y apellidos</th>
                        <th>Correo</th>
                        <th>Primera opción</th>
                        <th>Segunda opción</th>
                        <th>Estado inscripción</th>
                        <th>Estado admisión</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($postulantes as $postulante)
                        <tr>
                            <td>{{ $postulante->ci }}</td>
                            <td>{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                            <td>{{ $postulante->correo ?: 'Sin correo' }}</td>
                            <td>{{ $postulante->carreraPrimeraOpcion?->nombre ?: 'Sin selección' }}</td>
                            <td>{{ $postulante->carreraSegundaOpcion?->nombre ?: 'Sin selección' }}</td>
                            <td>
                                <span class="badge {{ $postulante->estado_inscripcion === 'INSCRITO' ? 'badge-success' : ($postulante->estado_inscripcion === 'OBSERVADO' ? 'badge-error' : 'badge-warning') }}">
                                    {{ $postulante->estado_inscripcion }}
                                </span>
                            </td>
                            <td>
                                <span class="badge {{ $postulante->estado_admision === 'PENDIENTE' ? 'badge-warning' : ($postulante->estado_admision === 'ADMITIDO' ? 'badge-success' : 'badge-error') }}">
                                    {{ $postulante->estado_admision }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-postulantes-admision.postulantes.show', $postulante) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-postulantes-admision.postulantes.edit', $postulante) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-postulantes-admision.postulantes.destroy', $postulante) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas marcar este pre-registro como OBSERVADO?')">Observar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="alert">
                                    <span>No existen postulantes registrados con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $postulantes->links() }}
        </div>
    </x-card>
@endsection
