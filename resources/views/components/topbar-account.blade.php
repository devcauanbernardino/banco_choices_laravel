@auth
    @php
        $tbUser = auth()->user();
        $tbName = trim((string) ($tbUser->nome ?? ''));
        $tbEmail = trim((string) ($tbUser->email ?? ''));
        $tbAvatarUrl = 'https://ui-avatars.com/api/?name=' . urlencode($tbName !== '' ? $tbName : '?') . '&background=6a0392&color=fff&size=128&bold=true';
        $tbProfileAria = $tbName !== '' ? __('sidebar.profile') . ': ' . $tbName : __('nav.profile');
    @endphp
    <div class="dropdown app-content-topbar-account flex-shrink-0">
        <button type="button"
                class="btn app-content-topbar-account-btn dropdown-toggle d-inline-flex align-items-center justify-content-center"
                data-bs-toggle="dropdown"
                data-bs-auto-close="true"
                data-bs-popper-config='{"strategy":"fixed"}'
                aria-expanded="false"
                aria-label="{{ $tbProfileAria }}">
            <img src="{{ $tbAvatarUrl }}" alt="" class="app-content-topbar-account-avatar">
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow bc-lang-menu bc-lang-menu--landing app-content-topbar-account-menu">
            <li>
                <h6 class="dropdown-header bc-lang-menu__heading mb-0">{{ __('sidebar.account') }}</h6>
            </li>
            @if ($tbName !== '' || $tbEmail !== '')
                <li class="bc-account-menu__meta">
                    @if ($tbName !== '')
                        <div class="bc-account-menu__meta-name">{{ $tbName }}</div>
                    @endif
                    @if ($tbEmail !== '')
                        <div class="bc-account-menu__meta-email">{{ $tbEmail }}</div>
                    @endif
                </li>
            @endif
            <li>
                <a class="dropdown-item bc-lang-menu__item" href="{{ route('profile.show') }}">
                    <span class="bc-account-menu__ico-wrap" aria-hidden="true">
                        <span class="material-symbols-outlined">person</span>
                    </span>
                    <span class="bc-lang-menu__label">{{ __('sidebar.profile') }}</span>
                    <span class="bc-lang-menu__tick" aria-hidden="true"></span>
                </a>
            </li>
            <li>
                <form method="POST" action="{{ route('logout') }}" class="bc-lang-menu__form w-100 m-0 p-0">
                    @csrf
                    <button type="submit" class="dropdown-item bc-lang-menu__item bc-account-menu__item--danger w-100 text-start border-0 bg-transparent">
                        <span class="bc-account-menu__ico-wrap" aria-hidden="true">
                            <span class="material-symbols-outlined">logout</span>
                        </span>
                        <span class="bc-lang-menu__label">{{ __('sidebar.logout') }}</span>
                        <span class="bc-lang-menu__tick" aria-hidden="true"></span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
@endauth
