@php
    $userName = Auth::user()->nome ?? '';
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6a0392&color=fff&size=128&bold=true';
    $userInitial = $userName !== '' ? mb_strtoupper(mb_substr($userName, 0, 1, 'UTF-8'), 'UTF-8') : '?';
@endphp

<header class="bc-topbar d-none d-lg-flex justify-content-between align-items-center">
    <div class="bc-topbar-title-block min-w-0">
        <h1 class="bc-topbar-title">{{ $topbarTitle ?? '' }}</h1>
        @if (!empty($topbarSubtitle))
            <p class="bc-topbar-sub">{{ $topbarSubtitle }}</p>
        @endif
    </div>

    <div class="bc-topbar-actions d-flex align-items-center gap-2">
        {{-- Theme toggle button --}}
        <button type="button" class="bc-topbar-btn js-theme-toggle-btn"
                aria-label="{{ __('sidebar.appearance') }}"
                aria-pressed="false">
            <span class="material-icons bc-topbar-btn-icon bc-topbar-btn-icon--light" aria-hidden="true">light_mode</span>
            <span class="material-icons bc-topbar-btn-icon bc-topbar-btn-icon--dark" aria-hidden="true">dark_mode</span>
        </button>

        {{-- Profile avatar --}}
        <a class="bc-topbar-avatar-link" href="{{ route('profile.show') }}"
           title="{{ $userName }}">
            <img class="bc-topbar-avatar"
                 src="{{ $avatarUrl }}"
                 alt="{{ $userInitial }}">
        </a>
    </div>
</header>
