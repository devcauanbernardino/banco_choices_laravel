@php
    $faviconPath = \App\Support\Branding::faviconPublicPath();
@endphp
<link rel="icon" href="{{ asset($faviconPath) }}?v={{ filemtime(public_path($faviconPath)) }}" type="{{ \App\Support\Branding::faviconMimeType() }}">
