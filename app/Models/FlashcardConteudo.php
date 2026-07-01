<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FlashcardConteudo extends Model
{
    protected $table = 'flashcard_conteudo';

    protected $fillable = ['questao_id', 'idioma', 'frente', 'verso'];

    public function questao(): BelongsTo
    {
        return $this->belongsTo(Questao::class, 'questao_id');
    }
}
