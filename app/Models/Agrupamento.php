<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agrupamento extends Model
{
    protected $fillable = ['faculdade_id', 'nome', 'slug', 'ordem', 'tipo'];

    protected function casts(): array
    {
        return [
            'ordem' => 'integer',
        ];
    }

    public function faculdade(): BelongsTo
    {
        return $this->belongsTo(Faculdade::class);
    }

    public function materias(): HasMany
    {
        return $this->hasMany(Materia::class)->orderBy('ordem');
    }
}
