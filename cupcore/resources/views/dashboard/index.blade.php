@extends('layouts.app')

@section('title', 'Dashboard | CUPCore')

@section('content')
    {{-- Dashboard: resumen central del sistema para usuarios autenticados.
        - Muestra indicadores adaptados al rol autenticado (administrador, coordinador, docente, postulante, autoridad).
        - NO reemplaza el menú de casos de uso: la navegación principal está en el sidebar institucional.
        - Diseñado para presentar KPIs y accesos rápidos, no para ejecutar procesos.
    --}}
    <section class="space-y-6">
        <div class="overflow-hidden rounded-[2rem] border border-blue-300/12 bg-white/6 p-6 shadow-[0_25px_80px_rgba(2,6,23,0.55)] backdrop-blur-2xl sm:p-8">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                <div class="max-w-3xl">
                    <p class="text-sm font-semibold uppercase tracking-[0.28em] text-blue-200/75">Dashboard</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                        Resumen general del sistema
                    </h2>
                    <p class="mt-3 text-sm leading-7 text-slate-300 sm:text-base">
                        Bienvenido, <span class="font-semibold text-white">{{ $userName !== '' ? $userName : 'Usuario' }}</span> ({{ $roleName }}). Consulta a continuación los indicadores generales del Curso Preuniversitario y utiliza el sidebar institucional para navegar por los módulos permitidos para tu rol.
                    </p>
                </div>
            </div>
        </div>

        <div>
            <h3 class="mb-4 text-xl font-semibold tracking-tight text-white">Indicadores de tu Rol</h3>

            @php
                $normalizedRole = \Illuminate\Support\Str::of((string) $roleName)->lower()->ascii()->toString();
            @endphp

            @if ($normalizedRole === 'administrador')
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Total de Usuarios</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_usuarios'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Registrados en el sistema</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-400">Usuarios Activos</p>
                        <p class="mt-4 text-4xl font-bold text-emerald-300">{{ $stats['usuarios_activos'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Acceso operativo habilitado</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Roles del Sistema</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_roles'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Perfiles de seguridad configurados</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">Log de Auditoría</p>
                        <p class="mt-4 text-4xl font-bold text-blue-300">{{ $stats['total_bitacora'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Registros en bitácora</p>
                    </div>
                </div>
            @elseif ($normalizedRole === 'coordinador')
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Postulantes</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_postulantes'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Inscritos y pre-registrados</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Docentes</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_docentes'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Plantel docente del CUP</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Materias Activas</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_materias'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Materias curriculares</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">Grupos Formados</p>
                        <p class="mt-4 text-4xl font-bold text-blue-300">{{ $stats['total_grupos'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Grupos académicos de estudio</p>
                    </div>
                </div>
            @elseif ($normalizedRole === 'docente')
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Profesión</p>
                        <p class="mt-4 truncate text-2xl font-bold text-white">{{ $stats['profesion'] ?? 'Docente' }}</p>
                        <p class="mt-2 text-xs text-slate-400">Título profesional registrado</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Especialidad</p>
                        <p class="mt-4 truncate text-2xl font-bold text-white">{{ $stats['especialidad'] ?? 'Área técnica' }}</p>
                        <p class="mt-2 text-xs text-slate-400">Especialidad docente</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">Grupos Asignados</p>
                        <p class="mt-4 text-4xl font-bold text-blue-300">{{ $stats['total_asignaciones'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Grupos con materias activas</p>
                    </div>
                </div>
            @elseif ($normalizedRole === 'postulante')
                <div class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Estado de Inscripción</p>
                            <p class="mt-4 text-2xl font-bold text-white">
                                <span class="badge {{ ($stats['estado_inscripcion'] ?? '') === 'INSCRITO' ? 'badge-success' : 'badge-warning' }}">
                                    {{ $stats['estado_inscripcion'] ?? 'PENDIENTE' }}
                                </span>
                            </p>
                            <p class="mt-2 text-xs text-slate-400">Situación actual de registro</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Estado de Admisión</p>
                            <p class="mt-4 text-2xl font-bold text-white">
                                <span class="badge {{ ($stats['estado_admision'] ?? '') === 'ADMITIDO' ? 'badge-success' : (($stats['estado_admision'] ?? '') === 'NO_ADMITIDO' ? 'badge-danger' : 'badge-warning') }}">
                                    {{ $stats['estado_admision'] ?? 'PENDIENTE' }}
                                </span>
                            </p>
                            <p class="mt-2 text-xs text-slate-400">Estado oficial del proceso</p>
                        </div>
                        <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl sm:col-span-2 xl:col-span-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">Opciones de Carrera</p>
                            <div class="mt-3 text-sm text-slate-200">
                                <p><span class="font-semibold">1ª Opción:</span> {{ $stats['carrera_1'] ?? 'Ninguna' }}</p>
                                <p class="mt-1"><span class="font-semibold">2ª Opción:</span> {{ $stats['carrera_2'] ?? 'Ninguna' }}</p>
                            </div>
                        </div>
                    </div>

                    @if (!empty($stats['resultado']))
                        <div class="overflow-hidden rounded-[2rem] border border-emerald-500/20 bg-emerald-500/5 p-6 shadow-[0_25px_80px_rgba(2,6,23,0.35)] backdrop-blur-2xl">
                            <h4 class="text-lg font-semibold tracking-tight text-emerald-300">Mi Resultado de Admisión</h4>
                            <div class="mt-4 grid gap-6 sm:grid-cols-2 2xl:grid-cols-4">
                                <div class="rounded-[1.25rem] border border-emerald-500/10 bg-slate-950/30 p-4">
                                    <p class="text-xs uppercase tracking-wider text-emerald-400">Promedio Final</p>
                                    <p class="mt-2 text-3xl font-bold text-white">{{ number_format($stats['resultado']->promedio_final, 2) }}</p>
                                </div>
                                <div class="rounded-[1.25rem] border border-emerald-500/10 bg-slate-950/30 p-4">
                                    <p class="text-xs uppercase tracking-wider text-emerald-400">Resultado Oficial</p>
                                    <p class="mt-2 text-xl font-bold text-white">{{ $stats['resultado']->estado_resultado }}</p>
                                </div>
                                <div class="rounded-[1.25rem] border border-emerald-500/10 bg-slate-950/30 p-4">
                                    <p class="text-xs uppercase tracking-wider text-emerald-400">Carrera Asignada</p>
                                    <p class="mt-2 text-sm font-semibold text-white">{{ $stats['resultado']->carreraAsignada?->nombre ?? 'Ninguna' }}</p>
                                </div>
                                <div class="rounded-[1.25rem] border border-emerald-500/10 bg-slate-950/30 p-4">
                                    <p class="text-xs uppercase tracking-wider text-emerald-400">Tipo de Asignación</p>
                                    <p class="mt-2 text-sm font-semibold text-white">{{ str_replace('_', ' ', $stats['resultado']->tipo_asignacion) }}</p>
                                </div>
                            </div>
                            @if (!empty($stats['resultado']->observacion))
                                <div class="mt-4 rounded-[1.25rem] border border-emerald-500/10 bg-slate-950/30 p-4 text-sm text-slate-300">
                                    <span class="font-semibold text-emerald-400">Observación:</span> {{ $stats['resultado']->observacion }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @elseif ($normalizedRole === 'autoridad academica')
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Postulantes Inscritos</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_inscritos'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Matriculados vigentes</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-emerald-400">Postulantes Admitidos</p>
                        <p class="mt-4 text-4xl font-bold text-emerald-300">{{ $stats['total_admitidos'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Estudiantes que aprobaron</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-slate-400">Docentes en Ejercicio</p>
                        <p class="mt-4 text-4xl font-bold text-white">{{ $stats['total_docentes'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Docentes operando grupos</p>
                    </div>
                    <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl sm:col-span-2 xl:col-span-1">
                        <p class="text-xs font-semibold uppercase tracking-[0.14em] text-blue-400">Promedio General del CUP</p>
                        <p class="mt-4 text-4xl font-bold text-blue-300">{{ $stats['promedio_general'] ?? 0 }}</p>
                        <p class="mt-2 text-xs text-slate-400">Calificación media general</p>
                    </div>
                </div>
            @else
                <div class="rounded-[1.5rem] border border-white/8 bg-slate-950/35 p-6 backdrop-blur-xl">
                    <p class="text-slate-300">No hay indicadores adicionales configurados para tu rol actualmente.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
