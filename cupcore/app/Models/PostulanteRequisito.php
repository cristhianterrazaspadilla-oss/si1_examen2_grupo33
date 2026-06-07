<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostulanteRequisito extends Model
{
    protected $table = 'postulante_requisitos';

    protected $fillable = [
        'postulante_id',
        'requisito_id',
        'estado',
        'observacion',
        'fecha_validacion',
        'validado_por',
    ];

    protected function casts(): array
    {
        return [
            'fecha_validacion' => 'datetime',
        ];
    }

    public function postulante(): BelongsTo
    {
        return $this->belongsTo(Postulante::class, 'postulante_id');
    }

    public function requisito(): BelongsTo
    {
        return $this->belongsTo(Requisito::class, 'requisito_id');
    }

    public function validadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
