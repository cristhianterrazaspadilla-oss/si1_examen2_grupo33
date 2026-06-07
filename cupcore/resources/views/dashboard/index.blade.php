@extends('layouts.app')

@section('title', 'Dashboard | CUPCore')

@section('content')
    <section class="space-y-6">
        <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
            <div class="overflow-hidden rounded-[2rem] border border-blue-300/12 bg-white/6 p-6 shadow-[0_25px_80px_rgba(2,6,23,0.55)] backdrop-blur-2xl sm:p-8">
                <div class="absolute pointer-events-none"></div>
                <div class="flex flex-col gap-8 xl:flex-row xl:items-start xl:justify-between">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-200/75">Panel central</p>
                        <h2 class="mt-4 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                            Bienvenido{{ $userName !== '' ? ', ' . $userName : '' }}
                        </h2>
                        <p class="mt-4 max-w-2xl text-sm leading-8 text-slate-300 sm:text-base">
                            Gestiona la operacion academica del Curso Preuniversitario FICCT desde un entorno unificado. Este dashboard organiza los casos de uso por paquetes y muestra el estado actual de implementacion.
                        </p>
                    </div>

                    <div class="grid w-full gap-4 sm:grid-cols-2 xl:max-w-lg xl:grid-cols-3">
                        <div class="min-w-0 rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-4 sm:p-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Paquetes visibles</p>
                            <p class="mt-3 text-3xl font-semibold text-white sm:text-[2rem]">{{ $packages->count() }}</p>
                        </div>
                        <div class="min-w-0 rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-4 sm:p-5">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Implementados</p>
                            <p class="mt-3 text-3xl font-semibold text-emerald-300 sm:text-[2rem]">{{ $implementedCount }}</p>
                        </div>
                        <div class="min-w-0 rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-4 sm:p-5 sm:col-span-2 xl:col-span-1">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-400">Pendientes</p>
                            <p class="mt-3 text-3xl font-semibold text-amber-300 sm:text-[2rem]">{{ $pendingCount }}</p>
                        </div>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert mt-8 border border-emerald-400/20 bg-emerald-500/10 text-emerald-100 shadow-none">
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
            </div>

            <div class="rounded-[2rem] border border-blue-300/12 bg-white/6 p-6 shadow-[0_25px_80px_rgba(2,6,23,0.55)] backdrop-blur-2xl sm:p-8">
                <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-200/75">Sesion</p>
                <div class="mt-6 space-y-4">
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-5">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Usuario autenticado</p>
                        <p class="mt-3 text-xl font-semibold text-white">{{ $userName !== '' ? $userName : 'Usuario autenticado' }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-5">
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-500">Rol visual</p>
                        <p class="mt-3 text-xl font-semibold text-blue-200">{{ $roleName }}</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-5 text-sm leading-7 text-slate-300">
                        El dashboard filtra visualmente los paquetes segun el rol autenticado. Los casos implementados muestran enlaces activos y los pendientes se mantienen visibles sin rutas operativas.
                    </div>
                </div>
            </div>
        </div>

        <div class="grid gap-6 2xl:grid-cols-2">
            @foreach ($packages as $package)
                <article class="relative overflow-hidden rounded-[2rem] border border-blue-300/12 bg-white/6 p-6 shadow-[0_25px_80px_rgba(2,6,23,0.55)] backdrop-blur-2xl sm:p-8">
                    <div class="absolute inset-x-0 top-0 h-32 bg-gradient-to-r {{ $package['accent'] }}"></div>
                    <div class="relative">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div class="max-w-2xl">
                                <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-200/75">Paquete funcional</p>
                                <h3 class="mt-3 text-2xl font-semibold tracking-tight text-white">{{ $package['title'] }}</h3>
                                <p class="mt-3 text-sm leading-7 text-slate-300">{{ $package['description'] }}</p>
                            </div>
                            <div class="inline-flex items-center gap-2 rounded-full border border-white/8 bg-slate-950/35 px-4 py-2 text-xs font-semibold uppercase tracking-[0.22em] text-slate-300">
                                <span class="h-2.5 w-2.5 rounded-full bg-blue-300"></span>
                                {{ collect($package['use_cases'])->where('status', 'Implementado')->count() }} activos
                            </div>
                        </div>

                        <div class="mt-8 grid gap-4">
                            @foreach ($package['use_cases'] as $useCase)
                                <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-5">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                                        <div>
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex rounded-full border border-blue-300/15 bg-blue-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.22em] text-blue-200">
                                                    {{ $useCase['code'] }}
                                                </span>
                                                <span class="badge {{ $useCase['status'] === 'Implementado' ? 'badge-success' : 'badge-warning' }}">
                                                    {{ $useCase['status'] }}
                                                </span>
                                            </div>
                                            <h4 class="mt-4 text-lg font-semibold text-white">{{ $useCase['title'] }}</h4>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @if ($useCase['status'] === 'Implementado' && count($useCase['links']) > 0)
                                                @foreach ($useCase['links'] as $link)
                                                    <a href="{{ $link['url'] }}" class="btn btn-sm rounded-xl border-0 bg-[linear-gradient(135deg,_#2563eb_0%,_#1d4ed8_45%,_#0ea5e9_100%)] text-white shadow-[0_14px_30px_rgba(29,78,216,0.28)] hover:brightness-110">
                                                        {{ $link['label'] }}
                                                    </a>
                                                @endforeach
                                            @else
                                                <button type="button" class="btn btn-sm rounded-xl border border-white/8 bg-slate-900/60 text-slate-400 shadow-none" disabled>
                                                    Pendiente
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </section>
@endsection
