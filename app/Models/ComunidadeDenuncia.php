<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunidadeDenuncia extends Model
{
    protected $table = 'comunidade_denuncias';

    protected $fillable = [
        'usuario_id',
        'denunciavel_tipo',
        'denunciavel_id',
        'motivo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
