@extends('layouts.app')

@section('title', __('stats.page_title'))
@section('mobile_title', __('stats.mobile_title'))

@section('topbar_title', __('stats.mobile_title'))

@section('content')
    {{-- Header --}}
    <div class="bc-page-header mb-4">
        <div>
            <h5 class="mb-0 fw-bold">{{ __('stats.heading') }}</h5>
            <small class="text-muted">{{ __('stats.subhead') }}</small>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card d-flex align-items-center gap-3">
                <div class="bc-icon-box bg-primary bg-opacity-10 text-primary">
                    <span class="material-icons">quiz</span>
                </div>
                <div>
                    <p class="bc-stat-label mb-1">{{ __('stats.kpi_total') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalResp, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card d-flex align-items-center gap-3">
                <div class="bc-icon-box bg-success bg-opacity-10 text-success">
                    <span class="material-icons">trending_up</span>
                </div>
                <div>
                    <p class="bc-stat-label mb-1">{{ __('stats.kpi_avg') }}</p>
                    <h4 class="fw-bold text-success mb-0">{{ $mediaAcertos }}%</h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card d-flex align-items-center gap-3">
                <div class="bc-icon-box bg-warning bg-opacity-10 text-warning">
                    <span class="material-icons">stars</span>
                </div>
                <div class="min-w-0 flex-grow-1">
                    <p class="bc-stat-label mb-1">{{ __('stats.kpi_best') }}</p>
                    <h5 class="fw-bold mb-0 text-break">{{ $melhorMateria }}</h5>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-3">
            <div class="bc-stat-card d-flex align-items-center gap-3">
                <div class="bc-icon-box bg-info bg-opacity-10 text-info">
                    <span class="material-icons">history</span>
                </div>
                <div>
                    <p class="bc-stat-label mb-1">{{ __('stats.kpi_sims') }}</p>
                    <h4 class="fw-bold mb-0">{{ number_format($totalSimulados, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart & Subject bars --}}
    <div class="row g-4 mb-4">
        {{-- Line chart --}}
        <div class="col-lg-8">
            <div class="bc-card p-4 h-100">
                <h6 class="fw-bold mb-4">{{ __('stats.chart_title') }}</h6>
                <div class="bc-chart-wrap">
                    <canvas id="chartEvolucao"></canvas>
                </div>
            </div>
        </div>

        {{-- Subject bars --}}
        <div class="col-lg-4">
            <div class="bc-card p-4 h-100">
                <h6 class="fw-bold mb-4">{{ __('stats.bar_title') }}</h6>

                @forelse (collect($porMateria)->take(5) as $m)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-1">
                            <span class="small fw-bold text-secondary">{{ ucfirst($m['nome']) }}</span>
                            <span class="small fw-bold text-primary">{{ $m['porcentagem'] }}%</span>
                        </div>
                        <div class="bc-progress">
                            <div class="bc-progress-bar" style="width: {{ $m['porcentagem'] }}%"></div>
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-5">{{ __('stats.no_data') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Weekly summary table --}}
    <div class="bc-card overflow-hidden">
        <div class="bc-card-header">
            <h6 class="fw-bold mb-0">{{ __('stats.week_title') }}</h6>
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
                                    <div class="bc-progress flex-grow-1" style="width: 100px;">
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
