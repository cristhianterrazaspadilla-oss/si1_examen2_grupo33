<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public const INSTITUTIONAL_NAMES = [
        'Administrador',
        'Coordinador',
        'Docente',
        'Postulante',
        'Autoridad Académica',
    ];

    protected $table = 'roles';

    protected $fillable = [
        'nombre',
        'descripcion',
        'estado',
    ];

    public function usuarios(): HasMany
    {
        return $this->hasMany(User::class, 'rol_id');
    }

    public function scopeInstitutional(Builder $query): Builder
    {
        return $query->whereIn('nombre', self::INSTITUTIONAL_NAMES);
    }
}
