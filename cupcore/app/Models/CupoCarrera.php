<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CupoCarrera extends Model
{
    protected $table = 'cupos_carrera';

    protected $fillable = [
        'carrera_id',
        'gestion',
        'cupo_maximo',
        'cupos_ocupados',
        'cupos_disponibles',
        'estado',
    ];

    public function carrera(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_id');
    }
}
