<?php

use App\Http\Controllers\ReportesDashboardComunicacion\BitacoraController;
use App\Http\Controllers\ReportesDashboardComunicacion\NotificacionController;
use App\Http\Controllers\ReportesDashboardComunicacion\ReporteAcademicoController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->middleware('auth')->group(function (): void {
    // CU16: reportes, exportaciones y dashboard academico.
    Route::middleware('role:administrador,coordinador,autoridad academica')->group(function (): void {
        Route::get('reportes', [ReporteAcademicoController::class, 'index'])->name('reportes.index');
        Route::get('reportes/consulta', [ReporteAcademicoController::class, 'consulta'])->name('reportes.consulta');
        Route::post('reportes/interpretar-comando', [ReporteAcademicoController::class, 'interpretarComando'])
            ->name('reportes.interpretar-comando');
        Route::get('reportes/exportar/csv', [ReporteAcademicoController::class, 'exportarCsv'])
            ->name('reportes.exportar.csv');
        Route::get('reportes/exportar/excel', [ReporteAcademicoController::class, 'exportarExcel'])
            ->name('reportes.exportar.excel');
        Route::get('reportes/historial', [ReporteAcademicoController::class, 'historial'])->name('reportes.historial');
        Route::get('reportes/exportar/pdf', [ReporteAcademicoController::class, 'exportarPdf'])
            ->name('reportes.exportar.pdf');
        Route::get('reportes/dashboard', [ReporteAcademicoController::class, 'dashboard'])->name('reportes.dashboard');
    });

    // CU17: consulta de bitacora del sistema.
    Route::middleware('role:administrador,autoridad academica')->group(function (): void {
        Route::get('bitacoras', [BitacoraController::class, 'index'])->name('bitacoras.index');
        Route::get('bitacoras/{bitacora}', [BitacoraController::class, 'show'])->name('bitacoras.show');
    });

    // CU19: comunicacion interna entre usuarios.
    Route::middleware('role:administrador,coordinador,docente,postulante,autoridad academica')->group(function (): void {
        Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
        Route::get('notificaciones/create', [NotificacionController::class, 'create'])->name('notificaciones.create');
        Route::post('notificaciones', [NotificacionController::class, 'store'])->name('notificaciones.store');
        Route::get('notificaciones/enviadas', [NotificacionController::class, 'enviadas'])->name('notificaciones.enviadas');
        Route::patch('notificaciones/marcar-todas-leidas', [NotificacionController::class, 'marcarTodasLeidas'])
            ->name('notificaciones.marcar-todas-leidas');
        Route::patch('notificaciones/{notificacion}/marcar-leida', [NotificacionController::class, 'marcarLeida'])
            ->name('notificaciones.marcar-leida');
        Route::get('notificaciones/{notificacion}', [NotificacionController::class, 'show'])->name('notificaciones.show');
    });
});
