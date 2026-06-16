<?php

use App\Http\Controllers\GestionAcademicaCUP\AulaController;
use App\Http\Controllers\GestionAcademicaCUP\EvaluacionController;
use App\Http\Controllers\GestionAcademicaCUP\GrupoController;
use App\Http\Controllers\GestionAcademicaCUP\HorarioController;
use App\Http\Controllers\GestionAcademicaCUP\MateriaController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->middleware('auth')->group(function (): void {
    // CU9-CU11: organizacion academica del CUP.
    Route::middleware('role:administrador,coordinador')->group(function (): void {
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

        Route::get('grupos/organizar', [GrupoController::class, 'create'])->name('grupos.organizar');
        Route::post('grupos/organizar', [GrupoController::class, 'store'])->name('grupos.organizar.store');
        Route::resource('grupos', GrupoController::class);

        Route::patch('aulas/{aula}/activar', [AulaController::class, 'activar'])->name('aulas.activar');
        Route::patch('horarios/{horario}/activar', [HorarioController::class, 'activar'])->name('horarios.activar');
        Route::resource('aulas', AulaController::class);
        Route::resource('horarios', HorarioController::class);
    });
});
