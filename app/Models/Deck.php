<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = [
        'usuario_id',
        'materia_id',
        'nome',
        'descricao',
        'compartilhado',
        'compartilhado_em',
        'deck_origem_id',
    ];

    protected function casts(): array
    {
        return [
            'compartilhado' => 'boolean',
            'compartilhado_em' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function origem(): BelongsTo
    {
        return $this->belongsTo(Deck::class, 'deck_origem_id');
    }

    public function cartas(): HasMany
    {
        return $this->hasMany(DeckCarta::class)->orderBy('ordem');
    }
}
