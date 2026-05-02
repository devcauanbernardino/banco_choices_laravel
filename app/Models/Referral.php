<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_user_id',
        'referido_user_id',
        'referido_email',
        'codigo_usado',
        'pedido_id',
        'valor_credito_gerado',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'valor_credito_gerado' => 'decimal:2',
        ];
    }

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    public function referido(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referido_user_id');
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}
