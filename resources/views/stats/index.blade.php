@extends('layouts.app')

@section('title', __('stats.page_title'))
@section('mobile_title', __('stats.mobile_title'))

@section('topbar_title', __('stats.mobile_title'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/page-estatisticas-mock.css') }}">
@endpush

@section('content')
    <div class="bc-mock-stats py-4 px-3 px-md-4">
        <header class="bc-mock-stats__header">
            <div>
                <h1 class="bc-mock-stats__title">{{ __('stats.heading') }}</h1>
                <p class="bc-mock-stats__sub">{{ __('stats.subhead') }}</p>
            </div>
            <div class="bc-mock-stats__actions">
                <button type="button" class="bc-mock-stats__btn-ghost" disabled aria-disabled="true">
                    <span class="material-symbols-outlined" aria-hidden="true">calendar_today</span>
                    {{ __('stats.period_short') }}
                </button>
                <a href="{{ route('stats.export-pdf') }}" class="bc-mock-stats__btn-primary text-decoration-none d-inline-flex align-items-center justify-content-center gap-2"
                   title="{{ __('stats.export_pdf_title') }}" aria-label="{{ __('stats.export_pdf_title') }}">
                    <span class="material-symbols-outlined" aria-hidden="true">picture_as_pdf</span>
                    {{ __('stats.export_report') }}
                </a>
            </div>
        </header>

        <section class="bc-mock-stats__kpi-grid" aria-label="{{ __('stats.heading') }}">
            <article class="bc-mock-stats__kpi">
                <div class="bc-mock-stats__kpi-top">
                    <span class="material-symbols-outlined bc-mock-stats__kpi-ico bc-mock-stats__kpi-ico--primary" aria-hidden="true">quiz</span>
                </div>
                <p class="bc-mock-stats__kpi-label">{{ __('stats.kpi_total') }}</p>
                <p class="bc-mock-stats__kpi-value">{{ number_format($totalResp, 0, ',', '.') }}</p>
            </article>
            <article class="bc-mock-stats__kpi">
                <div class="bc-mock-stats__kpi-top">
                    <span class="material-symbols-outlined bc-mock-stats__kpi-ico bc-mock-stats__kpi-ico--success" aria-hidden="true">target</span>
                </div>
                <p class="bc-mock-stats__kpi-label">{{ __('stats.kpi_avg') }}</p>
                <p class="bc-mock-stats__kpi-value">{{ $mediaAcertos }}%</p>
            </article>
            <article class="bc-mock-stats__kpi">
                <div class="bc-mock-stats__kpi-top">
                    <span class="material-symbols-outlined bc-mock-stats__kpi-ico bc-mock-stats__kpi-ico--amber" aria-hidden="true">history_edu</span>
                </div>
                <p class="bc-mock-stats__kpi-label">{{ __('stats.kpi_sims') }}</p>
                <p class="bc-mock-stats__kpi-value">{{ number_format($totalSimulados, 0, ',', '.') }}</p>
            </article>
            <article class="bc-mock-stats__kpi">
                <div class="bc-mock-stats__kpi-top">
                    <span class="material-symbols-outlined bc-mock-stats__kpi-ico bc-mock-stats__kpi-ico--info" aria-hidden="true">rewarded_ads</span>
                </div>
                <p class="bc-mock-stats__kpi-label">{{ __('stats.kpi_best') }}</p>
                <p class="bc-mock-stats__kpi-value text-break fs-5">{{ $melhorMateria }}</p>
            </article>
        </section>

        <div class="bc-mock-stats__charts">
            <section class="bc-mock-stats__chart-panel">
                <h3>{{ __('stats.chart_title') }}</h3>
                <div class="bc-mock-stats__chart-inner">
                    <canvas id="chartEvolucao"></canvas>
                </div>
            </section>
            <section class="bc-mock-stats__chart-panel">
                <h3>{{ __('stats.bar_title') }}</h3>
                @forelse (collect($porMateria)->take(5) as $m)
                    <div class="bc-mock-stats__bar-block">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-secondary">{{ ucfirst($m['nome']) }}</span>
                            <span class="small fw-bold text-primary">{{ $m['porcentagem'] }}%</span>
                        </div>
                        <div class="bc-progress">
                            <div class="bc-progress-bar" style="width: {{ $m['porcentagem'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-5 mb-0">{{ __('stats.no_data') }}</p>
                @endforelse
            </section>
        </div>

        <section class="bc-mock-stats__table-panel overflow-hidden">
            <div class="bc-mock-stats__table-head">
                <h3>{{ __('stats.week_title') }}</h3>
            </div>
            <div class="table-responsive">
                <table class="bc-table w-100 mb-0">
                    <thead>
                        <tr>
                            <th>{{ __('stats.th_week_start') }}</th>
                            <th>{{ __('stats.th_questions') }}</th>
                            <th>{{ __('stats.th_hits') }}</th>
                            <th>{{ __('stats.th_performance') }}</th>
                            <th class="text-end">{{ __('stats.th_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($semanal as $sem)
                            @php
                                $aprov = $sem['total'] > 0 ? round(($sem['acertos'] / $sem['total']) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="fw-bold">{{ \Carbon\Carbon::parse($sem['inicio_semana'])->format('d/m/Y') }}</td>
                                <td>{{ (int) $sem['total'] }}</td>
                                <td>{{ (int) $sem['acertos'] }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bc-progress bc-stats-progress-inline">
                                            <div class="bc-progress-bar" style="width: {{ $aprov }}%"></div>
                                        </div>
                                        <small class="fw-bold">{{ $aprov }}%</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-{{ $aprov >= 70 ? 'success' : 'warning' }} rounded-pill">
                                        {{ $aprov >= 70 ? __('stats.badge_goal') : __('stats.badge_evolving') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const tickColor = isDark ? '#9ca3af' : '#6b7280';
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const ctx = document.getElementById('chartEvolucao').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($evolucao['labels'] ?? []),
                datasets: [{
                    label: @json(__('stats.chart_dataset')),
                    data: @json($evolucao['data'] ?? []),
                    borderColor: '#a855f7',
                    backgroundColor: isDark ? 'rgba(168, 85, 247, 0.12)' : 'rgba(106, 3, 146, 0.08)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: '#c084fc',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { color: tickColor },
                        grid: isDark ? { color: gridColor } : { display: false }
                    },
                    x: {
                        ticks: { color: tickColor },
                        grid: isDark ? { color: gridColor } : { display: false }
                    }
                }
            }
        });
    })();
</script>
@endpush
