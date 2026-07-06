<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ComunidadePostImagem extends Model
{
    protected $table = 'comunidade_post_imagens';

    protected $fillable = [
        'post_id',
        'caminho',
        'ordem',
    ];

    protected $appends = ['url'];

    public function post(): BelongsTo
    {
        return $this->belongsTo(ComunidadePost::class, 'post_id');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->caminho);
    }
}
