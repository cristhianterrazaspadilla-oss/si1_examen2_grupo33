@extends('layouts.app')

@section('title', 'Recuperar contraseña | CUPCore')

@section('content')
    <div class="relative flex min-h-screen items-center justify-center overflow-hidden px-4 py-10">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.28),_transparent_34%),linear-gradient(135deg,_#020617_0%,_#0b1730_100%)]"></div>

        <div class="relative w-full max-w-lg rounded-[2rem] border border-blue-300/15 bg-slate-900/85 p-7 shadow-2xl backdrop-blur-xl sm:p-10">
            <p class="text-xs font-semibold uppercase tracking-[0.28em] text-blue-300">CUPCore</p>
            <h1 class="mt-3 text-3xl font-semibold text-white">Recuperar contraseña</h1>
            <p class="mt-3 text-sm leading-6 text-slate-300">
                Ingresa el correo asociado a tu cuenta. Esta pantalla representa el flujo CU18 para la demostración del sistema.
            </p>

            @if (session('success'))
                <div class="mt-6"><x-alert type="success" :message="session('success')" /></div>
            @endif

            @if ($errors->any())
                <div class="mt-6"><x-alert type="error" :message="$errors->first()" /></div>
            @endif

            <form method="POST" action="{{ route('password.demo.store') }}" class="mt-7 space-y-5">
                @csrf
                <label class="form-control">
                    <span class="label-text text-slate-200">Correo electrónico</span>
                    <input type="email" name="correo" value="{{ old('correo') }}" class="input input-bordered w-full" placeholder="usuario@correo.com" required autofocus>
                </label>

                <div class="rounded-2xl border border-amber-300/20 bg-amber-300/8 p-4 text-sm text-amber-100">
                    Modo demostración: se mostrará la confirmación, pero no se enviará un correo ni se modificará la contraseña.
                </div>

                <button type="submit" class="btn btn-primary w-full">Solicitar recuperación</button>
                <a href="{{ route('login') }}" class="btn btn-outline w-full">Volver al inicio de sesión</a>
            </form>
        </div>
    </div>
@endsection
