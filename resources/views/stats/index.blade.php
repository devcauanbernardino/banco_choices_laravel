@extends('layouts.app')

@section('title', __('stats.page_title'))
@section('mobile_title', __('stats.mobile_title'))

@section('topbar_title', __('stats.mobile_title'))

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="{{ asset('assets/css/shared-select.css') }}?v={{ @filemtime(public_path('assets/css/shared-select.css')) }}">
<link rel="stylesheet" href="{{ asset('assets/css/page-estatisticas-mock.css') }}?v={{ @filemtime(public_path('assets/css/page-estatisticas-mock.css')) }}">
@endpush

@section('content')
    @php
        $currentPeriod = $period ?? '90';
        $periodOptions = [
            '7' => __('stats.period_7'),
            '30' => __('stats.period_30'),
            '90' => __('stats.period_90'),
            'all' => __('stats.period_all'),
            'custom' => __('stats.period_custom'),
        ];
        $currentPeriodLabel = $periodOptions[$currentPeriod] ?? $periodOptions['90'];
        $customFromValue = $customFrom ?? '';
        $customToValue = $customTo ?? '';
        $exportParams = [];
        if ($currentPeriod !== '90') {
            $exportParams['period'] = $currentPeriod;
        }
        if ($currentPeriod === 'custom') {
            if ($customFromValue) $exportParams['from'] = $customFromValue;
            if ($customToValue) $exportParams['to'] = $customToValue;
        }
        $exportUrl = route('stats.export-pdf', $exportParams);
    @endphp
    <div class="bc-mock-stats bc-mock-page-shell">
        <header class="bc-mock-stats__header">
            <div class="bc-mock-stats__header-text">
                <h1 class="bc-mock-stats__title">{{ __('stats.heading') }}</h1>
                <p class="bc-mock-stats__sub">{{ __('stats.subhead') }}</p>
            </div>
        </header>

        <div class="bc-mock-stats__filter-panel">
            <form action="{{ route('stats') }}" method="GET" class="bc-mock-stats__period-form" id="bc-stats-period-form">
                <div class="bc-mock-stats__filter-body">
                    <div class="bc-mock-stats__filter-toolbar {{ $currentPeriod === 'custom' ? 'is-custom' : 'is-not-custom' }}" id="bc-stats-filter-toolbar" role="group" aria-label="{{ __('stats.period_aria') }}">
                        <div class="bc-mock-stats__filter-cluster bc-mock-stats__filter-cluster--period">
                            <span class="bc-mock-stats__filter-heading" id="bc-stats-period-lbl">{{ __('stats.period_label') }}</span>
                            <div class="bc-mock-stats__field-control">
                                <select id="bc-stats-period"
                                        name="period"
                                        class="bc-styled-select bc-mock-stats__select"
                                        aria-labelledby="bc-stats-period-lbl">
                                    @foreach ($periodOptions as $value => $label)
                                        <option value="{{ $value }}" {{ $currentPeriod === $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="bc-mock-stats__filter-cluster bc-mock-stats__filter-cluster--interval bc-mock-stats__filter-group--range" id="bc-stats-range" data-active="{{ $currentPeriod === 'custom' ? '1' : '0' }}" role="group" aria-labelledby="bc-stats-interval-lbl">
                            <span class="bc-mock-stats__filter-heading" id="bc-stats-interval-lbl">{{ __('stats.filter_range_label') }}</span>
                            <div class="bc-mock-stats__interval-controls">
                                <div class="bc-mock-stats__interval-field">
                                    <label class="bc-mock-stats__interval-field-lbl" for="bc-stats-from">{{ __('stats.range_from') }}</label>
                                    <input type="date" name="from" value="{{ $customFromValue }}" class="bc-mock-stats__range-input" id="bc-stats-from" autocomplete="off" @if($currentPeriod !== 'custom') disabled @endif>
                                </div>
                                <div class="bc-mock-stats__interval-field">
                                    <label class="bc-mock-stats__interval-field-lbl" for="bc-stats-to">{{ __('stats.range_to') }}</label>
                                    <input type="date" name="to" value="{{ $customToValue }}" class="bc-mock-stats__range-input" id="bc-stats-to" autocomplete="off" @if($currentPeriod !== 'custom') disabled @endif>
                                </div>
                                <div class="bc-mock-stats__interval-actions">
                                    <button type="submit" class="bc-mock-stats__range-btn" @if($currentPeriod !== 'custom') disabled @endif>{{ __('stats.range_apply') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <noscript>
                    <button type="submit" class="bc-mock-stats__btn-ghost">{{ __('simulados.filter_btn') }}</button>
                </noscript>
            </form>
            <span class="bc-mock-stats__filter-sep" aria-hidden="true"></span>
            <a href="{{ $exportUrl }}" class="bc-mock-stats__btn-primary bc-mock-stats__btn-export text-decoration-none d-inline-flex align-items-center justify-content-center gap-2"
               title="{{ __('stats.export_pdf_title') }}" aria-label="{{ __('stats.export_pdf_title') }}">
                <span class="material-symbols-outlined" aria-hidden="true">picture_as_pdf</span>
                <span>{{ __('stats.export_report') }}</span>
            </a>
        </div>

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

        @if(!empty($desempenhoParcialPorMateria))
        <section class="bc-mock-stats__parc-panel px-3 px-md-4 py-4 mb-3 rounded-4 border shadow-sm bg-body-tertiary/30 border-opacity-50" aria-label="{{ __('stats.partial_section_title') }}">
            <h3 class="h5 fw-bold mb-1">{{ __('stats.partial_section_title') }}</h3>
            <p class="small text-secondary mb-4">{{ __('stats.partial_section_help') }}</p>
            <div class="row g-4">
                @foreach ($desempenhoParcialPorMateria as $bloque)
                    <div class="col-12">
                        <h4 class="h6 fw-semibold mb-3 text-secondary">{{ ucfirst((string) $bloque['materia_nome']) }}</h4>
                        <div class="row g-3">
                            @foreach ($bloque['parciais'] as $pRow)
                                <div class="col-md-6 col-xl-4">
                                    <div class="p-3 rounded-3 border bg-body h-100">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="small fw-semibold">{{ $pRow['label'] }}</span>
                                            <span class="small fw-bold text-primary">{{ $pRow['pct'] }}%</span>
                                        </div>
                                        <div class="bc-progress mb-2">
                                            <div class="bc-progress-bar" style="width: {{ min(100, max(0, (float) $pRow['pct'])) }}%;"></div>
                                        </div>
                                        <p class="small text-muted mb-0">
                                            {{ __('stats.partial_hits_vs_total', ['acertos' => $pRow['acertos'], 'total' => $pRow['total']]) }}
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        @endif

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
<script src="{{ asset('assets/js/styled-select.js') }}?v={{ @filemtime(public_path('assets/js/styled-select.js')) }}" defer></script>
<script>
    (function () {
        var form = document.getElementById('bc-stats-period-form');
        var range = document.getElementById('bc-stats-range');
        var fromInput = document.getElementById('bc-stats-from');
        var toInput = document.getElementById('bc-stats-to');
        if (!form || !range) return;

        var toolbar = document.getElementById('bc-stats-filter-toolbar');
        var selectEl = document.getElementById('bc-stats-period');

        function setToolbarMode(isCustom) {
            if (!toolbar) return;
            toolbar.classList.toggle('is-custom', isCustom);
            toolbar.classList.toggle('is-not-custom', !isCustom);
            range.setAttribute('data-active', isCustom ? '1' : '0');
        }

        function maybeSubmitCustom() {
            if (fromInput && toInput && fromInput.value && toInput.value) {
                form.submit();
            }
        }

        var applyBtn = form.querySelector('.bc-mock-stats__range-btn');

        function onPeriodChange(select) {
            var isCustom = select.value === 'custom';
            setToolbarMode(isCustom);
            if (fromInput) {
                if (isCustom) fromInput.removeAttribute('disabled');
                else { fromInput.setAttribute('disabled', 'disabled'); fromInput.value = ''; }
            }
            if (toInput) {
                if (isCustom) toInput.removeAttribute('disabled');
                else { toInput.setAttribute('disabled', 'disabled'); toInput.value = ''; }
            }
            if (applyBtn) {
                if (isCustom) applyBtn.removeAttribute('disabled');
                else applyBtn.setAttribute('disabled', 'disabled');
            }
            if (isCustom) {
                if (fromInput && toInput && fromInput.value && toInput.value) {
                    form.submit();
                } else {
                    setTimeout(function () { if (fromInput && toInput) (fromInput.value ? toInput : fromInput).focus(); }, 50);
                }
                return;
            }
            form.submit();
        }

        window.bcStatsOnPeriodChange = onPeriodChange;

        function bind() {
            if (selectEl) {
                selectEl.addEventListener('change', function () {
                    onPeriodChange(this);
                });
            }
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bind);
        } else {
            bind();
        }

        fromInput && fromInput.addEventListener('change', maybeSubmitCustom);
        toInput && toInput.addEventListener('change', maybeSubmitCustom);
    })();
</script>
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
