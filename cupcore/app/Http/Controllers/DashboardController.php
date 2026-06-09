<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $roleName = Str::of((string) ($user?->rol?->nombre ?? ''))->lower()->ascii()->toString();
        $userName = trim((string) (($user?->nombre ?? '') . ' ' . ($user?->apellido ?? '')));

        $stats = [];
        
        if ($roleName === 'administrador') {
            $stats = [
                'total_usuarios' => \App\Models\User::count(),
                'usuarios_activos' => \App\Models\User::where('estado', 'ACTIVO')->count(),
                'total_roles' => \App\Models\Role::count(),
                'total_bitacora' => \App\Models\Bitacora::count(),
            ];
        } elseif ($roleName === 'coordinador') {
            $stats = [
                'total_postulantes' => \App\Models\Postulante::count(),
                'total_docentes' => \App\Models\Docente::count(),
                'total_materias' => \App\Models\Materia::count(),
                'total_grupos' => \App\Models\Grupo::count(),
            ];
        } elseif ($roleName === 'docente') {
            $docente = $user?->docente;
            $stats = [
                'tiene_registro' => !empty($docente),
                'profesion' => $docente?->profesion ?? 'No especificada',
                'especialidad' => $docente?->especialidad ?? 'No especificada',
                'total_asignaciones' => $docente ? \App\Models\DocenteAsignacion::where('docente_id', $docente->id)->count() : 0,
            ];
        } elseif ($roleName === 'postulante') {
            $postulante = $user?->postulante;
            $stats = [
                'tiene_registro' => !empty($postulante),
                'estado_inscripcion' => $postulante?->estado_inscripcion ?? 'No registrado',
                'estado_admision' => $postulante?->estado_admision ?? 'PENDIENTE',
                'carrera_1' => $postulante?->carreraPrimeraOpcion?->nombre ?? 'Ninguna',
                'carrera_2' => $postulante?->carreraSegundaOpcion?->nombre ?? 'Ninguna',
                'resultado' => $postulante?->resultadoAdmision,
            ];
        } elseif ($roleName === 'autoridad academica') {
            $stats = [
                'total_inscritos' => \App\Models\Postulante::where('estado_inscripcion', 'INSCRITO')->count(),
                'total_admitidos' => \App\Models\ResultadoAdmision::where('estado_resultado', 'APROBADO')->count(),
                'total_docentes' => \App\Models\Docente::count(),
                'promedio_general' => round(\App\Models\ResultadoAdmision::avg('promedio_final') ?? 0, 2),
            ];
        }

        return view('dashboard.index', [
            'roleName' => $user?->rol?->nombre ?? 'Sin rol',
            'userName' => $userName,
            'stats' => $stats,
        ]);
    }
}
