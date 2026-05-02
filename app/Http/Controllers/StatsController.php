<?php

namespace App\Http\Controllers;

use App\Models\HistoricoSimulado;
use App\Support\Branding;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    private const PERIODS = ['7', '30', '90', 'all', 'custom'];
    private const PERIOD_DEFAULT = '90';

    public function index(Request $request)
    {
        $period = $this->resolvePeriod($request->query('period'));
        [$customFrom, $customTo] = $this->resolveCustomRange($request);
        $stats = $this->statsPayloadForUser(Auth::id(), $period, $customFrom, $customTo);
        $stats['period'] = $period;
        $stats['customFrom'] = $customFrom?->format('Y-m-d');
        $stats['customTo'] = $customTo?->format('Y-m-d');

        return view('stats.index', $stats);
    }

    public function exportPdf(Request $request)
    {
        $period = $this->resolvePeriod($request->query('period'));
        [$customFrom, $customTo] = $this->resolveCustomRange($request);
        $user = Auth::user();
        $stats = $this->statsPayloadForUser($user->id, $period, $customFrom, $customTo);
        $stats['userName'] = trim((string) ($user->nome ?? '')) ?: __('stats.pdf_user_fallback');
        $stats['generatedAt'] = Carbon::now()->timezone(config('app.timezone'))->isoFormat('L LT');
        $stats['brandName'] = __('index.page_title');
        $stats['reportYear'] = (int) Carbon::now()->year;
        $stats['logoDataUri'] = $this->logoDataUriForPdf();

        $filename = 'relatorio-estatisticas-'.Carbon::now()->format('Y-m-d').'.pdf';

        return Pdf::loadView('stats.pdf', $stats)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    private function resolvePeriod(?string $raw): string
    {
        $raw = is_string($raw) ? trim($raw) : '';
        return in_array($raw, self::PERIODS, true) ? $raw : self::PERIOD_DEFAULT;
    }

    /**
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    private function resolveCustomRange(Request $request): array
    {
        $parse = function ($raw): ?Carbon {
            if (! is_string($raw) || trim($raw) === '') {
                return null;
            }
            try {
                return Carbon::createFromFormat('Y-m-d', trim($raw)) ?: null;
            } catch (\Throwable $e) {
                return null;
            }
        };

        $from = $parse($request->query('from'));
        $to = $parse($request->query('to'));

        if ($from && $to && $from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [
            $from ? $from->startOfDay() : null,
            $to ? $to->endOfDay() : null,
        ];
    }

    private function applyPeriod($query, string $period, string $column = 'data_realizacao', ?Carbon $customFrom = null, ?Carbon $customTo = null)
    {
        if ($period === 'all') {
            return $query;
        }
        if ($period === 'custom') {
            if ($customFrom) {
                $query->where($column, '>=', $customFrom);
            }
            if ($customTo) {
                $query->where($column, '<=', $customTo);
            }
            return $query;
        }
        $days = (int) $period;
        if ($days <= 0) {
            return $query;
        }
        return $query->where($column, '>=', Carbon::now()->subDays($days)->startOfDay());
    }

    /**
     * @return array<string, mixed>
     */
    private function statsPayloadForUser(?int $uid, string $period = self::PERIOD_DEFAULT, ?Carbon $customFrom = null, ?Carbon $customTo = null): array
    {
        $uid = $uid ?? 0;

        $kpiRow = $this->applyPeriod(
            DB::table('historico_simulados')->where('usuario_id', $uid),
            $period,
            'data_realizacao',
            $customFrom,
            $customTo
        )
            ->selectRaw('SUM(total_questoes) as total, SUM(acertos) as acertos, COUNT(id) as simulados')
            ->first();

        $totalResp = (int) ($kpiRow?->total ?? 0);
        $totalAcertos = (int) ($kpiRow?->acertos ?? 0);
        $totalSimulados = (int) ($kpiRow?->simulados ?? 0);
        $mediaAcertos = $totalResp > 0 ? round(($totalAcertos / $totalResp) * 100, 1) : 0;

        $porMateria = $this->applyPeriod(
            DB::table('historico_simulados as h')
                ->join('materias as m', 'm.id', '=', 'h.materia_id')
                ->where('h.usuario_id', $uid),
            $period,
            'h.data_realizacao',
            $customFrom,
            $customTo
        )
            ->selectRaw('m.nome, SUM(h.acertos) as acertos, SUM(h.total_questoes) as total')
            ->groupBy('h.materia_id', 'm.nome')
            ->orderByRaw('(SUM(h.acertos)/SUM(h.total_questoes)) DESC')
            ->get()
            ->map(fn ($r) => [
                'nome' => $r->nome,
                'porcentagem' => (int) $r->total > 0 ? round(($r->acertos / $r->total) * 100) : 0,
            ])->toArray();

        $melhorMateria = ! empty($porMateria) ? $porMateria[0]['nome'] : 'N/A';

        $evolucaoRows = $this->applyPeriod(
            DB::table('historico_simulados')->where('usuario_id', $uid),
            $period,
            'data_realizacao',
            $customFrom,
            $customTo
        )
            ->selectRaw('DATE(data_realizacao) as data, (SUM(acertos)/SUM(total_questoes))*100 as desempenho')
            ->groupByRaw('DATE(data_realizacao)')
            ->orderBy('data')
            ->limit(10)
            ->get();

        $evolucao = [
            'labels' => $evolucaoRows->map(fn ($r) => Carbon::parse($r->data)->format('d/m'))->values()->all(),
            'data' => $evolucaoRows->map(fn ($r) => round((float) ($r->desempenho ?? 0), 1))->values()->all(),
        ];

        $evolucaoLinhas = [];
        foreach ($evolucao['labels'] as $i => $label) {
            $evolucaoLinhas[] = [
                'data' => $label,
                'pct' => $evolucao['data'][$i] ?? 0,
            ];
        }

        $semanal = $this->applyPeriod(
            DB::table('historico_simulados')->where('usuario_id', $uid),
            $period,
            'data_realizacao',
            $customFrom,
            $customTo
        )
            ->selectRaw('YEARWEEK(data_realizacao, 1) as semana, MIN(DATE(data_realizacao)) as inicio_semana, SUM(total_questoes) as total, SUM(acertos) as acertos')
            ->groupByRaw('YEARWEEK(data_realizacao, 1)')
            ->orderByDesc('semana')
            ->limit(4)
            ->get()
            ->map(fn ($r) => [
                'inicio_semana' => $r->inicio_semana,
                'total' => (int) ($r->total ?? 0),
                'acertos' => (int) ($r->acertos ?? 0),
            ])->all();

        $desempenhoParcialPorMateria = $this->aggregateDesempenhoPorParcial($uid, $period, $customFrom, $customTo);

        return compact(
            'totalResp',
            'totalAcertos',
            'totalSimulados',
            'mediaAcertos',
            'melhorMateria',
            'porMateria',
            'evolucao',
            'evolucaoLinhas',
            'semanal',
            'desempenhoParcialPorMateria'
        );
    }

    /**
     * @return list<array{materia_id:int, materia_nome:string, parciais:list<array{codigo:string, label:string, acertos:int, total:int, pct:float}>}>
     */
    private function aggregateDesempenhoPorParcial(int $uid, string $period, ?Carbon $customFrom, ?Carbon $customTo): array
    {
        $query = HistoricoSimulado::query()->with('materia')->where('usuario_id', $uid);
        $this->applyPeriod($query, $period, 'data_realizacao', $customFrom, $customTo);
        /** @var \Illuminate\Support\Collection<int,HistoricoSimulado> $rows */
        $rows = $query->get(['materia_id', 'detalhes_json']);

        /** @var array<int, array<string, array{acertos:int, total:int}>> $byMid */
        $byMid = [];

        /** @var array<int, string> $nomesPorMid */
        $nomesPorMid = [];

        foreach ($rows as $row) {
            $mid = (int) $row->materia_id;
            if ($mid <= 0) {
                continue;
            }
            if (($nomesPorMid[$mid] ?? '') === '') {
                $nomesPorMid[$mid] = trim((string) ($row->materia?->nome ?? ''));
            }
            $payload = $row->detalhes_json ?? [];
            if (! is_array($payload)) {
                continue;
            }
            $detalhes = $payload['detalhes'] ?? [];
            if (! is_array($detalhes)) {
                continue;
            }

            foreach ($detalhes as $d) {
                if (! is_array($d)) {
                    continue;
                }
                $pRaw = $d['parcial'] ?? null;
                $codigo = $this->normalizeCodigoParcial($pRaw);
                $acertou = ! empty($d['acertou']);
                if (! isset($byMid[$mid])) {
                    $byMid[$mid] = [];
                }
                if (! isset($byMid[$mid][$codigo])) {
                    $byMid[$mid][$codigo] = ['acertos' => 0, 'total' => 0];
                }
                $byMid[$mid][$codigo]['total']++;
                if ($acertou) {
                    $byMid[$mid][$codigo]['acertos']++;
                }
            }
        }

        /** @var list<array{materia_id:int, materia_nome:string, parciais:list<array{codigo:string, label:string, acertos:int, total:int, pct:float}>}> $out */
        $out = [];

        foreach ($byMid as $mid => $buckets) {
            $nome = $nomesPorMid[(int) $mid] ?? '';
            if ($nome === '') {
                $nome = (string) (DB::table('materias')->whereKey($mid)->value('nome') ?? '');
            }

            $keys = array_keys($buckets);
            usort($keys, [$this, 'compareCodigoParcial']);

            $parciais = [];
            foreach ($keys as $k) {
                $tot = (int) ($buckets[$k]['total'] ?? 0);
                if ($tot <= 0) {
                    continue;
                }
                $ac = (int) ($buckets[$k]['acertos'] ?? 0);
                $parciais[] = [
                    'codigo' => $k,
                    'label' => $this->labelParcialCodigo((string) $k),
                    'acertos' => $ac,
                    'total' => $tot,
                    'pct' => round(($ac / $tot) * 100, 1),
                ];
            }

            $onlyUnknown = count($parciais) === 1 && (($parciais[0]['codigo'] ?? '') === '_sem');
            if ($parciais === [] || $onlyUnknown) {
                continue;
            }

            $out[] = [
                'materia_id' => $mid,
                'materia_nome' => $nome !== '' ? $nome : '—',
                'parciais' => $parciais,
            ];
        }

        usort($out, fn ($a, $b) => strcmp((string) $a['materia_nome'], (string) $b['materia_nome']));

        return $out;
    }

    /** @param  mixed  $pRaw */
    private function normalizeCodigoParcial($pRaw): string
    {
        if ($pRaw === null || $pRaw === '') {
            return '_sem';
        }
        $s = strtolower(trim((string) $pRaw));

        return $s === '' ? '_sem' : $s;
    }

    /** @internal  usort callable */
    private function compareCodigoParcial(string $a, string $b): int
    {
        $rank = static function (string $x): int {
            if ($x === 'final') {
                return 40;
            }
            if ($x === '1' || $x === '2' || $x === '3') {
                return 10 + (int) $x;
            }
            if ($x === '_sem') {
                return 99;
            }

            return 50;
        };
        $ra = $rank($a);
        $rb = $rank($b);
        if ($ra !== $rb) {
            return $ra <=> $rb;
        }

        return strcmp($a, $b);
    }

    private function labelParcialCodigo(string $codigo): string
    {
        return match ($codigo) {
            '1' => __('bank.parc.label_1'),
            '2' => __('bank.parc.label_2'),
            '3' => __('bank.parc.label_3'),
            'final' => __('bank.parc.final'),
            '_sem' => __('stats.parc_sem'),
            default => __('stats.parc_other', ['code' => $codigo]),
        };
    }

    private function logoDataUriForPdf(): ?string
    {
        // Dompdf incorpora PNG/JPEG (e alguns fluxos de imagem) via GD — sem ext-gd o PDF ainda gera, só sem logo.
        if (! extension_loaded('gd') || ! function_exists('imagecreatefrompng')) {
            return null;
        }

        $rel = Branding::logoPublicPath();
        $full = public_path($rel);
        if (! is_readable($full)) {
            return null;
        }

        $ext = strtolower(pathinfo($full, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => null,
        };

        if ($mime === null) {
            return null;
        }

        $raw = @file_get_contents($full);
        if ($raw === false || $raw === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($raw);
    }
}
