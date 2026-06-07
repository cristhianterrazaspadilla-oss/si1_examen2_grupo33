@extends('layouts.app')

@section('title', 'CU5 Gestionar Inscripcion de Postulantes | Detalle')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Detalle de Postulante" subtitle="CU5 Gestionar Inscripcion de Postulantes" />
        <div class="flex gap-2">
            <a href="{{ route('gestion-postulantes-admision.postulantes.edit', $postulante) }}" class="btn btn-info">Editar</a>
            <form method="POST" action="{{ route('gestion-postulantes-admision.postulantes.destroy', $postulante) }}">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-warning" onclick="return confirm('Deseas marcar este pre-registro como OBSERVADO?')">Observar</button>
            </form>
            <a href="{{ route('gestion-postulantes-admision.postulantes.index') }}" class="btn btn-outline">Volver</a>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    <x-card title="Informacion personal y academica">
        <div class="detail-grid cols-2">
            <div class="detail-item"><p class="detail-item-label">Nombre completo</p><p class="detail-item-value">{{ $postulante->nombres }} {{ $postulante->apellidos }}</p></div>
            <div class="detail-item"><p class="detail-item-label">CI</p><p class="detail-item-value">{{ $postulante->ci }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Usuario asociado</p><p class="detail-item-value">{{ $postulante->usuario ? $postulante->usuario->nombre.' '.$postulante->usuario->apellido : 'Sin asociar' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Rol</p><p class="detail-item-value">{{ $postulante->usuario?->rol?->nombre ?: 'No aplica' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Fecha de nacimiento</p><p class="detail-item-value">{{ optional($postulante->fecha_nacimiento)->format('d/m/Y') ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Sexo</p><p class="detail-item-value">{{ $postulante->sexo ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Tipo de sangre</p><p class="detail-item-value">{{ $postulante->tipo_sangre ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Titulo de bachiller</p><p class="detail-item-value">{{ $postulante->titulo_bachiller ? 'Si' : 'No / no especificado' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Correo</p><p class="detail-item-value">{{ $postulante->correo ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Telefono</p><p class="detail-item-value">{{ $postulante->telefono ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Ciudad</p><p class="detail-item-value">{{ $postulante->ciudad ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Colegio de procedencia</p><p class="detail-item-value">{{ $postulante->colegio_procedencia ?: 'Sin registro' }}</p></div>
            <div class="detail-item md:col-span-2"><p class="detail-item-label">Direccion</p><p class="detail-item-value">{{ $postulante->direccion ?: 'Sin registro' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Primera opcion</p><p class="detail-item-value">{{ $postulante->carreraPrimeraOpcion?->nombre ?: 'Sin seleccion' }}</p></div>
            <div class="detail-item"><p class="detail-item-label">Segunda opcion</p><p class="detail-item-value">{{ $postulante->carreraSegundaOpcion?->nombre ?: 'Sin seleccion' }}</p></div>
            <div class="detail-item">
                <p class="detail-item-label">Estado inscripcion</p>
                <div class="detail-item-value">
                    <span class="badge {{ $postulante->estado_inscripcion === 'INSCRITO' ? 'badge-success' : ($postulante->estado_inscripcion === 'OBSERVADO' ? 'badge-error' : 'badge-warning') }}">
                        {{ $postulante->estado_inscripcion }}
                    </span>
                </div>
            </div>
            <div class="detail-item">
                <p class="detail-item-label">Estado admision</p>
                <div class="detail-item-value">
                    <span class="badge {{ $postulante->estado_admision === 'PENDIENTE' ? 'badge-warning' : ($postulante->estado_admision === 'ADMITIDO' ? 'badge-success' : 'badge-error') }}">
                        {{ $postulante->estado_admision }}
                    </span>
                </div>
            </div>
        </div>
    </x-card>
@endsection
