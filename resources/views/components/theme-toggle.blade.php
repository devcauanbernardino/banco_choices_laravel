@php
    $isSheet = !empty($sheet);
    $rowClass = 'app-sidebar-theme-row' . ($isSheet ? ' app-sidebar-theme-row--sheet' : '');
@endphp

<div class="{{ $rowClass }}" title="{{ __('sidebar.appearance') }}">
    <div class="app-sidebar-theme-seg" role="group" aria-label="{{ __('sidebar.theme_group_aria') }}">
        <button type="button"
                class="app-sidebar-theme-opt js-theme-mode-btn"
                data-theme="light"
                aria-pressed="false"
                aria-label="{{ __('sidebar.theme_light_aria') }}">
            <span class="material-icons" aria-hidden="true">light_mode</span>
        </button>
        <button type="button"
                class="app-sidebar-theme-opt js-theme-mode-btn"
                data-theme="dark"
                aria-pressed="false"
                aria-label="{{ __('sidebar.theme_dark_aria') }}">
            <span class="material-icons" aria-hidden="true">dark_mode</span>
        </button>
    </div>
</div>
