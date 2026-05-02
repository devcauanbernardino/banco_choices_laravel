<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'email',
        'nome',
        'valor_total',
        'status',
        'stripe_payment_id',
        'codigo_cupom_usado',
    ];

    protected function casts(): array
    {
        return [
            'valor_total' => 'decimal:2',
            'data_criacao' => 'datetime',
        ];
    }

    public function itens(): HasMany
    {
        return $this->hasMany(PedidoItem::class, 'pedido_id');
    }
}
