<?php

namespace App\Http\Controllers;

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

        return compact(
            'totalResp',
            'totalAcertos',
            'totalSimulados',
            'mediaAcertos',
            'melhorMateria',
            'porMateria',
            'evolucao',
            'evolucaoLinhas',
            'semanal'
        );
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
