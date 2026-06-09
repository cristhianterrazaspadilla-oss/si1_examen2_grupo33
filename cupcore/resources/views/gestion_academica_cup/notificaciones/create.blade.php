@extends('layouts.app')

@section('title', 'Crear notificacion interna | CUPCore')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <x-page-title title="Crear notificacion interna" subtitle="La notificacion se mostrara dentro del sistema al usuario destinatario." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.notificaciones.index') }}" class="btn btn-outline">Cancelar</a>
            <a href="{{ route('gestion-academica-cup.notificaciones.enviadas') }}" class="btn btn-info">Ver enviadas</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Formulario de notificacion">
        <form method="POST" action="{{ route('gestion-academica-cup.notificaciones.store') }}" class="app-form">
            @csrf

            <section class="app-form-section">
                <h2 class="app-section-title">Destinatario y tipo</h2>
                <p class="app-section-subtitle">Selecciona el usuario receptor y clasifica la notificacion para facilitar el seguimiento.</p>
                <div class="app-form-grid cols-2">
                    <label class="form-control">
                        <span class="label-text">Destinatario</span>
                        <select name="usuario_receptor_id" class="select select-bordered" required>
                            <option value="">Selecciona un usuario activo</option>
                            @foreach ($usuariosActivos as $usuario)
                                <option value="{{ $usuario->id }}" @selected((string) old('usuario_receptor_id') === (string) $usuario->id)>
                                    {{ trim($usuario->nombre . ' ' . $usuario->apellido) }} | {{ $usuario->correo }}{{ $usuario->rol ? ' | ' . $usuario->rol->nombre : '' }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Tipo</span>
                        <select name="tipo" class="select select-bordered">
                            @foreach ($tiposNotificacion as $tipo)
                                <option value="{{ $tipo }}" @selected(old('tipo', 'GENERAL') === $tipo)>{{ $tipo }}</option>
                            @endforeach
                        </select>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Contenido</h2>
                <div class="app-form-grid">
                    <label class="form-control">
                        <span class="label-text">Titulo</span>
                        <input type="text" name="titulo" value="{{ old('titulo') }}" class="input input-bordered" maxlength="150" required>
                    </label>
                    <label class="form-control">
                        <span class="label-text">Mensaje</span>
                        <textarea name="mensaje" class="textarea textarea-bordered" maxlength="1000" required>{{ old('mensaje') }}</textarea>
                    </label>
                </div>
            </section>

            <section class="app-form-section">
                <h2 class="app-section-title">Roles activos</h2>
                <div class="flex flex-wrap gap-2">
                    @foreach ($rolesActivos as $rol)
                        <span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $rol->nombre }}</span>
                    @endforeach
                </div>
            </section>

            <div class="app-form-actions">
                <button type="submit" class="btn btn-primary">Enviar notificacion</button>
                <a href="{{ route('gestion-academica-cup.notificaciones.index') }}" class="btn btn-outline">Cancelar</a>
            </div>
        </form>
    </x-card>
@endsection
