<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComunidadePost extends Model
{
    protected $table = 'comunidade_posts';

    protected $fillable = [
        'usuario_id',
        'conteudo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ComunidadeComentario::class, 'post_id')->orderBy('created_at');
    }
}
