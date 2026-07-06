<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ComunidadeComentario extends Model
{
    protected $table = 'comunidade_comentarios';

    protected $fillable = [
        'post_id',
        'usuario_id',
        'conteudo',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ComunidadePost::class, 'post_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
