@extends('layouts.app')

@section('title', 'CU8 Administrar Carreras y Cupos | Cupos')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Cupos por Carrera" subtitle="CU8 Administrar Carreras y Cupos" />
        <a href="{{ route('gestion-postulantes-admision.cupos.create') }}" class="btn btn-primary">Nuevo</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron errores de validación.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <x-card title="Listado de cupos">
        <div class="overflow-x-auto">
            <table class="table">
                <thead>
                    <tr>
                        <th>Carrera</th>
                        <th>Gestión académica</th>
                        <th>Cupo total</th>
                        <th>Cupo disponible</th>
                        <th>Estado</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cupos as $cupo)
                        <tr>
                            <td>{{ $cupo->carrera?->nombre }} ({{ $cupo->carrera?->codigo }})</td>
                            <td>{{ $cupo->gestion }}</td>
                            <td>{{ $cupo->cupo_maximo }}</td>
                            <td>{{ $cupo->cupos_disponibles }}</td>
                            <td>
                                <span class="badge {{ $cupo->estado === 'ACTIVO' ? 'badge-success' : 'badge-error' }}">
                                    {{ $cupo->estado }}
                                </span>
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-postulantes-admision.cupos.show', $cupo) }}" class="btn btn-sm btn-outline">Ver</a>
                                    <a href="{{ route('gestion-postulantes-admision.cupos.edit', $cupo) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-postulantes-admision.cupos.destroy', $cupo) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('¿Deseas desactivar este cupo?')">Desactivar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="alert">
                                    <span>No existen cupos registrados.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $cupos->links() }}
        </div>
    </x-card>
@endsection
