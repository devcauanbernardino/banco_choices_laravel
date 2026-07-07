<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComunidadeComentario extends Model
{
    protected $table = 'comunidade_comentarios';

    protected $fillable = [
        'post_id',
        'parent_id',
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

    public function parentComentario(): BelongsTo
    {
        return $this->belongsTo(ComunidadeComentario::class, 'parent_id');
    }

    public function respostas(): HasMany
    {
        return $this->hasMany(ComunidadeComentario::class, 'parent_id')->orderBy('created_at');
    }

    public function curtidas(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comunidade_comentario_curtidas', 'comentario_id', 'usuario_id')->withTimestamps();
    }
}
