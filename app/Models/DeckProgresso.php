<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeckProgresso extends Model
{
    protected $table = 'deck_progresso';

    protected $fillable = [
        'usuario_id',
        'deck_carta_id',
        'fator_facilidade',
        'intervalo_dias',
        'repeticoes',
        'proxima_revisao_em',
        'ultima_revisao_em',
        'total_revisoes',
    ];

    protected function casts(): array
    {
        return [
            'fator_facilidade' => 'decimal:2',
            'intervalo_dias' => 'integer',
            'repeticoes' => 'integer',
            'total_revisoes' => 'integer',
            'proxima_revisao_em' => 'datetime',
            'ultima_revisao_em' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function carta(): BelongsTo
    {
        return $this->belongsTo(DeckCarta::class, 'deck_carta_id');
    }
}
