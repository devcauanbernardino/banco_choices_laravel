<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = [
        'usuario_id',
        'nome',
        'descricao',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function cartas(): HasMany
    {
        return $this->hasMany(DeckCarta::class)->orderBy('ordem');
    }
}
