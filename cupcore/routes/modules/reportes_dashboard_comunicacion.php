<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReportesDashboardComunicacion\DashboardController;
use App\Http\Controllers\ReportesDashboardComunicacion\ReporteController;
use App\Http\Controllers\ReportesDashboardComunicacion\BitacoraController;
use App\Http\Controllers\ReportesDashboardComunicacion\NotificacionController;

Route::prefix('reportes-dashboard-comunicacion')->name('reportes-dashboard-comunicacion.')->group(function (): void {
    Route::resource('dashboard', DashboardController::class);
    Route::resource('reportes', ReporteController::class);
    Route::resource('bitacoras', BitacoraController::class);
    Route::resource('notificaciones', NotificacionController::class);
});

