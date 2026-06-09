@extends('layouts.app')

@section('title', 'CU7 Gestionar Pagos | CUPCore')

@section('content')
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
        <x-page-title title="Gestionar Pagos" subtitle="CU7 Fase 1 y 2. Genera enlaces de Stripe Checkout y permite verificar el estado real del pago en Stripe." />
        <a href="{{ route('gestion-postulantes-admision.pagos.create') }}" class="btn btn-primary w-full sm:w-auto">Nuevo pago</a>
    </div>

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info mb-6">
            <span>{{ session('info') }}</span>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning mb-6">
            <span>{{ session('warning') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron errores al procesar pagos.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="alert alert-info mb-6">
        <span>La verificacion consulta directamente el estado del pago en Stripe. Solo si Stripe devuelve payment_status paid se confirma el pago y se inscribe al postulante.</span>
    </div>

    <x-card title="Busqueda y filtros">
        <form method="GET" action="{{ route('gestion-postulantes-admision.pagos.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4">
            <label class="form-control sm:col-span-2 md:col-span-3">
                <span class="label-text">Buscar por CI, nombres, apellidos o correo del postulante</span>
                <input type="text" name="search" value="{{ $search }}" class="input input-bordered">
            </label>

            <label class="form-control">
                <span class="label-text">Estado del pago</span>
                <select name="estado_pago" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach (['PENDIENTE', 'CONFIRMADO', 'RECHAZADO', 'ANULADO'] as $estado)
                        <option value="{{ $estado }}" @selected($estadoPago === $estado)>{{ $estado }}</option>
                    @endforeach
                </select>
            </label>

            <div class="sm:col-span-3 md:col-span-4 flex gap-2">
                <button type="submit" class="btn btn-primary">Buscar</button>
                <a href="{{ route('gestion-postulantes-admision.pagos.index') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    <x-card title="Listado de pagos">
        <div class="overflow-x-auto">
            <table class="table min-w-[1100px]">
                <thead>
                    <tr>
                        <th>Postulante</th>
                        <th>CI</th>
                        <th>Monto</th>
                        <th>Moneda</th>
                        <th>Estado</th>
                        <th>Fecha de pago</th>
                        <th>Stripe</th>
                        <th class="text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pagos as $pago)
                        <tr>
                            <td>
                                <div class="font-medium text-white">{{ $pago->postulante?->nombres }} {{ $pago->postulante?->apellidos }}</div>
                                <div class="text-xs text-base-content/70">{{ $pago->postulante?->correo ?: 'Sin correo' }}</div>
                            </td>
                            <td>{{ $pago->postulante?->ci ?: 'Sin CI' }}</td>
                            <td>{{ number_format((float) $pago->monto, 2) }}</td>
                            <td>{{ $pago->moneda }}</td>
                            <td>
                                <span class="badge {{ $pago->estado_pago === 'PENDIENTE' ? 'badge-warning' : ($pago->estado_pago === 'CONFIRMADO' ? 'badge-success' : 'badge-error') }}">
                                    {{ $pago->estado_pago }}
                                </span>
                            </td>
                            <td>{{ $pago->fecha_pago?->format('d/m/Y H:i') ?: 'Pendiente' }}</td>
                            <td>
                                @if ($pago->stripe_payment_link)
                                    <a href="{{ $pago->stripe_payment_link }}" target="_blank" rel="noopener noreferrer" class="link link-primary">Abrir</a>
                                @else
                                    <span class="text-sm text-base-content/60">Sin enlace</span>
                                @endif
                            </td>
                            <td>
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('gestion-postulantes-admision.pagos.show', $pago) }}" class="btn btn-sm btn-outline">Ver</a>
                                    @if ($pago->estado_pago === 'PENDIENTE' && $pago->stripe_payment_id)
                                        <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.verificar', $pago) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">Verificar pago</button>
                                        </form>
                                    @endif
                                    <a href="{{ route('gestion-postulantes-admision.pagos.edit', $pago) }}" class="btn btn-sm btn-info">Editar</a>
                                    <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.destroy', $pago) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-warning" onclick="return confirm('Deseas anular este pago pendiente?')">Anular</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8">
                                <div class="alert">
                                    <span>No existen pagos registrados con esos criterios.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $pagos->links() }}
        </div>
    </x-card>
@endsection
