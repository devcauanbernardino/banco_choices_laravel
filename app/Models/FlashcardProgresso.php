<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardProgresso extends Model
{
    protected $table = 'flashcard_progresso';

    protected $fillable = [
        'usuario_id',
        'materia_id',
        'overlay_key',
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

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
