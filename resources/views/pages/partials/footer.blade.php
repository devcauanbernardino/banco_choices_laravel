@php
    $whatsapp = config('landing.footer.social.whatsapp');
    $instagram = config('landing.footer.social.instagram');
    $brandName = config('branding.brand_name', 'Banco de Choices');
@endphp
<footer style="background: #f8f9fc; border-top: 1px solid rgba(15,23,42,.08); padding: 64px 0 28px;">
  <div style="max-width: 1200px; margin: 0 auto; padding: 0 clamp(16px,4vw,28px); box-sizing: border-box;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 44px; margin-bottom: 48px;">

      <!-- Brand column -->
      <div>
        <a href="{{ route('home') }}" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 14px;">
          <img src="{{ \App\Support\Branding::logoUrl() }}" alt="{{ $brandName }}" style="width: 38px; height: 38px; object-fit: contain; display: block;">
        </a>
        <p style="color: #6b7280; font-size: .875rem; line-height: 1.65; margin: 0 0 22px; max-width: 260px;">{{ __('landing.footer.tagline') }}</p>
        <div style="display: flex; gap: 10px;">
          <a href="{{ $instagram ?: '#' }}" {{ $instagram ? 'target=_blank rel=noopener' : '' }} aria-label="Instagram" class="lp-footer-social-ic" style="width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(106,3,146,.09); border: 1px solid rgba(106,3,146,.22); color: #6a0392; text-decoration: none; font-size: 1.05rem; transition: background .18s ease;">
            <i class="bi bi-instagram"></i>
          </a>
          <a href="{{ $whatsapp ?: '#' }}" {{ $whatsapp ? 'target=_blank rel=noopener' : '' }} aria-label="WhatsApp" class="lp-footer-social-ic" style="width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(106,3,146,.09); border: 1px solid rgba(106,3,146,.22); color: #6a0392; text-decoration: none; font-size: 1.05rem; transition: background .18s ease;">
            <i class="bi bi-whatsapp"></i>
          </a>
          <a href="#" aria-label="LinkedIn" class="lp-footer-social-ic" style="width: 38px; height: 38px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(106,3,146,.09); border: 1px solid rgba(106,3,146,.22); color: #6a0392; text-decoration: none; font-size: 1.05rem; transition: background .18s ease;">
            <i class="bi bi-linkedin"></i>
          </a>
        </div>
      </div>

      <!-- Nav column -->
      <div>
        <p style="font-size: .72rem; font-weight: 700; letter-spacing: .14em; color: #9ca3af; text-transform: uppercase; margin: 0 0 18px;">{{ __('landing.footer.nav_title') }}</p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 12px; margin: 0; padding: 0;">
          <li><a href="{{ route('home') }}#funcionalidades" class="lp-footer-nav-link" style="color: #374151; font-size: .9rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.topbar.funcionalidades') }}</a></li>
          <li><a href="{{ route('home') }}#modalidades" class="lp-footer-nav-link" style="color: #374151; font-size: .9rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.topbar.modalidades') }}</a></li>
          <li><a href="{{ route('home') }}#planes" class="lp-footer-nav-link" style="color: #374151; font-size: .9rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.topbar.planes') }}</a></li>
          <li><a href="{{ route('home') }}#faq" class="lp-footer-nav-link" style="color: #374151; font-size: .9rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.faq.title') }}</a></li>
        </ul>
      </div>

      <!-- Legal column -->
      <div>
        <p style="font-size: .72rem; font-weight: 700; letter-spacing: .14em; color: #9ca3af; text-transform: uppercase; margin: 0 0 18px;">{{ __('landing.footer.sobre_title') }}</p>
        <p style="color: #6b7280; font-size: .875rem; line-height: 1.65; margin: 0 0 20px;">{{ __('landing.footer.objetivo') }}</p>
        <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; margin: 0; padding: 0;">
          <li><a href="{{ route('terms') }}" class="lp-footer-nav-link" style="color: #374151; font-size: .875rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.footer.legal.terms') }}</a></li>
          <li><a href="{{ route('terms') }}" class="lp-footer-nav-link" style="color: #374151; font-size: .875rem; text-decoration: none; transition: color .18s ease;">{{ __('landing.footer.legal.privacy') }}</a></li>
          <li><a href="mailto:bancodechoices@gmail.com" class="lp-footer-nav-link" style="color: #374151; font-size: .875rem; text-decoration: none; transition: color .18s ease;">bancodechoices@gmail.com</a></li>
        </ul>
      </div>

    </div>

    <div style="border-top: 1px solid rgba(15,23,42,.08); padding-top: 22px; text-align: center; color: #9ca3af; font-size: .82rem;">
      © {{ date('Y') }} {{ __('landing.footer.copyright') }}
    </div>
  </div>
</footer>
