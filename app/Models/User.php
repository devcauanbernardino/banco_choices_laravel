<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    public $timestamps = false;

    protected $fillable = [
        'nome',
        'email',
        'senha',
        'codigo_cupom',
        'saldo_credito',
        'referido_por_codigo',
    ];

    protected $hidden = [
        'senha',
    ];

    protected function casts(): array
    {
        return [
            'saldo_credito' => 'decimal:2',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->senha;
    }

    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'usuarios_materias', 'usuario_id', 'materia_id')
            ->distinct();
    }

    /**
     * Matérias para listagem (evita repetir por linhas duplicadas na pivot ou nomes iguais em IDs diferentes).
     */
    public function materiasUnicas(): Collection
    {
        return $this->materias()
            ->orderBy('materias.nome')
            ->get()
            ->unique('id')
            ->unique('nome')
            ->values();
    }

    public function historicos(): HasMany
    {
        return $this->hasMany(HistoricoSimulado::class, 'usuario_id');
    }

    public function creditoMovimentos(): HasMany
    {
        return $this->hasMany(CreditoMovimento::class, 'user_id');
    }

    public function possuiMateria(int $materiaId): bool
    {
        return $this->materias()->where('materias.id', $materiaId)->exists();
    }

    public function garantirMaterias(array $materiaIds): void
    {
        $this->materias()->syncWithoutDetaching($materiaIds);
    }

    /**
     * Último plano em pedidos_itens cujo pedido corresponde ao e-mail do utilizador.
     */
    public function buscarUltimoPlanoIdParaUsuarioId(int $id): ?string
    {
        if ($id <= 0) {
            return null;
        }

        $email = self::query()->where('id', $id)->value('email');
        if ($email === null || $email === '') {
            return null;
        }

        $row = DB::table('pedidos_itens as pi')
            ->join('pedidos as p', 'p.id', '=', 'pi.pedido_id')
            ->whereRaw('TRIM(LOWER(p.email)) = TRIM(LOWER(?))', [$email])
            ->orderByDesc('p.id')
            ->orderByDesc('pi.id')
            ->select('pi.plano_id')
            ->first();

        if ($row === null) {
            return null;
        }

        $planoId = trim((string) ($row->plano_id ?? ''));

        return $planoId !== '' ? $planoId : null;
    }
}
