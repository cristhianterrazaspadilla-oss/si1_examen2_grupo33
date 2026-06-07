<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GestionDocenteEvaluacion\DocenteController;
use App\Http\Controllers\GestionDocenteEvaluacion\DocenteAsignacionController;
use App\Http\Controllers\GestionDocenteEvaluacion\AsistenciaDocenteController;
use App\Http\Controllers\GestionDocenteEvaluacion\NotaController;

Route::prefix('gestion-docente-evaluacion')->name('gestion-docente-evaluacion.')->group(function (): void {
    Route::resource('docentes', DocenteController::class);
    Route::resource('asignaciones', DocenteAsignacionController::class);
    Route::resource('asistencias', AsistenciaDocenteController::class);
    Route::resource('notas', NotaController::class);
});

