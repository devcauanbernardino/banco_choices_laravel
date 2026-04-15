<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    public $timestamps = false;

    protected $fillable = [
        'nome',
        'email',
        'senha',
    ];

    protected $hidden = [
        'senha',
    ];

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'usuarios_materias', 'usuario_id', 'materia_id')
                    ->withoutTimestamps();
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoSimulado::class, 'usuario_id');
    }

    public function possuiMateria(int $materiaId): bool
    {
        return $this->materias()->where('materias.id', $materiaId)->exists();
    }

    public function garantirMaterias(array $materiaIds): void
    {
        $this->materias()->syncWithoutDetaching($materiaIds);
    }
}
