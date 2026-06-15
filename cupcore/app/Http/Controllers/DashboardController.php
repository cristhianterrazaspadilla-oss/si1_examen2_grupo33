<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\Docente;
use App\Models\DocenteAsignacion;
use App\Models\Grupo;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\ResultadoAdmision;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

/**
 * Dashboard General del Sistema
 *
 * Funciona como landing page tras la autenticación y actúa como resumen ejecutivo del sistema.
 * Adapta dinámicamente los KPIs y estadísticas principales en base al Rol del usuario activo.
 * El menú de navegación (sidebar) se filtra de forma dinámica por rol directamente en el layout
 * principal (layouts/app.blade.php), eliminando la necesidad de una página activa "/panel-control".
 */
class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $roleName = Str::of((string) ($user?->rol?->nombre ?? ''))->lower()->ascii()->toString();
        $userName = trim((string) (($user?->nombre ?? '').' '.($user?->apellido ?? '')));

        $stats = [];

        if ($roleName === 'administrador') {
            $stats = [
                'total_usuarios' => User::count(),
                'usuarios_activos' => User::where('estado', 'ACTIVO')->count(),
                'total_roles' => Role::query()->institutional()->count(),
                'total_bitacora' => Bitacora::count(),
            ];
        } elseif ($roleName === 'coordinador') {
            $stats = [
                'total_postulantes' => Postulante::count(),
                'total_docentes' => Docente::count(),
                'total_materias' => Materia::count(),
                'total_grupos' => Grupo::count(),
            ];
        } elseif ($roleName === 'docente') {
            $docente = $user?->docente;
            $stats = [
                'tiene_registro' => ! empty($docente),
                'profesion' => $docente?->profesion ?? 'No especificada',
                'especialidad' => $docente?->especialidad ?? 'No especificada',
                'total_asignaciones' => $docente ? DocenteAsignacion::where('docente_id', $docente->id)->count() : 0,
            ];
        } elseif ($roleName === 'postulante') {
            $postulante = $user?->postulante;
            $stats = [
                'tiene_registro' => ! empty($postulante),
                'estado_inscripcion' => $postulante?->estado_inscripcion ?? 'No registrado',
                'estado_admision' => $postulante?->estado_admision ?? 'PENDIENTE',
                'carrera_1' => $postulante?->carreraPrimeraOpcion?->nombre ?? 'Ninguna',
                'carrera_2' => $postulante?->carreraSegundaOpcion?->nombre ?? 'Ninguna',
                'resultado' => $postulante?->resultadoAdmision,
            ];
        } elseif ($roleName === 'autoridad academica') {
            $stats = [
                'total_inscritos' => Postulante::where('estado_inscripcion', 'INSCRITO')->count(),
                'total_admitidos' => ResultadoAdmision::where('estado_resultado', 'APROBADO')->count(),
                'total_docentes' => Docente::count(),
                'promedio_general' => round(ResultadoAdmision::avg('promedio_final') ?? 0, 2),
            ];
        }

        return view('dashboard.index', [
            'roleName' => $user?->rol?->nombre ?? 'Sin rol',
            'userName' => $userName,
            'stats' => $stats,
        ]);
    }
}
