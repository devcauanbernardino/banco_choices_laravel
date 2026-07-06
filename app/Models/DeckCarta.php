<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeckCarta extends Model
{
    protected $table = 'deck_cartas';

    protected $fillable = [
        'deck_id',
        'frente',
        'verso',
        'ordem',
    ];

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function progressos(): HasMany
    {
        return $this->hasMany(DeckProgresso::class);
    }
}
