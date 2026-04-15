<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Materia extends Model
{
    public $timestamps = false;

    protected $fillable = ['nome'];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'usuarios_materias', 'materia_id', 'usuario_id')
                    ->withoutTimestamps();
    }
}
