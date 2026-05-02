<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Questao extends Model
{
    protected $table = 'questoes';

    protected $fillable = [
        'materia_id',
        'catedra_id',
        'overlay_key',
        'parcial',
        'tema',
        'is_demo',
    ];

    protected function casts(): array
    {
        return [
            'overlay_key' => 'integer',
            'is_demo' => 'boolean',
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function catedra(): BelongsTo
    {
        return $this->belongsTo(Catedra::class);
    }
}
