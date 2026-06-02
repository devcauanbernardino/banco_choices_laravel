@php
    $faviconSvg = null;
    foreach ([
        public_path('assets/img/favicon.svg'),
        base_path('public/assets/img/favicon.svg'),
    ] as $svgFile) {
        if (is_file($svgFile)) {
            $faviconSvg = file_get_contents($svgFile);
            break;
        }
    }
@endphp
@if ($faviconSvg)
    <link rel="icon" href="data:image/svg+xml,{{ rawurlencode($faviconSvg) }}" type="image/svg+xml">
@else
    <link rel="icon" href="{{ route('favicon') }}" type="image/png">
@endif
