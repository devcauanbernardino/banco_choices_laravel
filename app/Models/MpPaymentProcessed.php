<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MpPaymentProcessed extends Model
{
    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'mp_payment_processed';
    protected $primaryKey = 'mp_payment_id';
    protected $keyType = 'int';

    protected $fillable = [
        'mp_payment_id',
        'created_at',
        'payment_status',
        'external_reference',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }
}
