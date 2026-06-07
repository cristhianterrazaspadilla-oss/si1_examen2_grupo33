@extends('layouts.app')

@section('title', 'CU7 Gestionar Pagos | Editar')

@section('content')
    <x-page-title title="Editar Pago" subtitle="En esta fase solo se permite actualizar la observacion o anular logicamente el pago." />

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">No se pudo actualizar el pago.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.15fr_0.85fr]">
        <x-card title="Observacion del pago">
            <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.update', $pago) }}" class="app-form">
                @csrf
                @method('PUT')

                <section class="app-form-section">
                    <h2 class="app-section-title">Datos de referencia</h2>
                    <div class="app-form-grid cols-2">
                        <label class="form-control">
                            <span class="label-text">Postulante</span>
                            <input type="text" value="{{ $pago->postulante?->apellidos }}, {{ $pago->postulante?->nombres }}" class="input input-bordered" readonly>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Estado del pago</span>
                            <input type="text" value="{{ $pago->estado_pago }}" class="input input-bordered" readonly>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Monto</span>
                            <input type="text" value="{{ number_format((float) $pago->monto, 2) }} {{ $pago->moneda }}" class="input input-bordered" readonly>
                        </label>
                        <label class="form-control">
                            <span class="label-text">Stripe session ID</span>
                            <input type="text" value="{{ $pago->stripe_payment_id }}" class="input input-bordered" readonly>
                        </label>
                        <label class="form-control md:col-span-2">
                            <span class="label-text">Observacion</span>
                            <textarea name="observacion" class="textarea textarea-bordered" rows="5" placeholder="Actualiza la observacion interna del pago.">{{ old('observacion', $pago->observacion) }}</textarea>
                        </label>
                    </div>
                </section>

                <div class="app-form-actions">
                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    <a href="{{ route('gestion-postulantes-admision.pagos.show', $pago) }}" class="btn btn-outline">Volver</a>
                </div>
            </form>
        </x-card>

        <x-card title="Acciones disponibles">
            <div class="space-y-4 text-sm leading-7 text-base-content/75">
                <div class="alert alert-info">
                    <span>No se permite confirmar manualmente el pago en esta fase.</span>
                </div>
                <p>Si el pago debe invalidarse, se puede anular logicamente. El postulante volvera a estado REQUISITOS_APROBADOS siempre que no este inscrito.</p>

                <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.destroy', $pago) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-warning w-full" onclick="return confirm('Deseas anular este pago?')">Anular pago</button>
                </form>

                @if ($pago->stripe_payment_link)
                    <a href="{{ $pago->stripe_payment_link }}" target="_blank" rel="noopener noreferrer" class="btn btn-outline w-full">Abrir enlace de pago</a>
                @endif
            </div>
        </x-card>
    </div>
@endsection
