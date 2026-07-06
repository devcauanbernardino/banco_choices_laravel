@if (session('success') || session('error'))
<div class="bc-toast-wrap" aria-live="polite" aria-atomic="true">
    @if (session('success'))
        <div class="bc-toast bc-toast--success" role="status" data-bc-toast>
            <span class="material-icons bc-toast__icon" aria-hidden="true">check_circle</span>
            <span class="bc-toast__msg">{{ session('success') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="bc-toast bc-toast--error" role="alert" data-bc-toast>
            <span class="material-icons bc-toast__icon" aria-hidden="true">error</span>
            <span class="bc-toast__msg">{{ session('error') }}</span>
        </div>
    @endif
</div>
@endif
