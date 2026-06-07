<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisito extends Model
{
    protected $table = 'requisitos';

    protected $fillable = [
        'nombre',
        'descripcion',
        'obligatorio',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'obligatorio' => 'boolean',
        ];
    }

    public function postulanteRequisitos(): HasMany
    {
        return $this->hasMany(PostulanteRequisito::class, 'requisito_id');
    }
}
