<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditoMovimento extends Model
{
    protected $table = 'credito_movimentos';

    protected $fillable = [
        'user_id',
        'tipo',
        'valor',
        'referencia_tipo',
        'referencia_id',
        'descricao',
    ];

    protected function casts(): array
    {
        return [
            'valor' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
