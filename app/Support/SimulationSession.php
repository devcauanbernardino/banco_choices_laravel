<?php

namespace App\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Estado do simulado em andamento, persistido no banco por usuário (não na
 * sessão PHP) para sobreviver a logout, fechar o navegador ou trocar de
 * dispositivo — o usuário retoma exatamente de onde parou ao voltar.
 */
class SimulationSession
{
    private ?array $cache = null;

    private bool $loaded = false;

    public function init(array $data): void
    {
        $this->cache = $data;
        $this->loaded = true;
        $this->persist();
    }

    public function isActive(): bool
    {
        return $this->load() !== null;
    }

    public function get(string $key): mixed
    {
        $data = $this->load();

        return $data === null ? null : Arr::get($data, $key);
    }

    public function set(string $key, mixed $value): void
    {
        $data = $this->load() ?? [];
        Arr::set($data, $key, $value);
        $this->cache = $data;
        $this->persist();
    }

    public function all(): ?array
    {
        return $this->load();
    }

    public function clear(): void
    {
        $userId = Auth::id();
        if ($userId !== null) {
            DB::table('simulados_em_andamento')->where('usuario_id', $userId)->delete();
        }
        $this->cache = null;
        $this->loaded = true;
    }

    /**
     * Resumo leve do simulado em andamento do usuário atual, para exibir um
     * aviso de "continuar de onde parou" fora da própria tela do simulado.
     *
     * @return array{materia_nome: string, atual: int, total: int, modo: string}|null
     */
    public static function resumoAtual(): ?array
    {
        $userId = Auth::id();
        if ($userId === null) {
            return null;
        }

        $row = DB::table('simulados_em_andamento')->where('usuario_id', $userId)->first();
        if ($row === null) {
            return null;
        }

        $dados = json_decode((string) $row->dados, true);
        if (! is_array($dados)) {
            return null;
        }

        $total = count((array) ($dados['questoes'] ?? []));
        if ($total === 0) {
            return null;
        }

        return [
            'materia_nome' => (string) ($dados['materia_nome'] ?? ''),
            'atual' => (int) ($dados['atual'] ?? 0),
            'total' => $total,
            'modo' => (string) ($dados['modo'] ?? 'estudo'),
        ];
    }

    private function load(): ?array
    {
        if ($this->loaded) {
            return $this->cache;
        }

        $this->loaded = true;

        $userId = Auth::id();
        if ($userId === null) {
            return $this->cache = null;
        }

        $row = DB::table('simulados_em_andamento')->where('usuario_id', $userId)->first();
        if ($row === null) {
            return $this->cache = null;
        }

        $dados = json_decode((string) $row->dados, true);

        return $this->cache = is_array($dados) ? $dados : null;
    }

    private function persist(): void
    {
        $userId = Auth::id();
        if ($userId === null) {
            return;
        }

        $json = json_encode($this->cache);

        $updated = DB::table('simulados_em_andamento')
            ->where('usuario_id', $userId)
            ->update(['dados' => $json, 'updated_at' => now()]);

        if ($updated === 0) {
            DB::table('simulados_em_andamento')->insert([
                'usuario_id' => $userId,
                'dados' => $json,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
