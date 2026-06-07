<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Docente extends Model
{
    protected $table = 'docentes';

    protected $fillable = [
        'usuario_id',
        'ci',
        'nombres',
        'apellidos',
        'correo',
        'telefono',
        'profesion',
        'especialidad',
        'tiene_maestria',
        'tiene_diplomado',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'tiene_maestria' => 'boolean',
            'tiene_diplomado' => 'boolean',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function asignaciones(): HasMany
    {
        return $this->hasMany(DocenteAsignacion::class, 'docente_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(Horario::class, 'docente_id');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaDocente::class, 'docente_id');
    }
}