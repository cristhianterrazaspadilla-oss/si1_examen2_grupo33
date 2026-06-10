@extends('layouts.app')

@section('title', 'Detalle de notificacion | CUPCore')

@section('content')
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="Detalle de notificacion" subtitle="Consulta el contenido completo de la notificacion interna y su estado de lectura." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.notificaciones.index') }}" class="btn btn-outline w-full sm:w-auto">Volver a recibidas</a>
            @if ($canSendNotifications)
                <a href="{{ route('gestion-academica-cup.notificaciones.enviadas') }}" class="btn btn-info w-full sm:w-auto">Volver a enviadas</a>
            @endif
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

    <div class="grid gap-6 2xl:grid-cols-[1.2fr_0.8fr]">
        <x-card title="Notificacion">
            <div class="detail-grid">
                <div class="detail-item">
                    <p class="detail-item-label">Titulo</p>
                    <p class="detail-item-value">{{ $notificacion->titulo }}</p>
                </div>
                <div class="detail-grid cols-2">
                    <div class="detail-item">
                        <p class="detail-item-label">Tipo</p>
                        <p class="detail-item-value"><span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $notificacion->tipo ?: 'GENERAL' }}</span></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-item-label">Estado</p>
                        <p class="detail-item-value"><span class="badge {{ $notificacion->leido ? 'badge-success' : 'badge-info' }}">{{ $notificacion->leido ? 'LEIDA' : 'NO LEIDA' }}</span></p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-item-label">Fecha creacion</p>
                        <p class="detail-item-value">{{ optional($notificacion->created_at)->format('Y-m-d H:i') ?: 'Sin fecha' }}</p>
                    </div>
                    <div class="detail-item">
                        <p class="detail-item-label">Fecha lectura</p>
                        <p class="detail-item-value">{{ optional($notificacion->fecha_lectura)->format('Y-m-d H:i') ?: 'Sin lectura' }}</p>
                    </div>
                </div>
                <div class="detail-item">
                    <p class="detail-item-label">Mensaje completo</p>
                    <div class="detail-item-value break-words">{!! nl2br(e($notificacion->mensaje)) !!}</div>
                </div>
            </div>

            @if ($canMarkAsRead)
                <form method="POST" action="{{ route('gestion-academica-cup.notificaciones.marcar-leida', $notificacion) }}" class="mt-6">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Marcar como leida</button>
                </form>
            @endif
        </x-card>

        <div class="space-y-6">
            <x-card title="Emisor">
                <div class="detail-grid">
                    <div class="detail-item"><p class="detail-item-label">Nombre</p><p class="detail-item-value">{{ $notificacion->usuarioEmisor ? trim($notificacion->usuarioEmisor->nombre . ' ' . $notificacion->usuarioEmisor->apellido) : 'Sistema' }}</p></div>
                    <div class="detail-item"><p class="detail-item-label">Correo</p><p class="detail-item-value">{{ $notificacion->usuarioEmisor?->correo ?: 'Sin correo' }}</p></div>
                    <div class="detail-item"><p class="detail-item-label">Rol</p><p class="detail-item-value">{{ $notificacion->usuarioEmisor?->rol?->nombre ?: 'Sistema' }}</p></div>
                </div>
            </x-card>

            <x-card title="Receptor">
                <div class="detail-grid">
                    <div class="detail-item"><p class="detail-item-label">Nombre</p><p class="detail-item-value">{{ trim(($notificacion->usuarioReceptor?->nombre ?? '') . ' ' . ($notificacion->usuarioReceptor?->apellido ?? '')) }}</p></div>
                    <div class="detail-item"><p class="detail-item-label">Correo</p><p class="detail-item-value">{{ $notificacion->usuarioReceptor?->correo ?: 'Sin correo' }}</p></div>
                    <div class="detail-item"><p class="detail-item-label">Rol</p><p class="detail-item-value">{{ $notificacion->usuarioReceptor?->rol?->nombre ?: 'Sin rol' }}</p></div>
                    <div class="detail-item"><p class="detail-item-label">Ultima actualizacion</p><p class="detail-item-value">{{ optional($notificacion->updated_at)->format('Y-m-d H:i') ?: 'Sin fecha' }}</p></div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
