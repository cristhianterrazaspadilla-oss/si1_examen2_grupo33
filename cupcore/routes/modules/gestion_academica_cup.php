<?php

use App\Http\Controllers\GestionAcademicaCUP\AsistenciaDocenteController;
use App\Http\Controllers\GestionAcademicaCUP\AulaController;
use App\Http\Controllers\GestionAcademicaCUP\BitacoraController;
use App\Http\Controllers\GestionAcademicaCUP\DocenteAsignacionController;
use App\Http\Controllers\GestionAcademicaCUP\DocenteController;
use App\Http\Controllers\GestionAcademicaCUP\EvaluacionController;
use App\Http\Controllers\GestionAcademicaCUP\GrupoController;
use App\Http\Controllers\GestionAcademicaCUP\HorarioController;
use App\Http\Controllers\GestionAcademicaCUP\MateriaController;
use App\Http\Controllers\GestionAcademicaCUP\NotaController;
use App\Http\Controllers\GestionAcademicaCUP\NotificacionController;
use App\Http\Controllers\GestionAcademicaCUP\ReporteAcademicoController;
use App\Http\Controllers\GestionAcademicaCUP\ResultadoAdmisionController;
use Illuminate\Support\Facades\Route;

Route::prefix('gestion-academica-cup')->name('gestion-academica-cup.')->middleware('auth')->group(function (): void {
    // CU9-CU12 y CU15: administración y coordinación académica.
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

        Route::patch('aulas/{aula}/activar', [AulaController::class, 'activar'])->name('aulas.activar');
        Route::patch('horarios/{horario}/activar', [HorarioController::class, 'activar'])->name('horarios.activar');
        Route::resource('aulas', AulaController::class);
        Route::resource('horarios', HorarioController::class);

        Route::get('resultados/generar', [ResultadoAdmisionController::class, 'create'])->name('resultados.generar');
        Route::post('resultados/masivo', [ResultadoAdmisionController::class, 'generacionMasiva'])->name('resultados.masivo');
        Route::get('resultados/pendientes', [ResultadoAdmisionController::class, 'pendientes'])->name('resultados.pendientes');
        Route::post('resultados', [ResultadoAdmisionController::class, 'store'])->name('resultados.store');
        Route::resource('resultados', ResultadoAdmisionController::class)->only(['index', 'show', 'edit', 'update']);
    });

    // CU13: cada docente registra y consulta únicamente su propia asistencia.
    Route::middleware('role:docente')->group(function (): void {
        Route::resource('asistencias-docentes', AsistenciaDocenteController::class)
            ->except(['destroy'])
            ->parameters(['asistencias-docentes' => 'asistenciaDocente']);
    });

    // CU14: coordinadores y docentes administran notas; el controlador limita al docente a sus asignaciones.
    Route::middleware('role:coordinador,docente')->group(function (): void {
        Route::get('notas/seguimiento', [NotaController::class, 'seguimiento'])->name('notas.seguimiento');
        Route::resource('notas', NotaController::class)->except(['destroy']);
    });

    // CU16: información institucional para administración, coordinación y autoridad.
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

    Route::middleware('role:administrador,autoridad academica')->group(function (): void {
        Route::get('bitacoras', [BitacoraController::class, 'index'])->name('bitacoras.index');
        Route::get('bitacoras/{bitacora}', [BitacoraController::class, 'show'])->name('bitacoras.show');
    });

    // Todos reciben notificaciones; solo administrador y coordinador pueden enviarlas.
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
