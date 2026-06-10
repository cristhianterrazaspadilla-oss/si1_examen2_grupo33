@extends('layouts.app')

@section('title', 'CU16 KPIs Academicos | CUPCore')

@section('content')
    {{-- CU16 Dashboard académico: KPIs resumidos y tablas de apoyo.
        - Muestra indicadores agregados (postulantes, pagos, cupos, docentes, materias) y tablas resumidas.
        - Permite filtrar el dashboard para centrar análisis en una gestión/carrera/grupo/materia.
        - Es complemento visual de la pantalla de Consulta; no reemplaza la exportación de reportes.
    --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="CU16 KPIs Academicos" subtitle="Indicadores academicos y administrativos del proceso de admision." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.reportes.consulta') }}" class="btn btn-outline w-full sm:w-auto">Reportes</a>
            <a href="{{ route('gestion-academica-cup.reportes.historial') }}" class="btn btn-primary w-full sm:w-auto">Historial de reportes</a>
        </div>
    </div>

    <x-card title="Filtros del dashboard">
        <form method="GET" action="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-6">
            <label class="form-control">
                <span class="label-text">Gestion</span>
                <select name="gestion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['gestiones'] as $gestionOption)
                        <option value="{{ $gestionOption }}" @selected($filters['gestion'] === $gestionOption)>{{ $gestionOption }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Carrera</span>
                <select name="carrera_id" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['carreras'] as $carrera)
                        <option value="{{ $carrera->id }}" @selected((string) $filters['carrera_id'] === (string) $carrera->id)>{{ $carrera->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Grupo</span>
                <select name="grupo_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['grupos'] as $grupo)
                        <option value="{{ $grupo->id }}" @selected((string) $filters['grupo_id'] === (string) $grupo->id)>{{ $grupo->nombre }} - {{ $grupo->gestion }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Materia</span>
                <select name="materia_id" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($formOptions['materias'] as $materia)
                        <option value="{{ $materia->id }}" @selected((string) $filters['materia_id'] === (string) $materia->id)>{{ $materia->nombre }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado resultado</span>
                <select name="estado_resultado" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['estadosResultado'] as $estadoResultado)
                        <option value="{{ $estadoResultado }}" @selected($filters['estado_resultado'] === $estadoResultado)>{{ $estadoResultado }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Estado inscripcion</span>
                <select name="estado_inscripcion" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($formOptions['estadosInscripcion'] as $estadoInscripcion)
                        <option value="{{ $estadoInscripcion }}" @selected($filters['estado_inscripcion'] === $estadoInscripcion)>{{ $estadoInscripcion }}</option>
                    @endforeach
                </select>
            </label>
            <div class="sm:col-span-2 2xl:col-span-6 flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Aplicar filtros</button>
                <a href="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="btn btn-outline w-full sm:w-auto">Limpiar filtros</a>
            </div>
        </form>
    </x-card>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
        <x-card title="Postulantes registrados"><p class="text-3xl font-semibold text-white">{{ $summary['total_postulantes'] }}</p></x-card>
        <x-card title="Postulantes inscritos"><p class="text-3xl font-semibold text-white">{{ $summary['total_inscritos'] }}</p></x-card>
        <x-card title="Pagos confirmados"><p class="text-3xl font-semibold text-white">{{ $summary['total_pagos_confirmados'] }}</p></x-card>
        <x-card title="Grupos activos"><p class="text-3xl font-semibold text-white">{{ $summary['total_grupos_activos'] }}</p></x-card>
        <x-card title="Docentes activos"><p class="text-3xl font-semibold text-white">{{ $summary['total_docentes_activos'] }}</p></x-card>
        <x-card title="Materias activas"><p class="text-3xl font-semibold text-white">{{ $summary['total_materias_activas'] }}</p></x-card>
        <x-card title="Evaluaciones activas"><p class="text-3xl font-semibold text-white">{{ $summary['total_evaluaciones_activas'] }}</p></x-card>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 2xl:grid-cols-7">
        <x-card title="Resultados generados"><p class="text-3xl font-semibold text-white">{{ $summary['total_resultados'] }}</p></x-card>
        <x-card title="Aprobados"><p class="text-3xl font-semibold text-white">{{ $summary['total_aprobados'] }}</p></x-card>
        <x-card title="Reprobados"><p class="text-3xl font-semibold text-white">{{ $summary['total_reprobados'] }}</p></x-card>
        <x-card title="Sin asignacion"><p class="text-3xl font-semibold text-white">{{ $summary['total_sin_asignacion'] }}</p></x-card>
        <x-card title="Promedio general"><p class="text-3xl font-semibold text-white">{{ $summary['promedio_general'] }}</p></x-card>
        <x-card title="Cupos ocupados"><p class="text-3xl font-semibold text-white">{{ $summary['cupos_ocupados'] }}</p></x-card>
        <x-card title="Cupos disponibles"><p class="text-3xl font-semibold text-white">{{ $summary['cupos_disponibles'] }}</p></x-card>
    </div>

    <div class="grid gap-6 2xl:grid-cols-[1fr_1fr]">
        <x-card title="1. Resumen de admision">
            <div class="overflow-x-auto">
                <table class="table min-w-[420px] text-sm">
                    <thead>
                        <tr>
                            <th>Carrera asignada</th>
                            <th>Total aprobados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($aprobadosPorCarrera as $row)
                            <tr>
                                <td>{{ $row->carrera }}</td>
                                <td><span class="badge badge-success">{{ $row->total }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="alert"><span>Sin datos de aprobados por carrera.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4 flex flex-wrap gap-2 text-sm">
                <span class="badge badge-error">Reprobados: {{ $summary['total_reprobados'] }}</span>
                <span class="badge badge-warning">Sin asignacion: {{ $summary['total_sin_asignacion'] }}</span>
            </div>
        </x-card>

        <x-card title="2. Resultados academicos">
            <div class="overflow-x-auto">
                <table class="table min-w-[460px] text-sm">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Promedio</th>
                            <th>Notas registradas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($promedioPorMateria as $row)
                            <tr>
                                <td>{{ $row->materia }}</td>
                                <td>{{ number_format((float) $row->promedio, 2, '.', '') }}</td>
                                <td>{{ $row->total_notas }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="alert"><span>Sin datos de resultados academicos.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="3. Cupos por carrera">
            <div class="overflow-x-auto">
                <table class="table min-w-[420px] text-sm">
                    <thead>
                        <tr>
                            <th>Carrera</th>
                            <th>Ocupados</th>
                            <th>Disponibles</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($cuposPorCarrera as $row)
                            <tr>
                                <td>{{ $row->carrera }}</td>
                                <td><span class="badge badge-info">{{ (int) $row->ocupados }}</span></td>
                                <td><span class="badge badge-success">{{ (int) $row->disponibles }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><div class="alert"><span>Sin datos de cupos para los filtros actuales.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="4. Grupos academicos">
            <div class="space-y-5">
                <div class="overflow-x-auto">
                    <table class="table min-w-[360px] text-sm">
                        <thead>
                            <tr>
                                <th>Gestion</th>
                                <th>Grupos activos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($gruposPorGestion as $row)
                                <tr>
                                    <td>{{ $row->gestion }}</td>
                                    <td>{{ $row->total_grupos }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="2"><div class="alert"><span>Sin datos de grupos por gestion.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="overflow-x-auto">
                    <table class="table min-w-[480px] text-sm">
                        <thead>
                            <tr>
                                <th>Grupo</th>
                                <th>Gestion</th>
                                <th>Estudiantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($estudiantesPorGrupo as $row)
                                <tr>
                                    <td>{{ $row->nombre }}</td>
                                    <td>{{ $row->gestion }}</td>
                                    <td>{{ $row->total_estudiantes }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3"><div class="alert"><span>Sin estudiantes por grupo para los filtros actuales.</span></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </x-card>

        <x-card title="5. Docentes y asignaciones">
            <div class="overflow-x-auto">
                <table class="table min-w-[380px] text-sm">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Asignaciones activas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($docentesConAsignaciones as $row)
                            <tr>
                                <td>{{ trim($row->nombres . ' ' . $row->apellidos) }}</td>
                                <td><span class="badge badge-success">{{ $row->total_asignaciones }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="alert"><span>Sin docentes con asignaciones activas.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="6. Asistencia docente">
            <div class="overflow-x-auto">
                <table class="table min-w-[360px] text-sm">
                    <thead>
                        <tr>
                            <th>Estado asistencia</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($asistenciasPorEstado as $row)
                            <tr>
                                <td><span class="badge badge-info">{{ $row->estado_asistencia }}</span></td>
                                <td>{{ $row->total }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="alert"><span>Sin registros de asistencia para los filtros actuales.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="7. Notas por materia">
            <div class="overflow-x-auto">
                <table class="table min-w-[360px] text-sm">
                    <thead>
                        <tr>
                            <th>Materia</th>
                            <th>Notas registradas</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($notasPorMateria as $row)
                            <tr>
                                <td>{{ $row->materia }}</td>
                                <td>{{ $row->total_notas }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2"><div class="alert"><span>Sin notas registradas para los filtros actuales.</span></div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
@endsection
