<?php

use App\Http\Controllers\GestionDocenteEvaluacion\AsistenciaDocenteController;
use App\Http\Controllers\GestionDocenteEvaluacion\DocenteAsignacionController;
use App\Http\Controllers\GestionDocenteEvaluacion\DocenteController;
use App\Http\Controllers\GestionDocenteEvaluacion\NotaController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->middleware('auth')->group(function (): void {
    // CU12: administracion de docentes y asignaciones.
    Route::middleware('role:administrador,coordinador')->group(function (): void {
        Route::resource('docentes', DocenteController::class);
        Route::patch('docentes/{docente}/activar', [DocenteController::class, 'activar'])->name('docentes.activar');
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
    });

    // CU13: cada docente registra y consulta unicamente su propia asistencia.
    Route::middleware('role:docente')->group(function (): void {
        Route::resource('asistencias-docentes', AsistenciaDocenteController::class)
            ->except(['destroy'])
            ->parameters(['asistencias-docentes' => 'asistenciaDocente']);
    });

    // CU14: coordinadores y docentes administran notas.
    Route::middleware('role:coordinador,docente')->group(function (): void {
        Route::get('notas/seguimiento', [NotaController::class, 'seguimiento'])->name('notas.seguimiento');
        Route::resource('notas', NotaController::class)->except(['destroy']);
    });
});
