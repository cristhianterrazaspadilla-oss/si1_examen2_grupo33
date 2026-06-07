<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reporte extends Model
{
    protected $table = 'reportes';

    protected $fillable = [
        'usuario_id',
        'tipo_reporte',
        'formato',
        'filtros',
        'ruta_archivo',
        'fecha_generacion',
    ];

    protected function casts(): array
    {
        return [
            'filtros' => 'array',
            'fecha_generacion' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
