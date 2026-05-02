<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Faculdade extends Model
{
    protected $fillable = ['nome', 'slug', 'ordem', 'ativo'];

    protected function casts(): array
    {
        return [
            'ativo' => 'boolean',
            'ordem' => 'integer',
        ];
    }

    public function agrupamentos(): HasMany
    {
        return $this->hasMany(Agrupamento::class)->orderBy('ordem');
    }
}
