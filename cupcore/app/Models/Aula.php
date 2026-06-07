<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aula extends Model
{
    protected $table = 'aulas';

    protected $fillable = [
        'nombre',
        'codigo',
        'ubicacion',
        'capacidad',
        'estado',
    ];

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'aula_id');
    }
}
