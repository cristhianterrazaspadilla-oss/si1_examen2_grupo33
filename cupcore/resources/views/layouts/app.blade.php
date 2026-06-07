<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'CUPCore')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $isLoginScreen = request()->routeIs('login') || request()->routeIs('login.process');
    $currentTitle = trim($__env->yieldContent('title', 'CUPCore'));
    $authenticatedUser = auth()->user();
    $displayName = trim((string) (($authenticatedUser?->nombre ?? '') . ' ' . ($authenticatedUser?->apellido ?? '')));
    $displayRole = $authenticatedUser?->rol?->nombre ?? 'Sin rol';
    $normalizedRole = Str::of((string) $displayRole)->lower()->ascii()->toString();

    $sidebarGroups = [
        [
            'title' => 'Principal',
            'roles' => ['administrador', 'coordinador', 'docente', 'postulante', 'autoridad academica'],
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard'],
            ],
        ],
        [
            'title' => 'Autenticacion y Seguridad',
            'roles' => ['administrador'],
            'items' => [
                ['label' => 'Usuarios', 'route' => 'autenticacion-usuarios-seguridad.usuarios.index'],
                ['label' => 'Roles', 'route' => 'autenticacion-usuarios-seguridad.roles.index'],
                ['label' => 'Importaciones', 'route' => 'autenticacion-usuarios-seguridad.importaciones.index'],
            ],
        ],
        [
            'title' => 'Postulantes y Admision',
            'roles' => ['administrador', 'coordinador', 'postulante'],
            'items' => [
                ['label' => 'Postulantes', 'route' => 'gestion-postulantes-admision.postulantes.index'],
                ['label' => 'Requisitos', 'route' => 'gestion-postulantes-admision.requisitos.index'],
                ['label' => 'Validar requisitos', 'route' => 'gestion-postulantes-admision.requisitos-postulantes.index'],
                ['label' => 'Carreras', 'route' => 'gestion-postulantes-admision.carreras.index'],
                ['label' => 'Cupos', 'route' => 'gestion-postulantes-admision.cupos.index'],
            ],
        ],
    ];

    $visibleSidebarGroups = collect($sidebarGroups)
        ->filter(function (array $group) use ($normalizedRole) {
            return in_array($normalizedRole, $group['roles'], true);
        })
        ->map(function (array $group) {
            $group['items'] = collect($group['items'])
                ->filter(fn (array $item) => Route::has($item['route']))
                ->values()
                ->all();

            return $group;
        })
        ->filter(fn (array $group) => count($group['items']) > 0)
        ->values();
@endphp
<body class="{{ $isLoginScreen ? 'min-h-screen bg-slate-950 text-slate-100' : 'app-shell min-h-screen bg-slate-950 text-slate-100' }}">
    @if ($isLoginScreen)
        <main class="min-h-screen">
            @yield('content')
        </main>
    @else
        <div class="pointer-events-none fixed inset-0 overflow-hidden">
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_left,_rgba(37,99,235,0.22),_transparent_28%),radial-gradient(circle_at_bottom_right,_rgba(14,165,233,0.14),_transparent_26%),linear-gradient(135deg,_#020617_0%,_#081225_50%,_#0b1730_100%)]"></div>
            <div class="absolute -left-24 top-[-8rem] h-80 w-80 rounded-full bg-blue-500/10 blur-3xl"></div>
            <div class="absolute right-[-10rem] top-24 h-96 w-96 rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute bottom-[-12rem] left-1/3 h-[26rem] w-[26rem] rounded-full border border-blue-300/8 bg-blue-500/10 blur-3xl"></div>
        </div>

        <div class="relative flex min-h-screen">
            <aside id="app-sidebar" class="fixed inset-y-0 left-0 z-40 flex w-80 max-w-[86vw] -translate-x-full flex-col border-r border-blue-300/12 bg-slate-950/85 p-5 shadow-[0_25px_80px_rgba(2,6,23,0.85)] backdrop-blur-2xl transition-transform duration-300 lg:translate-x-0">
                <div class="mb-6 flex items-center gap-4 rounded-[1.75rem] border border-blue-300/12 bg-white/6 px-4 py-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl border border-blue-300/15 bg-blue-500/10 text-blue-300 shadow-[0_16px_35px_rgba(30,64,175,0.28)]">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 9.5 12 4l9 5.5-9 5.5L3 9.5Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 11.6V16l6 4 6-4v-4.4" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-200/75">CUPCore</p>
                        <p class="mt-1 text-lg font-semibold text-white">Panel institucional</p>
                    </div>
                </div>

                <div class="mb-5 rounded-[1.5rem] border border-blue-300/12 bg-white/6 p-4">
                    <p class="text-xs font-medium uppercase tracking-[0.22em] text-slate-500">Sesion activa</p>
                    <p class="mt-3 text-base font-semibold text-white">{{ $displayName !== '' ? $displayName : 'Usuario autenticado' }}</p>
                    <div class="mt-3 inline-flex items-center rounded-full border border-blue-300/15 bg-blue-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-blue-200">
                        {{ $displayRole }}
                    </div>
                </div>

                <nav class="flex-1 space-y-6 overflow-y-auto pr-1 pb-6">
                    @foreach ($visibleSidebarGroups as $group)
                        <div>
                            <p class="mb-3 px-2 text-[11px] font-semibold uppercase tracking-[0.24em] text-slate-500">{{ $group['title'] }}</p>
                            <div class="space-y-2">
                                @foreach ($group['items'] as $item)
                                    <a
                                        href="{{ route($item['route']) }}"
                                        class="group flex items-center justify-between rounded-2xl border px-4 py-3 text-sm transition {{ request()->routeIs($item['route']) ? 'border-blue-300/25 bg-blue-500/15 text-white shadow-[0_14px_35px_rgba(37,99,235,0.2)]' : 'border-white/6 bg-white/4 text-slate-300 hover:border-blue-300/18 hover:bg-white/8 hover:text-white' }}"
                                    >
                                        <span>{{ $item['label'] }}</span>
                                        <span class="text-[11px] uppercase tracking-[0.22em] text-slate-500 group-hover:text-blue-200">Ir</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </nav>

                <form method="POST" action="{{ route('logout') }}" class="sticky bottom-0 mt-4 border-t border-blue-300/10 bg-slate-950/92 pt-4 backdrop-blur-xl">
                    @csrf
                    <button type="submit" class="btn w-full rounded-2xl border border-rose-400/18 bg-[linear-gradient(135deg,_rgba(30,41,59,0.92)_0%,_rgba(30,64,175,0.72)_55%,_rgba(14,165,233,0.72)_100%)] text-white shadow-[0_18px_42px_rgba(15,23,42,0.42)] hover:border-rose-300/28 hover:brightness-110">
                        Cerrar sesion
                    </button>
                </form>
            </aside>

            <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-slate-950/70 backdrop-blur-sm lg:hidden"></div>

            <div class="flex min-h-screen flex-1 flex-col lg:pl-80">
                <header class="sticky top-0 z-20 border-b border-blue-300/10 bg-slate-950/65 backdrop-blur-2xl">
                    <div class="mx-auto flex w-full max-w-[1600px] items-center justify-between gap-4 px-4 py-4 sm:px-6 xl:px-8">
                        <div class="flex items-center gap-3">
                            <button id="sidebar-toggle" type="button" class="btn btn-ghost btn-circle border border-white/8 bg-white/6 text-slate-200 lg:hidden">
                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                                </svg>
                            </button>
                            <div>
                                <p class="text-xs font-medium uppercase tracking-[0.24em] text-blue-200/75">Campus digital</p>
                                <h1 class="mt-1 text-xl font-semibold tracking-tight text-white sm:text-2xl">{{ $currentTitle }}</h1>
                            </div>
                        </div>

                        <div class="hidden items-center gap-3 sm:flex">
                            <a href="{{ route('dashboard') }}" class="rounded-2xl border border-white/8 bg-white/5 px-4 py-2 text-sm font-medium text-slate-200 transition hover:border-blue-300/18 hover:bg-white/8 hover:text-white">
                                Dashboard
                            </a>
                            <div class="rounded-2xl border border-white/8 bg-white/5 px-4 py-2 text-right">
                                <p class="text-xs uppercase tracking-[0.22em] text-slate-500">Rol</p>
                                <p class="mt-1 text-sm font-semibold text-blue-200">{{ $displayRole }}</p>
                            </div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn rounded-2xl border border-rose-400/18 bg-white/5 px-4 text-sm font-medium text-slate-100 shadow-none hover:border-rose-300/28 hover:bg-rose-500/10">
                                    Cerrar sesion
                                </button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="relative flex-1 px-4 py-6 sm:px-6 xl:px-8">
                    <div class="mx-auto max-w-[1600px]">
                        @yield('content')
                    </div>
                </main>
            </div>
        </div>
    @endif

    @stack('scripts')

    @unless ($isLoginScreen)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const sidebar = document.getElementById('app-sidebar');
                const overlay = document.getElementById('sidebar-overlay');
                const toggleButton = document.getElementById('sidebar-toggle');

                if (!sidebar || !overlay || !toggleButton) {
                    return;
                }

                const openSidebar = () => {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                };

                const closeSidebar = () => {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                };

                toggleButton.addEventListener('click', openSidebar);
                overlay.addEventListener('click', closeSidebar);

                window.addEventListener('resize', () => {
                    if (window.innerWidth >= 1024) {
                        overlay.classList.add('hidden');
                        document.body.classList.remove('overflow-hidden');
                    }
                });
            });
        </script>
    @endunless
</body>
</html>
