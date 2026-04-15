@php
    $currentLocale = app()->getLocale();
    $locales = [
        'es_AR' => ['label' => __('lang.name_es_AR'), 'code' => 'AR', 'flag' => "\u{1F1E6}\u{1F1F7}"],
        'pt_BR' => ['label' => __('lang.name_pt_BR'), 'code' => 'BR', 'flag' => "\u{1F1E7}\u{1F1F7}"],
        'en_US' => ['label' => __('lang.name_en_US'), 'code' => 'US', 'flag' => "\u{1F1FA}\u{1F1F8}"],
    ];
@endphp

<div class="dropdown bc-lang-selector">
    <button class="btn btn-navbar-lang dropdown-toggle d-inline-flex align-items-center gap-2 w-100"
            type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside"
            aria-expanded="false" aria-label="{{ __('lang.selector_aria') }}">
        <svg class="bc-lang-icon-svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
             xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                  stroke="currentColor" stroke-width="1.5"/>
            <path d="M2 12H22" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/>
            <path d="M12 2C14.5013 4.73835 15.9228 8.29203 16 12C15.9228 15.708 14.5013 19.2616 12 22C9.49872 19.2616 8.07725 15.708 8 12C8.07725 8.29203 9.49872 4.73835 12 2Z"
                  stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <span class="d-none d-sm-inline">{{ __('lang.selector_label') }}</span>
    </button>

    <ul class="dropdown-menu dropdown-menu-end bc-lang-menu bc-lang-menu--landing shadow">
        <li>
            <h6 class="dropdown-header bc-lang-menu__heading mb-0">{{ __('lang.selector_label') }}</h6>
        </li>
        @foreach ($locales as $code => $info)
            <li>
                @if ($code === $currentLocale)
                    <span class="dropdown-item bc-lang-menu__item bc-lang-menu__item--active" aria-current="true">
                        <span class="bc-lang-menu__code" aria-hidden="true">{{ $info['code'] }}</span>
                        <span class="bc-lang-menu__flag" aria-hidden="true">{{ $info['flag'] }}</span>
                        <span class="bc-lang-menu__label">{{ $info['label'] }}</span>
                        <span class="bc-lang-menu__tick" aria-hidden="true"><span class="bc-lang-menu__check">&check;</span></span>
                    </span>
                @else
                    <form method="POST" action="{{ route('set-locale') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="locale" value="{{ $code }}">
                        <button type="submit" class="dropdown-item bc-lang-menu__item w-100 text-start border-0 bg-transparent">
                            <span class="bc-lang-menu__code" aria-hidden="true">{{ $info['code'] }}</span>
                            <span class="bc-lang-menu__flag" aria-hidden="true">{{ $info['flag'] }}</span>
                            <span class="bc-lang-menu__label">{{ $info['label'] }}</span>
                            <span class="bc-lang-menu__tick" aria-hidden="true"></span>
                        </button>
                    </form>
                @endif
            </li>
        @endforeach
    </ul>
</div>
