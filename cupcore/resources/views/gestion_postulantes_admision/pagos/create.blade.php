@extends('layouts.app')

@section('title', 'CU7 Gestionar Pagos | Nuevo pago')

@section('content')
    <x-page-title title="Nuevo Pago" subtitle="Genera un enlace de Stripe Checkout para postulantes con estado REQUISITOS_APROBADOS." />

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">No se pudo generar el pago.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.25fr_0.95fr]">
        <x-card title="Generar enlace de pago">
            <form method="POST" action="{{ route('gestion-postulantes-admision.pagos.store') }}" class="app-form">
                @csrf

                <section class="app-form-section">
                    <h2 class="app-section-title">Datos del pago</h2>
                    <div class="app-form-grid cols-2">
                        <label class="form-control md:col-span-2">
                            <span class="label-text">Postulante</span>
                            <select name="postulante_id" class="select select-bordered" required>
                                <option value="">Seleccione un postulante habilitado</option>
                                @foreach ($postulantes as $postulante)
                                    <option value="{{ $postulante->id }}" @selected((string) old('postulante_id', request('postulante_id')) === (string) $postulante->id)>
                                        {{ $postulante->apellidos }}, {{ $postulante->nombres }} - CI {{ $postulante->ci }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="form-control">
                            <span class="label-text">Monto</span>
                            <input type="number" name="monto" value="{{ old('monto') }}" class="input input-bordered" min="1" step="0.01" required>
                        </label>

                        <label class="form-control">
                            <span class="label-text">Moneda</span>
                            <select name="moneda" class="select select-bordered" required>
                                <option value="BOB" @selected(old('moneda', 'BOB') === 'BOB')>BOB</option>
                                <option value="USD" @selected(old('moneda') === 'USD')>USD</option>
                            </select>
                        </label>

                        <label class="form-control md:col-span-2">
                            <span class="label-text">Observacion</span>
                            <textarea name="observacion" class="textarea textarea-bordered" rows="4" placeholder="Detalle interno del pago o indicaciones adicionales.">{{ old('observacion') }}</textarea>
                        </label>
                    </div>
                </section>

                <div class="app-form-actions">
                    <button type="submit" class="btn btn-primary">Generar pago</button>
                    <a href="{{ route('gestion-postulantes-admision.pagos.index') }}" class="btn btn-outline">Volver</a>
                </div>
            </form>
        </x-card>

        <x-card title="Reglas de esta fase">
            <div class="space-y-4 text-sm leading-7 text-base-content/75">
                <div class="alert alert-info">
                    <span>Solo se listan postulantes con requisitos aprobados y sin otro pago pendiente.</span>
                </div>
                <ul class="list-disc space-y-2 pl-5">
                    <li>El enlace se crea en modo prueba con Stripe Checkout.</li>
                    <li>El pago se guarda en estado PENDIENTE.</li>
                    <li>El postulante cambia a estado PAGO_PENDIENTE.</li>
                    <li>En esta fase todavia no se confirma ni se inscribe automaticamente.</li>
                    <li>Si Stripe falla, no se registra ningun pago incompleto.</li>
                </ul>

                <div class="rounded-3xl border border-blue-300/12 bg-white/5 p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-blue-200/75">Referencia Stripe</p>
                    <p class="mt-3 text-base text-white">Producto configurado</p>
                    <p class="mt-1 text-sm text-base-content/70">Inscripcion Curso Preuniversitario FICCT</p>
                </div>
            </div>
        </x-card>
    </div>
@endsection
