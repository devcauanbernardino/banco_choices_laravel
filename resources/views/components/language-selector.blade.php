@php
    $currentLocale = app()->getLocale();
    $locales = [
        'es_AR' => ['label' => __('lang.name_es_AR'), 'short' => 'Español', 'flag_id' => 'ar'],
        'pt_BR' => ['label' => __('lang.name_pt_BR'), 'short' => 'Português', 'flag_id' => 'br'],
    ];
    $currentFlagId = $locales[$currentLocale]['flag_id'] ?? 'ar';
    /* Sidebar: menu ao lado do botão (direita). Sem isso, dropdown-menu-end + fixed faz o Popper mandar o menu para o canto oposto da viewport. */
    $langInSidebar = isset($sidebarCollapsedTooltip);
    $langPopperConfig = $langInSidebar
        ? [
            'strategy' => 'fixed',
            'placement' => 'right-start',
            'modifiers' => [
                ['name' => 'flip', 'enabled' => false],
            ],
        ]
        : [
            'strategy' => 'fixed',
        ];
    $compactTopbar = ! empty($compactTopbar ?? false);
@endphp

<div class="dropdown bc-lang-selector">
    <button class="btn btn-navbar-lang dropdown-toggle d-inline-flex align-items-center gap-2{{ $compactTopbar ? ' btn-navbar-lang--compact-topbar' : ' w-100' }}"
            type="button" data-bs-toggle="dropdown" data-bs-auto-close="true"
            data-bs-popper-config='@json($langPopperConfig)'
            aria-expanded="false" aria-label="{{ __('lang.selector_aria') }}"
            @isset($sidebarCollapsedTooltip) data-sidebar-tooltip="{{ $sidebarCollapsedTooltip }}" @endisset>
        <div class="bc-lang-btn-inner">
            <img class="bc-lang-icon-flag"
                 src="{{ asset('assets/img/flags/'.$currentFlagId.'.svg') }}"
                 width="22" height="17"
                 alt=""
                 decoding="async">
            @unless ($compactTopbar)
                <span class="d-none d-sm-inline">{{ __('lang.selector_label') }}</span>
            @endunless
        </div>
    </button>

    <ul class="dropdown-menu bc-lang-menu bc-lang-menu--landing shadow{{ $langInSidebar ? '' : ' dropdown-menu-end' }}">
        @foreach ($locales as $code => $info)
            <li>
                @if ($code === $currentLocale)
                    <span class="dropdown-item bc-lang-menu__item bc-lang-menu__item--active" aria-current="true">
                        <span class="bc-lang-menu__code" aria-hidden="true">{{ strtoupper($info['flag_id']) }}</span>
                        <span class="bc-lang-menu__label">{{ $info['short'] }}</span>
                    </span>
                @else
                    <form method="POST" action="{{ route('set-locale') }}" class="bc-lang-menu__form w-100 m-0 p-0">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit" class="dropdown-item bc-lang-menu__item w-100 text-start border-0 bg-transparent">
                            <span class="bc-lang-menu__code" aria-hidden="true">{{ strtoupper($info['flag_id']) }}</span>
                            <span class="bc-lang-menu__label">{{ $info['short'] }}</span>
                        </button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</div>
