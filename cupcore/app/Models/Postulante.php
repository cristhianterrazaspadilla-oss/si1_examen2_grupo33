<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Postulante extends Model
{
    protected $table = 'postulantes';

    protected $fillable = [
        'usuario_id',
        'ci',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'sexo',
        'tipo_sangre',
        'direccion',
        'telefono',
        'correo',
        'colegio_procedencia',
        'ciudad',
        'carrera_primera_opcion_id',
        'carrera_segunda_opcion_id',
        'titulo_bachiller',
        'estado_inscripcion',
        'estado_admision',
    ];

    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'titulo_bachiller' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function carreraPrimeraOpcion(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_primera_opcion_id');
    }

    public function carreraSegundaOpcion(): BelongsTo
    {
        return $this->belongsTo(Carrera::class, 'carrera_segunda_opcion_id');
    }

    public function postulanteRequisitos(): HasMany
    {
        return $this->hasMany(PostulanteRequisito::class, 'postulante_id');
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'postulante_id');
    }

    public function notas(): HasMany
    {
        return $this->hasMany(Nota::class, 'postulante_id');
    }

    public function grupoPostulantes(): HasMany
    {
        return $this->hasMany(GrupoPostulante::class, 'postulante_id');
    }

    public function resultadoAdmision(): HasOne
    {
        return $this->hasOne(ResultadoAdmision::class, 'postulante_id');
    }
}
