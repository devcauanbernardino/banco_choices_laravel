<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PomodoroCiclo extends Model
{
    protected $table = 'pomodoro_ciclos';

    protected $fillable = [
        'usuario_id',
        'materia_id',
        'sessao_uid',
        'duracao_minutos',
        'concluido_em',
    ];

    protected function casts(): array
    {
        return [
            'concluido_em' => 'datetime',
            'duracao_minutos' => 'integer',
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
}
