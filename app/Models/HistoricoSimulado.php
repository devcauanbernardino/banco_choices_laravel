<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HistoricoSimulado extends Model
{
    public $timestamps = false;

    protected $table = 'historico_simulados';

    protected $fillable = [
        'usuario_id',
        'materia_id',
        'acertos',
        'total_questoes',
        'detalhes_json',
    ];

    protected function casts(): array
    {
        return [
            'detalhes_json' => 'array',
            'data_realizacao' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
