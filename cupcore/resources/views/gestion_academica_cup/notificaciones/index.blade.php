@extends('layouts.app')

@section('title', 'CU19 Notificaciones Internas | CUPCore')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <x-page-title title="CU19 Notificaciones Internas" subtitle="Bandeja de notificaciones recibidas." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-outline">Volver al Dashboard</a>
            @if ($canSendNotifications)
                <a href="{{ route('gestion-academica-cup.notificaciones.create') }}" class="btn btn-primary">Crear notificacion</a>
                <a href="{{ route('gestion-academica-cup.notificaciones.enviadas') }}" class="btn btn-info">Ver enviadas</a>
            @endif
            <form method="POST" action="{{ route('gestion-academica-cup.notificaciones.marcar-todas-leidas') }}">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-outline">Marcar todas como leidas</button>
            </form>
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

    <div class="mb-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <x-card title="Total recibidas"><p class="text-3xl font-semibold text-white">{{ $totalRecibidas }}</p></x-card>
        <x-card title="No leidas"><p class="text-3xl font-semibold text-blue-200">{{ $noLeidas }}</p></x-card>
        <x-card title="Leidas"><p class="text-3xl font-semibold text-emerald-300">{{ $leidas }}</p></x-card>
        <x-card title="Pendientes globales"><p class="text-3xl font-semibold text-amber-300">{{ $contadorNoLeidas }}</p></x-card>
    </div>

    <x-card title="Filtros de consulta">
        <form method="GET" action="{{ route('gestion-academica-cup.notificaciones.index') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
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
                <input type="text" name="busqueda" value="{{ $filters['busqueda'] }}" class="input input-bordered" placeholder="Titulo, mensaje, tipo o emisor">
            </label>
            <div class="md:col-span-2 xl:col-span-5 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.notificaciones.index') }}" class="btn btn-outline">Limpiar filtros</a>
            </div>
        </form>
    </x-card>

    <x-card title="Bandeja de entrada">
        @if ($notificaciones->isEmpty())
            <div class="alert alert-info">
                <span>No tienes notificaciones internas para los filtros seleccionados.</span>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Titulo</th>
                            <th>Emisor</th>
                            <th>Mensaje resumido</th>
                            <th class="text-right">Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($notificaciones as $notificacion)
                            <tr class="{{ $notificacion->leido ? '' : 'bg-blue-500/5' }}">
                                <td>
                                    <span class="badge {{ $notificacion->leido ? 'badge-success' : 'badge-info' }}">
                                        {{ $notificacion->leido ? 'LEIDA' : 'NO LEIDA' }}
                                    </span>
                                </td>
                                <td>{{ \Illuminate\Support\Carbon::parse($notificacion->created_at)->format('Y-m-d H:i') }}</td>
                                <td><span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $notificacion->tipo ?: 'GENERAL' }}</span></td>
                                <td class="font-semibold text-white">{{ $notificacion->titulo }}</td>
                                <td>
                                    <div class="text-slate-100">
                                        {{ trim(($notificacion->emisor_nombre ?? '') . ' ' . ($notificacion->emisor_apellido ?? '')) !== '' ? trim(($notificacion->emisor_nombre ?? '') . ' ' . ($notificacion->emisor_apellido ?? '')) : 'Sistema' }}
                                    </div>
                                    <div class="text-xs text-slate-400">{{ $notificacion->emisor_correo ?: 'Sin correo' }}</div>
                                </td>
                                <td class="max-w-sm whitespace-normal break-words text-sm leading-7 text-slate-100">
                                    {{ \Illuminate\Support\Str::limit($notificacion->mensaje, 120) }}
                                </td>
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
