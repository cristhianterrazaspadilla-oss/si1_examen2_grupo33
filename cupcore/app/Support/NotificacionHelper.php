<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NotificacionHelper
{
    /**
     * Envía un aviso interno sin poner en riesgo la operación principal.
     */
    public static function enviar(?int $receptorId, string $titulo, string $mensaje, string $tipo = 'GENERAL'): void
    {
        if (! $receptorId) {
            return;
        }

        try {
            DB::table('notificaciones')->insert([
                'usuario_emisor_id' => Auth::id(),
                'usuario_receptor_id' => $receptorId,
                'titulo' => Str::limit(trim($titulo), 150, ''),
                'mensaje' => Str::limit(trim($mensaje), 1000, ''),
                'tipo' => Str::upper(trim($tipo)),
                'leido' => false,
                'fecha_lectura' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('No se pudo crear una notificación interna', [
                'receptor_id' => $receptorId,
                'tipo' => $tipo,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
