<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = [
        'usuario_emisor_id',
        'usuario_receptor_id',
        'titulo',
        'mensaje',
        'tipo',
        'leido',
        'fecha_lectura',
    ];

    protected function casts(): array
    {
        return [
            'fecha_lectura' => 'datetime',
            'leido' => 'boolean',
        ];
    }

    public function usuarioEmisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_emisor_id');
    }

    public function usuarioReceptor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_receptor_id');
    }
}
