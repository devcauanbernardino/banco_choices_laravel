<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ComunidadePost extends Model
{
    protected $table = 'comunidade_posts';

    protected $fillable = [
        'usuario_id',
        'titulo',
        'materia_id',
        'conteudo',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(ComunidadeComentario::class, 'post_id')->whereNull('parent_id')->orderBy('created_at');
    }

    public function todosComentarios(): HasMany
    {
        return $this->hasMany(ComunidadeComentario::class, 'post_id');
    }

    public function imagens(): HasMany
    {
        return $this->hasMany(ComunidadePostImagem::class, 'post_id')->orderBy('ordem');
    }

    public function curtidas(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'comunidade_post_curtidas', 'post_id', 'usuario_id')->withTimestamps();
    }
}
