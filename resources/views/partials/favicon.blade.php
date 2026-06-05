@if ($inlineFavicon = \App\Support\Branding::faviconInlineDataUri())
    <link rel="icon" href="{{ $inlineFavicon }}" type="image/svg+xml">
@else
    <link rel="icon" href="{{ \App\Support\Branding::faviconUrl() }}" type="{{ \App\Support\Branding::faviconMimeType() }}">
@endif
