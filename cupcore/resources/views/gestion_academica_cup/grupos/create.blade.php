@extends('layouts.app')

@section('title', 'CU10 Organizar Grupos Academicos | Organizar grupos')

@section('content')
    <x-page-title title="Organizar grupos automaticamente" subtitle="El sistema organizara postulantes inscritos en grupos de maximo 70 estudiantes segun la gestion academica seleccionada." />

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

    <div class="grid gap-6 lg:grid-cols-[1.05fr_0.95fr]">
        <x-card title="Configuracion de organizacion">
            <form method="GET" action="{{ route('gestion-academica-cup.grupos.organizar') }}" class="app-form">
                <section class="app-form-section">
                    <h2 class="app-section-title">Parametros de calculo</h2>
                    <div class="app-form-grid cols-2">
                        <label class="form-control">
                            <span class="label-text">Gestion</span>
                            <select name="gestion" class="select select-bordered w-full" required>
                                @foreach ($gestionesAcademicas as $gestionOption)
                                    <option value="{{ $gestionOption }}" @selected($gestion === $gestionOption)>{{ $gestionOption }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Capacidad maxima</span>
                            <input type="number" name="capacidad_maxima" value="{{ $capacidadMaxima }}" class="input input-bordered w-full" min="1" max="70" required>
                        </label>
                    </div>
                </section>

                <div class="app-form-actions flex flex-wrap gap-2">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto">Actualizar calculo</button>
                    <a href="{{ route('gestion-academica-cup.grupos.index') }}" class="btn btn-outline w-full sm:w-auto">Volver</a>
                </div>
            </form>

            <form method="POST" action="{{ route('gestion-academica-cup.grupos.organizar.store') }}" class="mt-6 border-t border-blue-300/10 pt-6">
                @csrf
                <input type="hidden" name="gestion" value="{{ $gestion }}">
                <input type="hidden" name="capacidad_maxima" value="{{ $capacidadMaxima }}">

                <div class="space-y-3 text-sm text-base-content/75">
                    <p>Este calculo corresponde a la gestion seleccionada: <span class="font-semibold text-white">{{ $gestion }}</span>.</p>
                    <p>Con capacidad <span class="font-semibold text-white">{{ $capacidadMaxima }}</span>, el sistema generara <span class="font-semibold text-white">{{ $resumen['grupos_necesarios'] }}</span> grupo(s).</p>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary w-full sm:w-auto" @disabled($resumen['disponibles'] === 0 || $resumen['grupos_activos_gestion'] > 0)>
                        Generar grupos
                    </button>
                </div>
            </form>
        </x-card>

        <x-card title="Calculo previo">
            <div class="space-y-5">
                <div class="alert alert-info">
                    <span>Este calculo corresponde a la gestion seleccionada: {{ $gestion }}.</span>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Total postulantes inscritos</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $resumen['inscritos'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Ya asignados en grupos activos</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $resumen['ya_asignados'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Disponibles para asignar</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $resumen['disponibles'] }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Grupos necesarios</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ $resumen['grupos_necesarios'] }}</p>
                </div>
                <div class="alert alert-info">
                    <span>Con capacidad {{ $resumen['capacidad_maxima'] }}, el sistema generara {{ $resumen['grupos_necesarios'] }} grupo(s).</span>
                </div>
                @if ($resumen['disponibles'] === 0)
                    <div class="alert alert-info">
                        <span>No hay postulantes disponibles para asignar en esta gestion.</span>
                    </div>
                @endif
                @if ($resumen['grupos_activos_gestion'] > 0)
                    <div class="alert alert-warning">
                        <span>Ya existen grupos activos para esta gestion. No se generaran duplicados.</span>
                    </div>
                @endif
            </div>
        </x-card>
    </div>
@endsection
