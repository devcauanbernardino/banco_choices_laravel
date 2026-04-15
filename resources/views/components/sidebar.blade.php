@php
    $navLinks = [
        ['route' => 'dashboard',     'icon' => 'dashboard',  'label' => __('nav.dashboard'),  'short' => __('nav.dashboard')],
        ['route' => 'stats',         'icon' => 'bar_chart',  'label' => __('nav.stats'),      'short' => __('nav.stats')],
        ['route' => 'questionbank',  'icon' => 'quiz',       'label' => __('nav.bank'),       'short' => __('nav.bank')],
        ['route' => 'history',       'icon' => 'assignment',  'label' => __('nav.simulados'),  'short' => __('nav.simulados')],
        ['route' => 'addon.materias', 'icon' => 'add_shopping_cart', 'label' => __('nav.buy_subjects'), 'short' => __('nav.buy_subjects_short')],
    ];
@endphp

{{-- Desktop sidebar --}}
<aside class="app-sidebar d-none d-lg-flex flex-column" id="appSidebarDesktop"
       aria-label="{{ __('nav.menu_aria') }}">

    {{-- Brand --}}
    <div class="app-sidebar-brand">
        <a class="app-sidebar-brand-link text-decoration-none" href="{{ route('dashboard') }}"
           aria-label="{{ __('index.page_title') }}"
           data-sidebar-tooltip="{{ __('index.page_title') }}">
            <span class="app-sidebar-logo-wrap app-sidebar-logo-wrap--brand" aria-hidden="true">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" width="160" height="40"
                     class="app-sidebar-logo app-sidebar-logo--brand">
            </span>
            <div class="app-sidebar-brand-text">
                <div class="app-sidebar-sub">{{ __('sidebar.subtitle') }}</div>
            </div>
        </a>
    </div>

    {{-- Navegação principal: única zona que rola (altura curta / zoom) --}}
    <nav class="app-sidebar-nav flex-grow-1" aria-label="{{ __('nav.menu_aria') }}">
        @foreach ($navLinks as $link)
            <a class="app-sidebar-link{{ request()->routeIs($link['route']) ? ' active' : '' }}"
               href="{{ route($link['route']) }}"
               data-sidebar-tooltip="{{ $link['label'] }}"
               @if(request()->routeIs($link['route'])) aria-current="page" @endif>
                <span class="material-icons" aria-hidden="true">{{ $link['icon'] }}</span>
                <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Rodapé fixo: conta → idioma/região → recolher painel (scroll só no .app-sidebar-nav) --}}
    <div class="app-sidebar-end">
        <div class="app-sidebar-footer">
            <span class="app-sidebar-section-label">{{ __('sidebar.account') }}</span>

            <a class="app-sidebar-link{{ request()->routeIs('profile.show') ? ' active' : '' }}"
               href="{{ route('profile.show') }}"
               data-sidebar-tooltip="{{ __('sidebar.profile') }}">
                <span class="material-icons" aria-hidden="true">person</span>
                <span class="app-sidebar-link-text">{{ __('sidebar.profile') }}</span>
            </a>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="app-sidebar-link app-sidebar-link-logout w-100 border-0 bg-transparent text-start"
                        data-sidebar-tooltip="{{ __('sidebar.logout') }}">
                    <span class="material-icons" aria-hidden="true">logout</span>
                    <span class="app-sidebar-link-text">{{ __('sidebar.logout') }}</span>
                </button>
            </form>
        </div>

        <div class="app-sidebar-section px-3 pb-2 pt-2 app-sidebar-section--lang">
            <span class="app-sidebar-section-label">{{ __('lang.selector_label') }}</span>
            <div class="mt-2">
                @include('components.language-selector', ['sidebarCollapsedTooltip' => __('lang.selector_label')])
            </div>
        </div>

        <div class="app-sidebar-collapse-wrap d-none d-lg-flex">
            <button type="button" class="app-sidebar-collapse-btn js-sidebar-toggle"
                    aria-expanded="true" aria-controls="appSidebarDesktop"
                    data-tooltip-expanded="{{ __('sidebar.collapse_aria') }}"
                    data-tooltip-collapsed="{{ __('sidebar.expand_aria') }}"
                    data-label-expanded="{{ __('sidebar.collapse') }}"
                    data-label-collapsed="{{ __('sidebar.expand') }}"
                    data-sidebar-tooltip="{{ __('sidebar.collapse_aria') }}">
                <span class="material-icons app-sidebar-collapse-ico" aria-hidden="true">keyboard_double_arrow_left</span>
                <span class="app-sidebar-collapse-label">{{ __('sidebar.collapse') }}</span>
            </button>
        </div>
    </div>
</aside>

{{-- Mobile offcanvas "More" panel --}}
<div class="offcanvas offcanvas-bottom app-offcanvas-more" tabindex="-1" id="sidebarMobile"
     aria-labelledby="sidebarMobileLabel">
    <div class="app-offcanvas-more-handle" aria-hidden="true"></div>
    <div class="offcanvas-header border-bottom border-opacity-10">
        <div class="d-flex align-items-center gap-2" id="sidebarMobileLabel">
            <span class="fw-bold">{{ __('sidebar.more_options') }}</span>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"
                aria-label="{{ __('sidebar.close') }}"></button>
    </div>
    <div class="offcanvas-body pb-4">
        <div class="app-sidebar-section px-0">
            <span class="app-sidebar-section-label">{{ __('lang.selector_label') }}</span>
            <div class="mt-2">
                @include('components.language-selector')
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="app-sidebar-link app-sidebar-link-logout mt-3 d-flex rounded-3 w-100 border-0 bg-transparent text-start"
                    title="{{ __('sidebar.logout') }}">
                <span class="material-icons" aria-hidden="true">logout</span>
                <span class="app-sidebar-link-text">{{ __('sidebar.logout') }}</span>
            </button>
        </form>
    </div>
</div>

{{-- Mobile bottom navigation --}}
<nav class="app-mobile-bottom d-lg-none" aria-label="{{ __('nav.menu_aria') }}">
    <div class="app-mobile-bottom-inner">
        @foreach ($navLinks as $link)
            @php $active = request()->routeIs($link['route']); @endphp
            <a class="app-mobile-bottom-item{{ $active ? ' active' : '' }}"
               href="{{ route($link['route']) }}"
               @if($active) aria-current="page" @endif>
                <span class="material-icons" aria-hidden="true">{{ $link['icon'] }}</span>
                <span class="app-mobile-bottom-label">{{ $link['short'] }}</span>
            </a>
        @endforeach

        <a class="app-mobile-bottom-item{{ request()->routeIs('profile.show') ? ' active' : '' }}"
           href="{{ route('profile.show') }}"
           @if(request()->routeIs('profile.show')) aria-current="page" @endif>
            <span class="material-icons" aria-hidden="true">person</span>
            <span class="app-mobile-bottom-label">{{ __('nav.profile') }}</span>
        </a>

        <button type="button" class="app-mobile-bottom-item app-mobile-bottom-item--btn"
                data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile"
                aria-controls="sidebarMobile" aria-label="{{ __('sidebar.more_aria') }}">
            <span class="material-icons" aria-hidden="true">more_horiz</span>
            <span class="app-mobile-bottom-label">{{ __('sidebar.more') }}</span>
        </button>
    </div>
</nav>
