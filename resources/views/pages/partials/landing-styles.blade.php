@php
    $landingCssPath = public_path('assets/css/landing-v2.css');
    $landingCssVersion = is_file($landingCssPath) ? filemtime($landingCssPath) : time();
@endphp
<link rel="stylesheet" href="{{ route('landing.css') }}?v={{ $landingCssVersion }}">
