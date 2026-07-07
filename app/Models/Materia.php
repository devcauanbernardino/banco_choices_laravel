<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materia extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome', 'cor', 'slug', 'agrupamento_id', 'ordem'];

    /**
     * Paleta usada quando a matéria não tem uma cor definida manualmente.
     * Escolhida por hash do id para ser estável entre requisições.
     */
    private const PALETA_PADRAO = [
        '#2F4B8F', '#1F7A6C', '#B5541A', '#6B4C9A', '#B5305A', '#5C6B3A', '#2E7D8A', '#8A4A2E',
    ];

    public function corExibicao(): string
    {
        if (!empty($this->cor)) {
            return $this->cor;
        }

        return self::PALETA_PADRAO[$this->id % count(self::PALETA_PADRAO)];
    }

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

    public function posts(): HasMany
    {
        return $this->hasMany(ComunidadePost::class, 'materia_id');
    }
}
