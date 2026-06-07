<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    protected $table = 'materias';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'estado',
    ];

    public function evaluaciones(): HasMany
    {
        return $this->hasMany(Evaluacion::class, 'materia_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'materia_id');
    }

    public function docenteAsignaciones(): HasMany
    {
        return $this->hasMany(DocenteAsignacion::class, 'materia_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'materia_id');
    }
}
