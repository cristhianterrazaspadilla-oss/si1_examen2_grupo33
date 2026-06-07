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
    Route::resource('postulantes', PostulanteController::class);
    Route::resource('requisitos', RequisitoController::class);
    Route::resource('postulante-requisitos', PostulanteRequisitoController::class);
    Route::get('requisitos-postulantes', [PostulanteRequisitoController::class, 'index'])
        ->middleware('auth')
        ->name('requisitos-postulantes.index');
    Route::get('requisitos-postulantes/{postulante}', [PostulanteRequisitoController::class, 'show'])
        ->middleware('auth')
        ->name('requisitos-postulantes.show');
    Route::put('requisitos-postulantes/{postulante}', [PostulanteRequisitoController::class, 'update'])
        ->middleware('auth')
        ->name('requisitos-postulantes.update');
    Route::post('pagos/{pago}/verificar', [PagoController::class, 'verificar'])
        ->middleware('auth')
        ->name('pagos.verificar');
    Route::resource('pagos', PagoController::class);
    Route::resource('carreras', CarreraController::class);
    Route::resource('cupos', CupoCarreraController::class);
    Route::resource('resultados', ResultadoAdmisionController::class);
});

