<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Helper de Auditoría General del Sistema.
 *
 * Centraliza la persistencia de las actividades y eventos generados por los usuarios
 * en la tabla `bitacoras`. Diseñado de forma tolerante a fallos: si el registro
 * de bitácora falla, la operación principal no debe verse interrumpida, 
 * sino que se captura la excepción y se escribe una advertencia en el log de Laravel.
 */
class BitacoraHelper
{
    /**
     * Registra un evento de auditoría en la base de datos de manera tolerante a fallos.
     */
    public static function registrar(
        string $accion,
        string $modulo,
        ?string $descripcion = null,
        ?int $usuarioId = null
    ): void {
        try {
            $resolvedUsuarioId = $usuarioId ?? Auth::id();
            $ipAddress = app()->bound('request') ? request()->ip() : null;

            DB::table('bitacoras')->insert([
                'usuario_id' => $resolvedUsuarioId,
                'accion' => Str::upper(trim($accion)),
                'descripcion' => $descripcion !== null ? Str::limit(trim($descripcion), 500, '') : null,
                'modulo' => trim($modulo),
                'ip_address' => $ipAddress,
                'fecha' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo registrar accion en bitacora', [
                'accion' => $accion,
                'modulo' => $modulo,
                'usuario_id' => $usuarioId ?? Auth::id(),
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
