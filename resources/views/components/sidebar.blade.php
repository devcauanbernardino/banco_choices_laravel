@php
    $navLinks = [
        ['route' => 'dashboard', 'symbol' => 'dashboard', 'mobile_icon' => 'dashboard', 'label' => __('nav.dashboard'), 'short' => __('nav.dashboard')],
        ['route' => 'questionbank', 'symbol' => 'menu_book', 'mobile_icon' => 'quiz', 'label' => __('nav.studies'), 'short' => __('nav.studies_short')],
        ['route' => 'history', 'symbol' => 'folder_open', 'mobile_icon' => 'folder', 'label' => __('nav.files'), 'short' => __('nav.files_short')],
        ['route' => 'stats', 'symbol' => 'analytics', 'mobile_icon' => 'bar_chart', 'label' => __('nav.stats'), 'short' => __('nav.stats')],
        ['route' => 'referral.show', 'symbol' => 'redeem', 'mobile_icon' => 'redeem', 'label' => __('nav.referrals'), 'short' => __('nav.referrals_short')],
        ['route' => 'addon.materias', 'symbol' => 'shopping_cart', 'mobile_icon' => 'add_shopping_cart', 'label' => __('nav.buy_subjects'), 'short' => __('nav.buy_subjects_short')],
    ];
@endphp

{{-- Desktop sidebar — referência visual: stats curadoria (nav + CTA + utilizador) --}}
<aside class="app-sidebar app-sidebar--painel d-none d-lg-flex flex-column" id="appSidebarDesktop"
       aria-label="{{ __('nav.menu_aria') }}">

    <div class="app-sidebar-brand">
        <a class="app-sidebar-brand-link text-decoration-none" href="{{ route('dashboard') }}"
           aria-label="{{ __('index.page_title') }}"
           data-sidebar-tooltip="{{ __('index.page_title') }}">
            <span class="app-sidebar-brand-logo-box" aria-hidden="true">
                <img src="{{ \App\Support\Branding::logoUrl() }}" alt="" class="app-sidebar-brand-logo-img" width="40" height="40">
            </span>
            <div class="app-sidebar-brand-text min-w-0">
                <p class="app-sidebar-brand-title">{{ __('index.page_title') }}</p>
                <div class="app-sidebar-sub">{{ __('sidebar.brand_tagline') }}</div>
            </div>
        </a>
    </div>

    <div class="app-sidebar-cta-wrap">
        <a href="{{ route('questionbank') }}" class="app-sidebar-cta"
           data-sidebar-tooltip="{{ __('sidebar.new_sim') }}">
            <span class="app-sidebar-cta-plus" aria-hidden="true">+</span>
            <span class="app-sidebar-cta-label">{{ __('sidebar.new_sim') }}</span>
        </a>
    </div>

    <nav class="app-sidebar-nav flex-grow-1" aria-label="{{ __('nav.menu_aria') }}">
        @foreach ($navLinks as $link)
            <a class="app-sidebar-link{{ request()->routeIs($link['route']) ? ' active' : '' }}"
               href="{{ route($link['route']) }}"
               data-sidebar-tooltip="{{ $link['label'] }}"
               @if(request()->routeIs($link['route'])) aria-current="page" @endif>
                <span class="material-symbols-outlined{{ request()->routeIs($link['route']) ? ' is-fill' : '' }}" aria-hidden="true">{{ $link['symbol'] }}</span>
                <span class="app-sidebar-link-text">{{ $link['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="app-sidebar-bottom">
        <div class="app-sidebar-collapse-wrap d-none d-lg-flex">
            <button type="button" class="app-sidebar-collapse-btn js-sidebar-toggle"
                    aria-expanded="true" aria-controls="appSidebarDesktop"
                    data-tooltip-expanded="{{ __('sidebar.collapse_aria') }}"
                    data-tooltip-collapsed="{{ __('sidebar.expand_aria') }}"
                    data-label-expanded="{{ __('sidebar.collapse') }}"
                    data-label-collapsed="{{ __('sidebar.expand') }}"
                    data-sidebar-tooltip="{{ __('sidebar.collapse_aria') }}">
                <span class="material-symbols-outlined app-sidebar-collapse-ico" aria-hidden="true">keyboard_double_arrow_left</span>
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
    <div class="offcanvas-body app-offcanvas-more-body d-flex flex-column pb-4">
        <div class="flex-grow-1 min-h-0" aria-hidden="true"></div>

        <div class="app-offcanvas-account flex-shrink-0 pt-1">
            <a href="{{ route('profile.show') }}" class="btn btn-outline-primary w-100 rounded-3 d-inline-flex align-items-center justify-content-center gap-2 py-2 mb-2">
                <span class="material-icons" aria-hidden="true">person</span>
                {{ __('sidebar.profile') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" class="app-sidebar-logout-form mt-2 mb-0">
                @csrf
                <button type="submit"
                        class="app-sidebar-link app-sidebar-link-logout d-flex rounded-3 w-100 border-0 bg-transparent text-start align-items-center"
                        title="{{ __('sidebar.logout') }}">
                    <span class="material-icons" aria-hidden="true">logout</span>
                    <span class="app-sidebar-link-text">{{ __('sidebar.logout') }}</span>
                </button>
            </form>
        </div>
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
                <span class="material-icons" aria-hidden="true">{{ $link['mobile_icon'] }}</span>
                <span class="app-mobile-bottom-label">{{ $link['short'] }}</span>
            </a>
        @endforeach

        <button type="button" class="app-mobile-bottom-item app-mobile-bottom-item--btn"
                data-bs-toggle="offcanvas" data-bs-target="#sidebarMobile"
                aria-controls="sidebarMobile" aria-label="{{ __('sidebar.more_aria') }}">
            <span class="material-icons" aria-hidden="true">more_horiz</span>
            <span class="app-mobile-bottom-label">{{ __('sidebar.more') }}</span>
        </button>
    </div>
</nav>
