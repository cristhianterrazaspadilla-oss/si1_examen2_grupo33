@extends('layouts.app')

@section('title', 'Notificaciones enviadas | CUPCore')

@section('content')
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="Notificaciones enviadas" subtitle="Consulta el historial de notificaciones internas enviadas desde tu cuenta." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.notificaciones.index') }}" class="btn btn-outline w-full sm:w-auto">Volver a recibidas</a>
            <a href="{{ route('gestion-academica-cup.notificaciones.create') }}" class="btn btn-primary w-full sm:w-auto">Crear notificacion</a>
            <a href="{{ route('dashboard') }}" class="btn btn-outline w-full sm:w-auto">Dashboard</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6"><x-alert type="success" :message="session('success')" /></div>
    @endif

    @if (session('info'))
        <div class="mb-6"><x-alert type="info" :message="session('info')" /></div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <x-card title="Total enviadas"><p class="text-3xl font-semibold text-white">{{ $totalEnviadas }}</p></x-card>
        <x-card title="Leidas"><p class="text-3xl font-semibold text-emerald-300">{{ $leidas }}</p></x-card>
        <x-card title="No leidas"><p class="text-3xl font-semibold text-blue-200">{{ $noLeidas }}</p></x-card>
    </div>

    <x-card title="Filtros">
        <form method="GET" action="{{ route('gestion-academica-cup.notificaciones.enviadas') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-6">
            <label class="form-control">
                <span class="label-text">Receptor</span>
                <select name="usuario_receptor_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($usuariosActivos as $usuario)
                        <option value="{{ $usuario->id }}" @selected($filters['usuario_receptor_id'] === (string) $usuario->id)>
                            {{ trim($usuario->nombre . ' ' . $usuario->apellido) }}{{ $usuario->rol ? ' - ' . $usuario->rol->nombre : '' }}
                        </option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Tipo</span>
                <select name="tipo" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($tiposNotificacion as $tipo)
                        <option value="{{ $tipo }}" @selected($filters['tipo'] === $tipo)>{{ $tipo }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado lectura</span>
                <select name="estado_lectura" class="select select-bordered">
                    <option value="">Todos</option>
                    <option value="no_leidas" @selected($filters['estado_lectura'] === 'no_leidas')>No leidas</option>
                    <option value="leidas" @selected($filters['estado_lectura'] === 'leidas')>Leidas</option>
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Fecha desde</span>
                <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Fecha hasta</span>
                <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Busqueda</span>
                <input type="text" name="busqueda" value="{{ $filters['busqueda'] }}" class="input input-bordered" placeholder="Titulo, mensaje, tipo o receptor">
            </label>
            <div class="sm:col-span-2 2xl:col-span-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.notificaciones.enviadas') }}" class="btn btn-outline w-full sm:w-auto">Limpiar filtros</a>
            </div>
        </form>
    </x-card>

    <x-card title="Historial de enviadas">
        @if ($notificaciones->isEmpty())
            <div class="alert alert-info">
                <span>No existen notificaciones enviadas para los filtros seleccionados.</span>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table min-w-[980px] text-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Titulo</th>
                            <th>Receptor</th>
                            <th>Estado</th>
                            <th>Fecha lectura</th>
                            <th class="text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notificaciones as $notificacion)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($notificacion->created_at)->format('Y-m-d H:i') }}</td>
                                <td><span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $notificacion->tipo ?: 'GENERAL' }}</span></td>
                                <td class="max-w-[15rem] break-words font-semibold text-white">{{ $notificacion->titulo }}</td>
                                <td>
                                    <div class="text-slate-100">{{ trim(($notificacion->receptor_nombre ?? '') . ' ' . ($notificacion->receptor_apellido ?? '')) }}</div>
                                    <div class="text-xs text-slate-400">{{ $notificacion->receptor_correo ?: 'Sin correo' }}</div>
                                </td>
                                <td><span class="badge {{ $notificacion->leido ? 'badge-success' : 'badge-info' }}">{{ $notificacion->leido ? 'LEIDA' : 'NO LEIDA' }}</span></td>
                                <td>{{ $notificacion->fecha_lectura ? \Illuminate\Support\Carbon::parse($notificacion->fecha_lectura)->format('Y-m-d H:i') : 'Sin lectura' }}</td>
                                <td>
                                    <div class="flex justify-end">
                                        <a href="{{ route('gestion-academica-cup.notificaciones.show', $notificacion->id) }}" class="btn btn-sm btn-outline">Ver detalle</a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $notificaciones->links() }}
            </div>
        @endif
    </x-card>
@endsection
