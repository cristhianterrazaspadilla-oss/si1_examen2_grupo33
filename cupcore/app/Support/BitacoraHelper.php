<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BitacoraHelper
{
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
