@extends('layouts.app')

@section('title', 'CU7 Gestionar Pagos | Detalle')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title
            title="Detalle del Pago"
            :subtitle="$isPostulante
                ? 'Consulta el estado y la información de tu pago.'
                : 'Consulta el enlace generado, el estado actual y verifica directamente el pago contra Stripe.'"
        />
        <div class="flex gap-2">
            @if ($canManagePayments)
                <a href="{{ route('gestion-postulantes-admision.pagos.edit', $pago) }}" class="btn btn-info">Editar</a>
            @endif
            <a href="{{ route('gestion-postulantes-admision.pagos.index') }}" class="btn btn-outline">Volver</a>
        </div>
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
                <p class="font-semibold">No se pudo completar la verificacion.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="alert alert-info mb-6">
        <span>
            {{ $isPostulante
                ? 'El estado se actualizará cuando la administración verifique el pago.'
                : 'La verificacion consulta directamente el estado del pago en Stripe. Solo si Stripe devuelve payment_status paid se confirmara el pago.' }}
        </span>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <x-card title="Datos del postulante">
            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Nombre completo</p>
                    <p class="mt-2 text-base text-white">{{ $pago->postulante?->nombres }} {{ $pago->postulante?->apellidos }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">CI</p>
                    <p class="mt-2 text-base text-white">{{ $pago->postulante?->ci ?: 'Sin registro' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Correo</p>
                    <p class="mt-2 text-base text-white">{{ $pago->postulante?->correo ?: 'Sin correo' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estado de inscripcion</p>
                    <p class="mt-2">
                        <span class="badge {{ $pago->postulante?->estado_inscripcion === 'INSCRITO' ? 'badge-success' : ($pago->postulante?->estado_inscripcion === 'OBSERVADO' ? 'badge-error' : 'badge-warning') }}">
                            {{ $pago->postulante?->estado_inscripcion ?: 'Sin estado' }}
                        </span>
                    </p>
                </div>
            </div>
        </x-card>

        <x-card title="Datos del pago">
            <div class="space-y-5">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Monto</p>
                    <p class="mt-2 text-2xl font-semibold text-white">{{ number_format((float) $pago->monto, 2) }} {{ $pago->moneda }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Estado del pago</p>
                    <p class="mt-2">
                        <span class="badge {{ $pago->estado_pago === 'PENDIENTE' ? 'badge-warning' : ($pago->estado_pago === 'CONFIRMADO' ? 'badge-success' : 'badge-error') }}">
                            {{ $pago->estado_pago }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Fecha de pago</p>
                    <p class="mt-2 text-base text-white">{{ $pago->fecha_pago?->format('d/m/Y H:i') ?: 'Pendiente de confirmacion' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Observacion</p>
                    <p class="mt-2 text-base text-white">{{ $pago->observacion ?: 'Sin observacion registrada' }}</p>
                </div>
                @unless ($isPostulante)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Stripe session ID</p>
                        <p class="mt-2 break-all text-sm text-base-content/75">{{ $pago->stripe_payment_id ?: 'Sin referencia' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Stripe payment link</p>
                        <p class="mt-2 break-all text-sm text-base-content/75">{{ $pago->stripe_payment_link ?: 'Sin enlace' }}</p>
                    </div>
                @endunless
                @if ($canManagePayments && $pago->estado_pago === 'PENDIENTE' && $pago->stripe_payment_id)
                    <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.verificar', $pago) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-full">Verificar pago</button>
                    </form>
                @endif
                @if ($pago->stripe_payment_link)
                    <a href="{{ $pago->stripe_payment_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline w-full">Abrir enlace de pago</a>
                @endif
            </div>
        </x-card>
    </div>
@endsection
