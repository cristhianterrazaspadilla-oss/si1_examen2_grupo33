<?php

use App\Http\Controllers\AutenticacionUsuariosSeguridad\AuthController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\ImportacionController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\PasswordController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\RolController;
use App\Http\Controllers\AutenticacionUsuariosSeguridad\UsuarioController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/recuperar-password', [PasswordController::class, 'index'])->name('password.demo');
Route::post('/recuperar-password', [PasswordController::class, 'store'])->name('password.demo.store');

Route::prefix('autenticacion-usuarios-seguridad')
    ->name('autenticacion-usuarios-seguridad.')
    ->middleware(['auth', 'role:administrador'])
    ->group(function (): void {
        Route::resource('usuarios', UsuarioController::class);
        Route::resource('roles', RolController::class)->only(['index', 'show']);
        Route::resource('importaciones', ImportacionController::class);
    });
