@extends('layouts.app')

@section('title', 'Iniciar sesion | CUPCore')

@section('content')
<section class="login-screen relative isolate overflow-hidden bg-slate-950">
    <div class="absolute inset-0">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.28),_transparent_32%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.18),_transparent_28%),linear-gradient(135deg,_#020617_0%,_#081225_45%,_#0b1730_100%)]"></div>
        <div class="absolute -left-24 bottom-[-14rem] h-[28rem] w-[28rem] rounded-full border border-blue-400/15 bg-blue-500/10 blur-3xl"></div>
        <div class="absolute right-[-8rem] top-[-8rem] h-[22rem] w-[22rem] rounded-full border border-cyan-300/10 bg-cyan-400/10 blur-3xl"></div>
    </div>

    <div class="relative mx-auto flex min-h-screen max-w-7xl items-center px-4 py-8 sm:px-6 lg:px-8">
        <div class="grid w-full overflow-hidden rounded-[2rem] border border-blue-400/20 bg-slate-950/70 shadow-[0_30px_120px_rgba(2,6,23,0.9)] backdrop-blur-xl lg:grid-cols-[1.05fr_0.95fr]">
            <div class="relative hidden min-h-[720px] overflow-hidden border-b border-blue-400/10 p-8 lg:block lg:border-b-0 lg:border-r lg:p-14">
                <div class="absolute inset-x-0 bottom-0 h-72 bg-[radial-gradient(circle_at_center,_rgba(37,99,235,0.88),_rgba(29,78,216,0.45)_42%,_transparent_72%)]"></div>
                <div class="absolute inset-x-0 bottom-[-10rem] h-80 rounded-t-[100%] border border-blue-300/15 bg-[linear-gradient(180deg,_rgba(29,78,216,0.28)_0%,_rgba(15,23,42,0)_100%)]"></div>

                <div class="relative z-10 flex h-full flex-col justify-between">
                    <div class="space-y-8">
                        <div class="inline-flex h-20 w-20 items-center justify-center rounded-3xl border border-blue-300/20 bg-white/8 shadow-[0_18px_40px_rgba(30,64,175,0.35)] backdrop-blur-md">
                            <svg class="h-10 w-10 text-blue-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5 12 4l9 5.5-9 5.5L3 9.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 11.6V16l6 4 6-4v-4.4" />
                            </svg>
                        </div>

                        <div class="space-y-5">
                            <p class="text-lg font-medium tracking-[0.24em] text-blue-200/80 uppercase">Bienvenido a</p>
                            <div class="space-y-3">
                                <h1 class="text-6xl font-semibold tracking-tight text-white xl:text-7xl">
                                    CUP<span class="text-blue-400">Core</span>
                                </h1>
                                <p class="max-w-xl text-lg leading-8 text-slate-300/90">
                                    Sistema de gestion del Curso Preuniversitario FICCT.
                                </p>
                            </div>
                            <p class="max-w-2xl text-base leading-8 text-slate-400">
                                Administra postulantes, requisitos, pagos, evaluaciones y resultados desde una plataforma centralizada.
                            </p>
                        </div>
                    </div>

                    <div class="relative z-10 max-w-xl rounded-[1.75rem] border border-blue-300/10 bg-white/5 p-6 backdrop-blur-md">
                        <p class="text-sm font-semibold uppercase tracking-[0.22em] text-blue-200/80">Plataforma academica</p>
                        <div class="mt-5 grid gap-4 sm:grid-cols-2">
                            <div class="rounded-2xl border border-white/8 bg-slate-950/40 p-4">
                                <p class="text-sm text-slate-400">Modulos integrados</p>
                                <p class="mt-2 text-2xl font-semibold text-white">5</p>
                            </div>
                            <div class="rounded-2xl border border-white/8 bg-slate-950/40 p-4">
                                <p class="text-sm text-slate-400">Acceso seguro</p>
                                <p class="mt-2 text-2xl font-semibold text-white">24/7</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex min-h-screen items-center justify-center p-4 sm:p-8 lg:min-h-[720px] lg:p-12">
                <div class="w-full max-w-xl rounded-[2rem] border border-blue-300/15 bg-white/6 p-6 shadow-[0_20px_80px_rgba(15,23,42,0.55)] backdrop-blur-2xl sm:p-8">
                    <div class="mb-8 flex items-start justify-between gap-4">
                        <div>
                            <p class="text-sm font-medium uppercase tracking-[0.22em] text-blue-200/70">Acceso institucional</p>
                            <h2 class="mt-3 text-3xl font-semibold tracking-tight text-white sm:text-4xl">Iniciar sesion</h2>
                            <p class="mt-3 text-sm leading-7 text-slate-300">
                                Ingresa tus credenciales para continuar.
                            </p>
                        </div>
                        <div class="rounded-2xl border border-blue-300/20 bg-slate-900/60 px-4 py-3 text-right">
                            <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Proyecto</p>
                            <p class="mt-1 text-sm font-semibold text-blue-300">CUPCore</p>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert mb-5 border border-emerald-400/20 bg-emerald-500/10 text-emerald-100 shadow-none">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert mb-5 border border-rose-400/20 bg-rose-500/10 text-rose-100 shadow-none">
                            <svg class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v4.5m0 3h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <div class="text-sm">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login.process') }}" class="space-y-5">
                        @csrf

                        <div class="form-control">
                            <label class="label px-1">
                                <span class="label-text text-sm font-medium text-slate-200">Correo electronico</span>
                            </label>
                            <label class="input flex h-16 items-center gap-3 rounded-2xl border border-blue-300/15 bg-slate-950/40 px-4 shadow-none">
                                <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 7.5v9A2.25 2.25 0 0 1 19.5 18.75h-15A2.25 2.25 0 0 1 2.25 16.5v-9A2.25 2.25 0 0 1 4.5 5.25h15A2.25 2.25 0 0 1 21.75 7.5Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m3 6 8.294 6.22a1.2 1.2 0 0 0 1.412 0L21 6" />
                                </svg>
                                <input
                                    type="email"
                                    name="correo"
                                    value="{{ old('correo') }}"
                                    placeholder="admin@ficct.edu.bo"
                                    class="h-full w-full border-0 bg-transparent px-0 text-base text-white placeholder:text-slate-500 focus:outline-none focus:ring-0"
                                    required
                                    autofocus
                                >
                            </label>
                        </div>

                        <div class="form-control">
                            <label class="label px-1">
                                <span class="label-text text-sm font-medium text-slate-200">Contrasena</span>
                            </label>
                            <div class="relative">
                                <label class="input flex h-16 items-center gap-3 rounded-2xl border border-blue-300/15 bg-slate-950/40 px-4 pr-14 shadow-none">
                                    <svg class="h-5 w-5 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V7.875a4.5 4.5 0 1 0-9 0V10.5m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                                    </svg>
                                    <input
                                        id="password-input"
                                        type="password"
                                        name="password"
                                        placeholder="Ingresa tu contrasena"
                                        class="h-full w-full border-0 bg-transparent px-0 text-base text-white placeholder:text-slate-500 focus:outline-none focus:ring-0"
                                        required
                                    >
                                </label>
                                <button
                                    type="button"
                                    id="toggle-password"
                                    class="absolute inset-y-0 right-4 flex items-center text-slate-400 transition hover:text-blue-300"
                                    aria-label="Mostrar u ocultar contrasena"
                                    aria-pressed="false"
                                >
                                    <svg id="eye-open" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5.25 12 5.25c4.476 0 8.268 2.693 9.542 6.75-1.274 4.057-5.066 6.75-9.542 6.75-4.477 0-8.268-2.693-9.542-6.75Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg id="eye-closed" class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 3 18 18" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.584 10.587A2.25 2.25 0 0 0 13.5 13.5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.88 5.12A10.477 10.477 0 0 1 12 4.875c4.478 0 8.271 2.699 9.545 6.758a10.422 10.422 0 0 1-4.112 5.145M6.228 6.228A10.45 10.45 0 0 0 2.455 12.01c1.273 4.058 5.067 6.74 9.545 6.74a10.47 10.47 0 0 0 5.148-1.35" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="flex flex-col gap-4 pt-1 sm:flex-row sm:items-center sm:justify-between">
                            <label class="label cursor-pointer justify-start gap-3 p-0">
                                <input
                                    type="checkbox"
                                    name="remember"
                                    value="1"
                                    class="checkbox checkbox-primary border-blue-300/30 bg-slate-950/50 [--chkbg:theme(colors.blue.500)] [--chkfg:white]"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <span class="label-text text-sm text-slate-300">Recordar sesion</span>
                            </label>
                            <a href="{{ route('login') }}#soporte-acceso" class="text-sm font-medium text-blue-300 transition hover:text-blue-200">
                                Olvidaste tu contrasena?
                            </a>
                        </div>

                        <button
                            type="submit"
                            class="btn h-14 w-full rounded-2xl border-0 bg-[linear-gradient(135deg,_#2563eb_0%,_#1d4ed8_45%,_#0ea5e9_100%)] text-base font-semibold text-white shadow-[0_20px_45px_rgba(29,78,216,0.4)] transition hover:-translate-y-0.5 hover:brightness-110"
                        >
                            Ingresar
                        </button>
                    </form>

                    <div id="soporte-acceso" class="mt-8 rounded-2xl border border-white/8 bg-slate-950/35 px-5 py-4 text-sm text-slate-400">
                        <p class="font-medium text-slate-200">Acceso al sistema</p>
                        <p class="mt-2 leading-7">
                            Usa tu correo institucional y tu contrasena habitual. Si no puedes ingresar, solicita soporte al administrador del Curso Preuniversitario FICCT.
                        </p>
                        <p class="mt-3 text-xs text-slate-500">
                            Credenciales de prueba disponibles solo para verificacion interna.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const toggleButton = document.getElementById('toggle-password');
        const passwordInput = document.getElementById('password-input');
        const eyeOpen = document.getElementById('eye-open');
        const eyeClosed = document.getElementById('eye-closed');

        if (!toggleButton || !passwordInput || !eyeOpen || !eyeClosed) {
            return;
        }

        toggleButton.addEventListener('click', () => {
            const showingPassword = passwordInput.type === 'text';

            passwordInput.type = showingPassword ? 'password' : 'text';
            eyeOpen.classList.toggle('hidden', !showingPassword);
            eyeClosed.classList.toggle('hidden', showingPassword);
            toggleButton.setAttribute('aria-pressed', showingPassword ? 'false' : 'true');
        });
    });
</script>
@endpush
