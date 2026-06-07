<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GestionAcademicaCUP\MateriaController;
use App\Http\Controllers\GestionAcademicaCUP\EvaluacionController;
use App\Http\Controllers\GestionAcademicaCUP\GrupoController;
use App\Http\Controllers\GestionAcademicaCUP\GrupoPostulanteController;
use App\Http\Controllers\GestionAcademicaCUP\AulaController;
use App\Http\Controllers\GestionAcademicaCUP\HorarioController;
use App\Http\Controllers\GestionAcademicaCUP\DocenteController;
use App\Http\Controllers\GestionAcademicaCUP\DocenteAsignacionController;
use App\Http\Controllers\GestionAcademicaCUP\AsistenciaDocenteController;
use App\Http\Controllers\GestionAcademicaCUP\NotaController;

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->group(function (): void {
    Route::middleware('auth')->group(function (): void {
        Route::resource('materias', MateriaController::class);
        Route::get('materias/{materia}/evaluaciones/create', [EvaluacionController::class, 'create'])
            ->name('materias.evaluaciones.create');
        Route::post('materias/{materia}/evaluaciones', [EvaluacionController::class, 'store'])
            ->name('materias.evaluaciones.store');
        Route::get('evaluaciones/{evaluacion}/edit', [EvaluacionController::class, 'edit'])
            ->name('evaluaciones.edit');
        Route::put('evaluaciones/{evaluacion}', [EvaluacionController::class, 'update'])
            ->name('evaluaciones.update');
        Route::delete('evaluaciones/{evaluacion}', [EvaluacionController::class, 'destroy'])
            ->name('evaluaciones.destroy');
        Route::get('grupos/organizar', [GrupoController::class, 'create'])
            ->name('grupos.organizar');
        Route::post('grupos/organizar', [GrupoController::class, 'store'])
            ->name('grupos.organizar.store');
        Route::resource('grupos', GrupoController::class);
        Route::resource('docentes', DocenteController::class);
        Route::patch('docentes/{docente}/activar', [DocenteController::class, 'activar'])
            ->name('docentes.activar');
        Route::get('docentes/{docente}/asignaciones/create', [DocenteAsignacionController::class, 'create'])
            ->name('docentes.asignaciones.create');
        Route::post('docentes/{docente}/asignaciones', [DocenteAsignacionController::class, 'store'])
            ->name('docentes.asignaciones.store');
        Route::get('docente-asignaciones/{asignacion}/edit', [DocenteAsignacionController::class, 'edit'])
            ->name('docente-asignaciones.edit');
        Route::patch('docente-asignaciones/{asignacion}/activar', [DocenteAsignacionController::class, 'activar'])
            ->name('docente-asignaciones.activar');
        Route::put('docente-asignaciones/{asignacion}', [DocenteAsignacionController::class, 'update'])
            ->name('docente-asignaciones.update');
        Route::delete('docente-asignaciones/{asignacion}', [DocenteAsignacionController::class, 'destroy'])
            ->name('docente-asignaciones.destroy');
        Route::patch('aulas/{aula}/activar', [AulaController::class, 'activar'])
            ->name('aulas.activar');
        Route::patch('horarios/{horario}/activar', [HorarioController::class, 'activar'])
            ->name('horarios.activar');
        Route::resource('aulas', AulaController::class);
        Route::resource('horarios', HorarioController::class);
        Route::resource('asistencias-docentes', AsistenciaDocenteController::class)
            ->except(['destroy'])
            ->parameters(['asistencias-docentes' => 'asistenciaDocente']);
        Route::get('notas/seguimiento', [NotaController::class, 'seguimiento'])
            ->name('notas.seguimiento');
        Route::resource('notas', NotaController::class)
            ->except(['destroy']);
    });
    Route::resource('grupo-postulantes', GrupoPostulanteController::class);
});

