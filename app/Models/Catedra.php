<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Catedra extends Model
{
    protected $fillable = ['materia_id', 'nome', 'slug', 'ordem'];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function questoes(): HasMany
    {
        return $this->hasMany(Questao::class);
    }
}
