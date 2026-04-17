@php
    $userName = Auth::user()->nome ?? '';
    $avatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($userName) . '&background=6a0392&color=fff&size=128&bold=true';
@endphp

<header class="bc-topbar bc-topbar--painel d-none d-lg-flex align-items-center">
    <div class="bc-topbar-title-block min-w-0 d-flex flex-column justify-content-center py-1">
        <p class="bc-topbar-kicker mb-0">{{ __('sidebar.subtitle') }}</p>
        <h1 class="bc-topbar-title">{{ $topbarTitle ?? '' }}</h1>
    </div>

    <div class="bc-topbar-center d-flex align-items-center flex-grow-1 min-w-0 px-3 px-xl-4">
        <form class="bc-topbar-search w-100" action="{{ route('questionbank') }}" method="get" role="search">
            <label class="visually-hidden" for="bcTopbarSearchInput">{{ __('topbar.search_placeholder') }}</label>
            <span class="material-symbols-outlined bc-topbar-search-icon" aria-hidden="true">search</span>
            <input id="bcTopbarSearchInput" type="search" name="q" value="{{ request('q') }}"
                   class="form-control bc-topbar-search-input"
                   placeholder="{{ __('topbar.search_placeholder') }}"
                   autocomplete="off">
        </form>
    </div>

    <div class="bc-topbar-toolbar d-flex align-items-center justify-content-end flex-shrink-0 py-0">
        <div class="bc-topbar-utility-pill d-inline-flex align-items-center gap-1">
            <button type="button" class="bc-topbar-btn bc-topbar-btn--theme js-theme-toggle-btn"
                    aria-label="{{ __('sidebar.appearance') }}"
                    aria-pressed="false">
                <span class="material-symbols-outlined bc-topbar-btn-icon bc-topbar-btn-icon--light" aria-hidden="true">light_mode</span>
                <span class="material-symbols-outlined bc-topbar-btn-icon bc-topbar-btn-icon--dark" aria-hidden="true">dark_mode</span>
            </button>
            <button type="button" class="bc-topbar-icon-btn" disabled
                    aria-label="{{ __('topbar.notifications_aria') }}"
                    title="{{ __('topbar.notifications_aria') }}">
                <span class="material-symbols-outlined" aria-hidden="true">notifications</span>
            </button>
            <a href="{{ route('home') }}" class="bc-topbar-icon-btn"
               aria-label="{{ __('topbar.help_aria') }}"
               title="{{ __('topbar.help_aria') }}">
                <span class="material-symbols-outlined" aria-hidden="true">help</span>
            </a>
            <a href="{{ route('profile.show') }}" class="bc-topbar-icon-btn bc-topbar-icon-btn--avatar d-none d-xl-inline-flex"
               title="{{ $userName }}">
                <img class="bc-topbar-avatar-tiny" src="{{ $avatarUrl }}" alt="{{ __('sidebar.profile') }}" width="32" height="32">
            </a>
        </div>
    </div>
</header>
