@extends('layouts.app')

@section('title', 'CU6 Gestionar Requisitos de Admisión | Validar Requisitos')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="Validar Requisitos del Postulante" subtitle="CU6 Gestionar Requisitos de Admisión" />
        <a href="{{ route('gestion-postulantes-admision.requisitos-postulantes.index') }}" class="btn btn-outline">Volver</a>
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

    <div class="alert alert-info mb-6">
        <span>Si todos los requisitos obligatorios activos están aprobados, el postulante quedará habilitado para pago con estado REQUISITOS_APROBADOS.</span>
    </div>

    <x-card title="Datos básicos del postulante">
        <div class="grid gap-4 md:grid-cols-2">
            <div><p class="text-sm text-base-content/70">CI</p><p class="font-medium">{{ $postulante->ci }}</p></div>
            <div><p class="text-sm text-base-content/70">Nombres y apellidos</p><p class="font-medium">{{ $postulante->nombres }} {{ $postulante->apellidos }}</p></div>
            <div><p class="text-sm text-base-content/70">Correo</p><p class="font-medium">{{ $postulante->correo ?: 'Sin correo' }}</p></div>
            <div><p class="text-sm text-base-content/70">Estado inscripción</p><span class="badge {{ $postulante->estado_inscripcion === 'REQUISITOS_APROBADOS' ? 'badge-success' : ($postulante->estado_inscripcion === 'OBSERVADO' ? 'badge-error' : 'badge-warning') }}">{{ $postulante->estado_inscripcion }}</span></div>
            <div><p class="text-sm text-base-content/70">Primera opción</p><p class="font-medium">{{ $postulante->carreraPrimeraOpcion?->nombre ?: 'Sin selección' }}</p></div>
            <div><p class="text-sm text-base-content/70">Segunda opción</p><p class="font-medium">{{ $postulante->carreraSegundaOpcion?->nombre ?: 'Sin selección' }}</p></div>
        </div>
    </x-card>

    <x-card title="Validación de requisitos">
        <form method="POST" action="{{ route('gestion-postulantes-admision.requisitos-postulantes.update', $postulante) }}" class="space-y-4">
            @csrf
            @method('PUT')
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Requisito</th>
                            <th>Obligatorio</th>
                            <th>Estado actual</th>
                            <th>Observación</th>
                            <th>Validado por</th>
                            <th>Fecha validación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitos as $index => $item)
                            <tr>
                                <td>
                                    <p class="font-medium">{{ $item->requisito->nombre }}</p>
                                    <p class="text-sm text-base-content/70">{{ $item->requisito->descripcion }}</p>
                                    <input type="hidden" name="requisitos[{{ $index }}][id]" value="{{ $item->id }}">
                                </td>
                                <td>
                                    <span class="badge {{ $item->requisito->obligatorio ? 'badge-warning' : 'badge-info' }}">
                                        {{ $item->requisito->obligatorio ? 'Sí' : 'No' }}
                                    </span>
                                </td>
                                <td>
                                    <select name="requisitos[{{ $index }}][estado]" class="select select-bordered min-w-40">
                                        @foreach (['PENDIENTE', 'APROBADO', 'OBSERVADO'] as $estado)
                                            <option value="{{ $estado }}" @selected(old("requisitos.$index.estado", $item->estado) === $estado)>{{ $estado }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <textarea name="requisitos[{{ $index }}][observacion]" class="textarea textarea-bordered min-w-52">{{ old("requisitos.$index.observacion", $item->observacion) }}</textarea>
                                </td>
                                <td>{{ $item->validadoPor?->nombre ? $item->validadoPor->nombre.' '.$item->validadoPor->apellido : 'Sin validación' }}</td>
                                <td>{{ $item->fecha_validacion ? $item->fecha_validacion->format('d/m/Y H:i') : 'Sin validación' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="btn btn-primary">Guardar validación</button>
                <a href="{{ route('gestion-postulantes-admision.requisitos-postulantes.index') }}" class="btn btn-outline">Volver</a>
            </div>
        </form>
    </x-card>
@endsection
