<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $roleName = Str::of((string) ($user?->rol?->nombre ?? ''))->lower()->ascii()->toString();

        $packages = collect([
            [
                'key' => 'seguridad',
                'title' => 'Autenticacion, Usuarios y Seguridad',
                'description' => 'Administracion del acceso institucional, usuarios operativos y flujos de soporte de credenciales.',
                'accent' => 'from-blue-500/30 via-cyan-400/10 to-transparent',
                'roles' => ['administrador'],
                'use_cases' => [
                    [
                        'code' => 'CU3',
                        'title' => 'Administrar Usuarios y Roles',
                        'roles' => ['administrador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Usuarios', 'route' => 'autenticacion-usuarios-seguridad.usuarios.index'],
                            ['label' => 'Roles', 'route' => 'autenticacion-usuarios-seguridad.roles.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU4',
                        'title' => 'Importar Datos Masivos Excel/CSV',
                        'roles' => ['administrador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Importaciones', 'route' => 'autenticacion-usuarios-seguridad.importaciones.index'],
                        ]),
                    ],
                ],
            ],
            [
                'key' => 'admision',
                'title' => 'Gestion de Postulantes y Admision',
                'description' => 'Seguimiento del ciclo de admision: pre-registro, requisitos, pagos, carreras, cupos y resultados.',
                'accent' => 'from-indigo-500/30 via-blue-500/10 to-transparent',
                'roles' => ['administrador', 'coordinador', 'postulante'],
                'use_cases' => [
                    [
                        'code' => 'CU5',
                        'title' => 'Gestionar Inscripcion de Postulantes',
                        'roles' => ['administrador', 'postulante'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Postulantes', 'route' => 'gestion-postulantes-admision.postulantes.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU6',
                        'title' => 'Gestionar Requisitos de Admision',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Catalogo', 'route' => 'gestion-postulantes-admision.requisitos.index'],
                            ['label' => 'Validacion', 'route' => 'gestion-postulantes-admision.requisitos-postulantes.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU7',
                        'title' => 'Gestionar Pagos',
                        'roles' => ['administrador', 'coordinador', 'postulante'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Pagos', 'route' => 'gestion-postulantes-admision.pagos.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU8',
                        'title' => 'Administrar Carreras y Cupos',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Carreras', 'route' => 'gestion-postulantes-admision.carreras.index'],
                            ['label' => 'Cupos', 'route' => 'gestion-postulantes-admision.cupos.index'],
                        ]),
                    ],
                ],
            ],
            [
                'key' => 'academica',
                'title' => 'Gestion Academica del CUP',
                'description' => 'Planificacion de materias, grupos y ocupacion de aulas para el ciclo preuniversitario.',
                'accent' => 'from-sky-500/30 via-blue-400/10 to-transparent',
                'roles' => ['administrador', 'coordinador'],
                'use_cases' => [
                    [
                        'code' => 'CU9',
                        'title' => 'Administrar Materias y Evaluaciones',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Materias', 'route' => 'gestion-academica-cup.materias.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU10',
                        'title' => 'Organizar Grupos Academicos',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Grupos', 'route' => 'gestion-academica-cup.grupos.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU11',
                        'title' => 'Gestionar Horarios y Aulas',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Horarios', 'route' => 'gestion-academica-cup.horarios.index'],
                            ['label' => 'Aulas', 'route' => 'gestion-academica-cup.aulas.index'],
                        ]),
                    ],
                ],
            ],
            [
                'key' => 'docencia',
                'title' => 'Gestion Docente y Evaluacion Academica',
                'description' => 'Coordinacion de docentes, asignaciones, asistencia y seguimiento de desempeno academico.',
                'accent' => 'from-cyan-500/30 via-sky-400/10 to-transparent',
                'roles' => ['administrador', 'coordinador', 'docente'],
                'use_cases' => [
                    [
                        'code' => 'CU12',
                        'title' => 'Gestionar Docentes y Asignaciones',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Docentes', 'route' => 'gestion-academica-cup.docentes.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU13',
                        'title' => 'Registrar Asistencia Docente',
                        'roles' => ['docente'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Asistencias', 'route' => 'gestion-academica-cup.asistencias-docentes.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU14',
                        'title' => 'Gestionar Notas y Seguimiento Academico',
                        'roles' => ['coordinador', 'docente'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Notas', 'route' => 'gestion-academica-cup.notas.index'],
                            ['label' => 'Seguimiento', 'route' => 'gestion-academica-cup.notas.seguimiento'],
                        ]),
                    ],
                ],
            ],
            [
                'key' => 'reportes',
                'title' => 'Reportes, Dashboard y Comunicacion Interna',
                'description' => 'Analitica, bitacora institucional y notificaciones internas para la operacion del curso.',
                'accent' => 'from-blue-600/30 via-indigo-400/10 to-transparent',
                'roles' => ['administrador', 'coordinador', 'docente', 'postulante', 'autoridad academica'],
                'use_cases' => [
                    [
                        'code' => 'CU16',
                        'title' => 'Generar Reportes y Dashboard Academico',
                        'roles' => ['administrador', 'coordinador', 'autoridad academica'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Reportes', 'route' => 'gestion-academica-cup.reportes.consulta'],
                        ]),
                    ],
                    [
                        'code' => 'CU17',
                        'title' => 'Consultar Bitacora del Sistema',
                        'roles' => ['administrador', 'autoridad academica'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Bitacora', 'route' => 'gestion-academica-cup.bitacoras.index'],
                        ]),
                    ],
                    [
                        'code' => 'CU15',
                        'title' => 'Gestionar Resultados de Admision',
                        'roles' => ['administrador', 'coordinador'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Resultados', 'route' => 'gestion-academica-cup.resultados.index'],
                            ['label' => 'Pendientes', 'route' => 'gestion-academica-cup.resultados.pendientes'],
                        ]),
                    ],
                    [
                        'code' => 'CU19',
                        'title' => 'Gestionar Notificaciones Internas',
                        'roles' => ['administrador', 'coordinador', 'docente', 'postulante', 'autoridad academica'],
                        'status' => 'Implementado',
                        'links' => $this->resolvedLinks([
                            ['label' => 'Notificaciones', 'route' => 'gestion-academica-cup.notificaciones.index'],
                        ]),
                    ],
                ],
            ],
        ]);

        $visiblePackages = $packages
            ->filter(fn (array $package) => in_array($roleName, $package['roles'], true))
            ->map(function (array $package) use ($roleName): array {
                $package['use_cases'] = collect($package['use_cases'])
                    ->filter(fn (array $useCase) => in_array($roleName, $useCase['roles'], true))
                    ->map(function (array $useCase): array {
                        unset($useCase['roles']);

                        return $useCase;
                    })
                    ->values()
                    ->all();

                return $package;
            })
            ->filter(fn (array $package) => ! empty($package['use_cases']))
            ->values();

        $implementedCount = $visiblePackages->sum(
            fn (array $package) => collect($package['use_cases'])->where('status', 'Implementado')->count()
        );
        $pendingCount = $visiblePackages->sum(
            fn (array $package) => collect($package['use_cases'])->where('status', 'Pendiente')->count()
        );

        return view('dashboard.index', [
            'packages' => $visiblePackages,
            'implementedCount' => $implementedCount,
            'pendingCount' => $pendingCount,
            'roleName' => $user?->rol?->nombre ?? 'Sin rol',
            'userName' => trim((string) (($user?->nombre ?? '') . ' ' . ($user?->apellido ?? ''))),
        ]);
    }

    /**
     * @param  array<int, array{label:string, route:string}>  $links
     * @return array<int, array{label:string, route:string, url:string}>
     */
    protected function resolvedLinks(array $links): array
    {
        return collect($links)
            ->filter(fn (array $link) => Route::has($link['route']))
            ->map(fn (array $link) => [
                'label' => $link['label'],
                'route' => $link['route'],
                'url' => route($link['route']),
            ])
            ->values()
            ->all();
    }
}
