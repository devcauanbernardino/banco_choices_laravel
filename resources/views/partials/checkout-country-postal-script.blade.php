@php
    $countryId = $countryId ?? 'checkout-country';
    $postalId = $postalId ?? 'checkout-postal';
    $feedbackId = $postalId.'-feedback';
@endphp
<script>
(function () {
    const countryEl = document.getElementById(@json($countryId));
    const postalEl = document.getElementById(@json($postalId));
    const fb = document.getElementById(@json($feedbackId));
    if (!countryEl || !postalEl || !fb) {
        return;
    }
    const url = @json(url('/api/postal-lookup'));
    const i18n = {
        ok: @json(__('signup.checkout.postal_lookup_ok')),
        okShort: @json(__('signup.checkout.postal_lookup_ok_short')),
        notFound: @json(__('signup.checkout.postal_lookup_not_found')),
        invalid: @json(__('signup.checkout.postal_lookup_invalid')),
    };

    function clearFb() {
        fb.textContent = '';
        fb.className = 'small mt-1';
        fb.hidden = true;
    }

    function setFb(kind, msg) {
        fb.hidden = false;
        fb.textContent = msg;
        fb.className = 'small mt-1 ' + (kind === 'ok' ? 'text-success' : 'text-danger');
    }

    function labelFromData(data) {
        if (!data || typeof data !== 'object') {
            return '';
        }
        if (data.localidade) {
            return [data.localidade, data.uf].filter(Boolean).join(' — ');
        }
        if (data.places && data.places[0]) {
            const p = data.places[0];
            const parts = [p['place name'], p.state || p['state abbreviation']].filter(Boolean);
            return parts.join(', ') + (data.country ? ' (' + data.country + ')' : '');
        }
        return '';
    }

    async function lookup() {
        clearFb();
        const country = (countryEl.value || '').toUpperCase();
        const postal = (postalEl.value || '').trim();
        if (!postal) {
            return;
        }
        if (country === 'BR') {
            const digits = postal.replace(/\D/g, '');
            if (digits.length !== 8) {
                setFb('err', i18n.invalid);
                return;
            }
        }
        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ country: country, postal_code: postal }),
            });
            const data = await res.json().catch(function () { return {}; });
            if (!res.ok) {
                setFb('err', i18n.notFound);
                return;
            }
            const loc = labelFromData(data);
            setFb('ok', loc ? (i18n.ok + ' ' + loc) : i18n.okShort);
        } catch (e) {
            setFb('err', i18n.notFound);
        }
    }

    postalEl.addEventListener('blur', lookup);
    countryEl.addEventListener('change', clearFb);
})();
</script>
