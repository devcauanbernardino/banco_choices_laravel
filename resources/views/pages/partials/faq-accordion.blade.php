@php
    $faqs = [];
    for ($i = 1; $i <= 6; $i++) {
        $faqs[] = [
            'q' => __('landing.faq.q'.$i),
            'a' => __('landing.faq.a'.$i),
        ];
    }
@endphp
<div class="accordion lp-faq__accordion" id="lpFaqAccordion">
    @foreach($faqs as $i => $f)
        @php $idx = $i + 1; @endphp
        <div class="accordion-item lp-faq__item lp-glass">
            <h3 class="accordion-header" id="lpFaqHeading{{ $idx }}">
                <button class="accordion-button collapsed lp-faq__button" type="button"
                        data-bs-toggle="collapse" data-bs-target="#lpFaqCollapse{{ $idx }}"
                        aria-expanded="false" aria-controls="lpFaqCollapse{{ $idx }}">
                    <span>{{ $f['q'] }}</span>
                </button>
            </h3>
            <div id="lpFaqCollapse{{ $idx }}" class="accordion-collapse collapse"
                 aria-labelledby="lpFaqHeading{{ $idx }}" data-bs-parent="#lpFaqAccordion">
                <div class="accordion-body lp-faq__body">
                    {{ $f['a'] }}
                </div>
            </div>
        </div>
    @endforeach
</div>
