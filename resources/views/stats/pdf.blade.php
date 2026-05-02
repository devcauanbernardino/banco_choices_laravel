<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <title>{{ __('stats.pdf_doc_title') }} — {{ $brandName }}</title>
    <style>
        @page { margin: 14mm 12mm 22mm 12mm; }
        * { box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            color: #1f2937;
            line-height: 1.45;
            margin: 0;
            padding: 0;
        }
        .pdf-footer {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            height: 16mm;
            border-top: 2px solid #6a0392;
            padding-top: 3mm;
            font-size: 8px;
            color: #4b5563;
            text-align: center;
        }
        .pdf-footer strong { color: #6a0392; font-size: 9px; }
        .pdf-header {
            width: 100%;
            background-color: #490066;
            color: #ffffff;
            border-radius: 4px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .pdf-header-inner { padding: 12px 14px; }
        .pdf-header-brand {
            text-align: center;
            margin: 0 0 8px 0;
        }
        .pdf-logo--hero {
            height: 48px;
            width: auto;
            max-width: 280px;
            display: block;
            margin: 0 auto;
        }
        .pdf-brand-name {
            font-size: 15px;
            font-weight: bold;
            letter-spacing: -0.02em;
            margin: 0 0 8px 0;
            color: #ffffff;
            text-align: center;
        }
        .pdf-brand-tagline {
            font-size: 8.5px;
            color: #e9d5ff;
            margin: 0 auto 10px auto;
            line-height: 1.4;
            max-width: 34rem;
            text-align: center;
        }
        .pdf-doc-line {
            border-top: 1px solid rgba(255,255,255,0.25);
            margin: 10px 0 8px 0;
            padding-top: 8px;
        }
        .pdf-doc-title {
            font-size: 13px;
            font-weight: bold;
            margin: 0 0 3px 0;
            color: #ffffff;
        }
        .pdf-doc-sub {
            font-size: 9px;
            color: #f5d0fe;
            margin: 0 0 6px 0;
        }
        .pdf-meta {
            font-size: 8.5px;
            color: #ede9fe;
            margin: 0;
        }
        h2 {
            font-size: 11px;
            margin: 14px 0 6px 0;
            padding-bottom: 4px;
            color: #6a0392;
            border-bottom: 2px solid #e9d5ff;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .data-table { width: 100%; border-collapse: collapse; margin-top: 4px; margin-bottom: 2px; }
        .data-table th,
        .data-table td {
            border: 1px solid #d1d5db;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f3e8ff;
            color: #5b21b6;
            font-size: 8.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .data-table tbody tr:nth-child(even) { background-color: #fafafa; }
        .num { text-align: right; }
        .kpi-wrap { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .kpi-wrap td { width: 25%; vertical-align: top; padding: 3px; }
        .kpi-box {
            border: 1px solid #e9d5ff;
            border-radius: 4px;
            padding: 8px 6px;
            background-color: #faf5ff;
            text-align: center;
            min-height: 52px;
        }
        .kpi-label {
            font-size: 7.5px;
            text-transform: uppercase;
            color: #6b7280;
            margin: 0 0 4px 0;
            letter-spacing: 0.04em;
        }
        .kpi-val {
            font-size: 15px;
            font-weight: bold;
            color: #6a0392;
            margin: 0;
            line-height: 1.15;
        }
        .kpi-val--sm { font-size: 11px; }
    </style>
</head>
<body>
    <div class="pdf-header">
        <div class="pdf-header-inner">
            @if (!empty($logoDataUri))
                <div class="pdf-header-brand">
                    <img class="pdf-logo--hero" src="{{ $logoDataUri }}" alt="{{ $brandName }}">
                </div>
                <p class="pdf-brand-tagline">{{ __('stats.pdf_brand_tagline') }}</p>
            @else
                <p class="pdf-brand-name">{{ $brandName }}</p>
                <p class="pdf-brand-tagline">{{ __('stats.pdf_brand_tagline') }}</p>
            @endif
            <div class="pdf-doc-line">
                <p class="pdf-doc-title">{{ __('stats.pdf_title') }}</p>
                <p class="pdf-doc-sub">{{ __('stats.pdf_cover_subtitle') }}</p>
                <p class="pdf-meta">{{ __('stats.pdf_meta', ['name' => $userName, 'when' => $generatedAt]) }}</p>
            </div>
        </div>
    </div>

    <table class="kpi-wrap">
        <tr>
            <td>
                <div class="kpi-box">
                    <p class="kpi-label">{{ __('stats.kpi_total') }}</p>
                    <p class="kpi-val">{{ number_format($totalResp, 0, ',', '.') }}</p>
                </div>
            </td>
            <td>
                <div class="kpi-box">
                    <p class="kpi-label">{{ __('stats.kpi_avg') }}</p>
                    <p class="kpi-val">{{ $mediaAcertos }}%</p>
                </div>
            </td>
            <td>
                <div class="kpi-box">
                    <p class="kpi-label">{{ __('stats.kpi_sims') }}</p>
                    <p class="kpi-val">{{ number_format($totalSimulados, 0, ',', '.') }}</p>
                </div>
            </td>
            <td>
                <div class="kpi-box">
                    <p class="kpi-label">{{ __('stats.kpi_best') }}</p>
                    <p class="kpi-val kpi-val--sm">{{ $melhorMateria }}</p>
                </div>
            </td>
        </tr>
    </table>

    <h2>{{ __('stats.bar_title') }}</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('stats.pdf_th_subject') }}</th>
                <th class="num">{{ __('stats.pdf_th_pct') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($porMateria as $m)
                <tr>
                    <td>{{ $m['nome'] }}</td>
                    <td class="num">{{ $m['porcentagem'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="2">{{ __('stats.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>{{ __('stats.chart_title') }}</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('stats.pdf_th_day') }}</th>
                <th class="num">{{ __('stats.pdf_th_performance') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($evolucaoLinhas as $row)
                <tr>
                    <td>{{ $row['data'] }}</td>
                    <td class="num">{{ $row['pct'] }}%</td>
                </tr>
            @empty
                <tr><td colspan="2">{{ __('stats.no_data') }}</td></tr>
            @endforelse
        </tbody>
    </table>

    @if(!empty($desempenhoParcialPorMateria))
    <h2>{{ __('stats.partial_section_title') }}</h2>
    <p style="margin: 0 0 8px 0; font-size: 9px; color:#4b5563;">{{ __('stats.partial_section_help') }}</p>
    @foreach ($desempenhoParcialPorMateria as $bloque)
        <p style="font-weight:bold;margin:12px 0 4px;font-size:10.5px;">{{ ucfirst((string) $bloque['materia_nome']) }}</p>
        <table class="data-table" style="margin-bottom:10px;">
            <thead>
                <tr>
                    <th>{{ __('stats.partial_col_parcial') }}</th>
                    <th class="num">{{ __('stats.partial_col_hits') }}</th>
                    <th class="num">{{ __('stats.partial_col_total') }}</th>
                    <th class="num">{{ __('stats.pdf_th_pct') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($bloque['parciais'] as $pRow)
                    <tr>
                        <td>{{ $pRow['label'] }}</td>
                        <td class="num">{{ (int) $pRow['acertos'] }}</td>
                        <td class="num">{{ (int) $pRow['total'] }}</td>
                        <td class="num">{{ $pRow['pct'] }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endforeach
    @endif

    <h2>{{ __('stats.week_title') }}</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>{{ __('stats.th_week_start') }}</th>
                <th class="num">{{ __('stats.th_questions') }}</th>
                <th class="num">{{ __('stats.th_hits') }}</th>
                <th class="num">{{ __('stats.th_performance') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($semanal as $sem)
                @php
                    $aprov = $sem['total'] > 0 ? round(($sem['acertos'] / $sem['total']) * 100, 1) : 0;
                @endphp
                <tr>
                    <td>{{ \Carbon\Carbon::parse($sem['inicio_semana'])->format('d/m/Y') }}</td>
                    <td class="num">{{ (int) $sem['total'] }}</td>
                    <td class="num">{{ (int) $sem['acertos'] }}</td>
                    <td class="num">{{ $aprov }}%</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="pdf-footer">
        <strong>{{ $brandName }}</strong>
        <br>{{ __('stats.pdf_footer_rights', ['year' => $reportYear, 'brand' => $brandName]) }}
        <br><span style="font-size:7.5px;color:#9ca3af;">{{ __('stats.pdf_footer_disclaimer') }}</span>
    </div>
</body>
</html>
