<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GestionPostulantesAdmision\PostulanteController;
use App\Http\Controllers\GestionPostulantesAdmision\RequisitoController;
use App\Http\Controllers\GestionPostulantesAdmision\PostulanteRequisitoController;
use App\Http\Controllers\GestionPostulantesAdmision\PagoController;
use App\Http\Controllers\GestionPostulantesAdmision\CarreraController;
use App\Http\Controllers\GestionPostulantesAdmision\CupoCarreraController;
use App\Http\Controllers\GestionPostulantesAdmision\ResultadoAdmisionController;

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
