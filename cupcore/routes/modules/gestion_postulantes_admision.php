<?php

use App\Http\Controllers\GestionPostulantesAdmision\CarreraController;
use App\Http\Controllers\GestionPostulantesAdmision\CupoCarreraController;
use App\Http\Controllers\GestionPostulantesAdmision\PagoController;
use App\Http\Controllers\GestionPostulantesAdmision\PostulanteController;
use App\Http\Controllers\GestionPostulantesAdmision\PostulanteRequisitoController;
use App\Http\Controllers\GestionPostulantesAdmision\RequisitoController;
use App\Http\Controllers\GestionPostulantesAdmision\ResultadoAdmisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestion-postulantes-admision')->name('gestion-postulantes-admision.')->group(function (): void {
    Route::middleware(['auth', 'role:administrador,postulante'])->group(function (): void {
        Route::resource('postulantes', PostulanteController::class);
    });

    Route::middleware(['auth', 'role:administrador,coordinador'])->group(function (): void {
        Route::resource('requisitos', RequisitoController::class);
        Route::get('requisitos-postulantes', [PostulanteRequisitoController::class, 'index'])
            ->name('requisitos-postulantes.index');
        Route::get('requisitos-postulantes/{postulante}', [PostulanteRequisitoController::class, 'show'])
            ->name('requisitos-postulantes.show');
        Route::put('requisitos-postulantes/{postulante}', [PostulanteRequisitoController::class, 'update'])
            ->name('requisitos-postulantes.update');
        Route::resource('carreras', CarreraController::class);
        Route::resource('cupos', CupoCarreraController::class);
    });

    Route::middleware(['auth', 'role:administrador,coordinador,postulante'])->group(function (): void {
        Route::post('pagos/{pago}/verificar', [PagoController::class, 'verificar'])
            ->name('pagos.verificar');
        Route::resource('pagos', PagoController::class);
    });
});

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->middleware('auth')->group(function (): void {
    // CU15: resultados finales de admision.
    Route::middleware('role:administrador,coordinador')->group(function (): void {
        Route::get('resultados/generar', [ResultadoAdmisionController::class, 'create'])->name('resultados.generar');
        Route::post('resultados/masivo', [ResultadoAdmisionController::class, 'generacionMasiva'])->name('resultados.masivo');
        Route::get('resultados/pendientes', [ResultadoAdmisionController::class, 'pendientes'])->name('resultados.pendientes');
        Route::post('resultados', [ResultadoAdmisionController::class, 'store'])->name('resultados.store');
        Route::resource('resultados', ResultadoAdmisionController::class)->only(['index', 'show', 'edit', 'update']);
    });
});
