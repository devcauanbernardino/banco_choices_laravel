<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoAttempt extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::creating(function (DemoAttempt $row): void {
            if ($row->created_at === null) {
                $row->created_at = now();
            }
        });
    }

    protected $fillable = [
        'session_uuid',
        'ip',
        'materia_id',
        'questao_id',
        'acertou',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'acertou' => 'boolean',
        ];
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class, 'questao_id');
    }
}
