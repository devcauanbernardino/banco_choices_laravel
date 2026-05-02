<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'slug', 'agrupamento_id', 'ordem'];

    protected function casts(): array
    {
        return [
            'agrupamento_id' => 'integer',
            'ordem' => 'integer',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_materias', 'materia_id', 'usuario_id')
                    ->withoutTimestamps();
    }

    public function agrupamento(): BelongsTo
    {
        return $this->belongsTo(Agrupamento::class);
    }

    public function catedras(): HasMany
    {
        return $this->hasMany(Catedra::class)->orderBy('ordem');
    }

    public function questoes(): HasMany
    {
        return $this->hasMany(Questao::class);
    }
}
