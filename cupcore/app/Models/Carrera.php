<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Carrera extends Model
{
    protected $table = 'carreras';

    protected $fillable = [
        'nombre',
        'codigo',
        'descripcion',
        'estado',
    ];

    public function cuposCarrera(): HasMany
    {
        return $this->hasMany(CupoCarrera::class, 'carrera_id');
    }

    public function postulantesPrimeraOpcion(): HasMany
    {
        return $this->hasMany(Postulante::class, 'carrera_primera_opcion_id');
    }

    public function postulantesSegundaOpcion(): HasMany
    {
        return $this->hasMany(Postulante::class, 'carrera_segunda_opcion_id');
    }

    public function resultadosAdmision(): HasMany
    {
        return $this->hasMany(ResultadoAdmision::class, 'carrera_asignada_id');
    }
}
