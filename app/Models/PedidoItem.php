<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PedidoItem extends Model
{
    public $timestamps = false;

    protected $table = 'pedidos_itens';

    protected $fillable = [
        'pedido_id',
        'materia_id',
        'plano_id',
        'preco',
        'data_expiracao',
    ];

    protected function casts(): array
    {
        return [
            'preco' => 'decimal:2',
            'data_expiracao' => 'date',
        ];
    }

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia_id');
    }
}
