<link rel="icon" href="{{ \App\Support\Branding::faviconUrl() }}" type="{{ \App\Support\Branding::faviconMimeType() }}">
@if ($ico = \App\Support\Branding::faviconIcoUrl())
    <link rel="shortcut icon" href="{{ $ico }}">
@endif
