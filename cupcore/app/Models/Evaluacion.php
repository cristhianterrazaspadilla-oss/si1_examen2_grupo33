<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Evaluacion extends Model
{
    protected $table = 'evaluaciones';

    protected $fillable = [
        'materia_id',
        'nombre',
        'numero_evaluacion',
        'fecha_evaluacion',
        'porcentaje',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_evaluacion' => 'date',
            'porcentaje' => 'decimal:2',
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'evaluacion_id');
    }
}
