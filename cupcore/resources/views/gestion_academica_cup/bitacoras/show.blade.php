@extends('layouts.app')

@section('title', 'Detalle de bitacora | CUPCore')

@section('content')
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="Detalle de bitacora" subtitle="Consulta el detalle completo de una accion registrada en el sistema." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.bitacoras.index') }}" class="btn btn-outline w-full sm:w-auto">Volver al listado</a>
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-full sm:w-auto">Dashboard</a>
        </div>
    </div>

    <div class="grid gap-6 2xl:grid-cols-[1.15fr_0.85fr]">
        <x-card title="Accion registrada">
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Accion</p>
                    <p class="mt-2"><span class="badge border border-sky-400/30 bg-sky-500/20 text-sky-100">{{ $bitacora->accion }}</span></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Modulo</p>
                    <p class="mt-2"><span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $bitacora->modulo ?: 'Sin modulo' }}</span></p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Fecha</p>
                    <p class="mt-2 text-white">{{ \Illuminate\Support\Carbon::parse($bitacora->fecha)->format('Y-m-d H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">IP address</p>
                    <p class="mt-2 text-white">{{ $bitacora->ip_address ?: 'Sin IP registrada' }}</p>
                    <p class="mt-1 text-xs text-slate-400">La IP corresponde a la direccion registrada al momento de la accion.</p>
                </div>
                <div class="md:col-span-2">
                    <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Descripcion</p>
                    <div class="mt-2 overflow-x-auto rounded-2xl border border-base-300/60 bg-base-200/50 p-4 whitespace-pre-line break-words text-sm leading-7 text-slate-100">
                        {{ $bitacora->descripcion ?: 'Sin descripcion registrada.' }}
                    </div>
                </div>
            </div>
        </x-card>

        <div class="space-y-6">
            <x-card title="Usuario relacionado">
                @php
                    $usuarioNombre = trim((string) (($bitacora->usuario_nombre ?? '') . ' ' . ($bitacora->usuario_apellido ?? '')));
                @endphp

                <div class="grid gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Nombre completo</p>
                        <p class="mt-2 text-white">{{ $usuarioNombre !== '' ? $usuarioNombre : 'Usuario no disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Correo</p>
                        <p class="mt-2 text-white">{{ $bitacora->usuario_correo ?: 'Usuario no disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">CI</p>
                        <p class="mt-2 text-white">{{ $bitacora->usuario_ci ?: 'Usuario no disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Rol</p>
                        <p class="mt-2 text-white">{{ $bitacora->rol_nombre ?: 'Usuario no disponible' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Estado del usuario</p>
                        <p class="mt-2 text-white">{{ $bitacora->usuario_estado ?: 'Usuario no disponible' }}</p>
                    </div>
                </div>
            </x-card>

            <x-card title="Datos tecnicos">
                <div class="grid gap-4">
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">ID del registro</p>
                        <p class="mt-2 text-white">{{ $bitacora->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Created at</p>
                        <p class="mt-2 text-white">{{ $bitacora->created_at ? \Illuminate\Support\Carbon::parse($bitacora->created_at)->format('Y-m-d H:i') : 'Sin registro' }}</p>
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-[0.22em] text-blue-200/75">Updated at</p>
                        <p class="mt-2 text-white">{{ $bitacora->updated_at ? \Illuminate\Support\Carbon::parse($bitacora->updated_at)->format('Y-m-d H:i') : 'Sin registro' }}</p>
                    </div>
                </div>
            </x-card>
        </div>
    </div>
@endsection
