<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'role:administrador,coordinador,docente,postulante,autoridad academica'])
    ->name('dashboard');

require __DIR__.'/modules/autenticacion_usuarios_seguridad.php';
require __DIR__.'/modules/gestion_postulantes_admision.php';
require __DIR__.'/modules/gestion_academica_cup.php';
