<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\AuthController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\UsuarioController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\RolController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\ImportacionController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\PasswordController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::prefix('autenticacion-usuarios-seguridad')
    ->name('autenticacion-usuarios-seguridad.')
    ->middleware('auth')
    ->group(function (): void {
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('roles', RolController::class);
        Route::resource('importaciones', ImportacionController::class);
        Route::resource('password', PasswordController::class);
    });