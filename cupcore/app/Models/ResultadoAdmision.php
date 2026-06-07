<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResultadoAdmision extends Model
{
    protected $table = 'resultados_admision';

    protected $fillable = [
        'postulante_id',
        'promedio_final',
        'estado_resultado',
        'carrera_asignada_id',
        'tipo_asignacion',
        'observacion',
        'justificacion_modificacion',
        'modificado_por',
        'fecha_resultado',
    ];

    protected function casts(): array
    {
        return [
            'promedio_final' => 'decimal:2',
            'fecha_resultado' => 'datetime',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function carreraAsignada(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_asignada_id');
    }

    public function modificadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'modificado_por');
    }
}
