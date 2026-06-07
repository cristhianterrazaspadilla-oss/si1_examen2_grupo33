<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Grupo extends Model
{
    protected $table = 'grupos';

    protected $fillable = [
        'nombre',
        'codigo',
        'gestion',
        'capacidad_maxima',
        'cantidad_estudiantes',
        'estado',
    ];

    public function grupoPostulantes(): HasMany
    {
        return $this->hasMany(GrupoPostulante::class, 'grupo_id');
    }

    public function docenteAsignaciones(): HasMany
    {
        return $this->hasMany(DocenteAsignacion::class, 'grupo_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'grupo_id');
    }
}
