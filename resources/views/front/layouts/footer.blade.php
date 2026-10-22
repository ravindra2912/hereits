<!-- Footer -->
<footer class="mt-auto modern-tech-footer pt-5 pb-4">
  <div class="container">
    <div class="row g-4 pb-4">
      <div class="col-lg-4 col-md-6">
        <div class="mb-3">
          <img src="{{ config('const.site_setting.logo-bg-white', config('const.site_setting.logo')) }}" alt="Logo" class="img-fluid" style="max-height: 42px;" loading="lazy">
        </div>
        <p class="text-white-50 mb-4 pe-lg-4" style="font-size: 0.92rem; line-height: 1.6;">
          Connecting people with trusted local businesses, healthcare specialists, salons, and retail shops. Designed for modern discovery and seamless commerce.
        </p>
        <div class="d-flex align-items-center gap-2">
          <span class="location-beacon me-1"></span>
          <span class="small text-white-50">Local Commerce & Appointment Engine</span>
        </div>
      </div>

      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.8px;">Company</h6>
        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
          <li class="mb-2"><a href="{{ route('aboutUs') }}">About Us</a></li>
          <li class="mb-2"><a href="{{ route('contactUs') }}">Contact Us</a></li>
          <li class="mb-2"><a href="{{ route('blog.index') }}">Blogs</a></li>
          <li class="mb-2"><a href="{{ route('why-join-with-us') }}">Why Join With Us</a></li>
          <li class="mb-2"><a href="{{ route('register.business') }}" class="text-primary fw-bold">List Your Business</a></li>
          <li class="mb-2"><a href="{{ route('termAndCondition') }}">Terms & Conditions</a></li>
          <li class="mb-2"><a href="{{ route('privacyPolicy') }}">Privacy Policy</a></li>
          <li class="mb-2"><a href="{{ route('VendorPolicy') }}">Vendor Policy</a></li>
        </ul>
      </div>

      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.8px;">Support</h6>
        <ul class="list-unstyled mb-0" style="font-size: 0.9rem;">
          <li class="mb-2"><a href="{{ route('faq') }}">FAQ</a></li>
          <li class="mb-2"><a href="{{ route('CancellationAndRefundPolicy') }}">Refund Policy</a></li>
          <li class="mb-2"><a href="{{ route('categories-list') }}">All Categories</a></li>
          <li class="mb-2"><a href="{{ route('business-list') }}">Explore Stores</a></li>
        </ul>
      </div>

      <div class="col-lg-4 col-md-6">
        <h6 class="text-white fw-bold mb-3 text-uppercase" style="font-size: 0.78rem; letter-spacing: 0.8px;">Connect With Us</h6>
        <p class="text-white-50 small mb-3">Stay updated with latest stores, announcements, and local community updates.</p>
        <div class="d-flex gap-2 mt-2">
          @if(config('const.social_links.facebook'))
          <a href="{{ config('const.social_links.facebook') }}" class="modern-social-btn" title="Facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
          @endif
          @if(config('const.social_links.twitter'))
          <a href="{{ config('const.social_links.twitter') }}" class="modern-social-btn" title="Twitter" target="_blank"><i class="fab fa-twitter"></i></a>
          @endif
          @if(config('const.social_links.instagram'))
          <a href="{{ config('const.social_links.instagram') }}" class="modern-social-btn" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
          @endif
          @if(config('const.social_links.linkedin'))
          <a href="{{ config('const.social_links.linkedin') }}" class="modern-social-btn" title="LinkedIn" target="_blank"><i class="fab fa-linkedin-in"></i></a>
          @endif
          @if(config('const.social_links.youtube'))
          <a href="{{ config('const.social_links.youtube') }}" class="modern-social-btn" title="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
          @endif
        </div>
      </div>
    </div>

    <div class="pt-4 mt-2 border-top border-secondary border-opacity-25 d-flex flex-column flex-md-row justify-content-between align-items-center gap-2 text-white-50 small">
      <div>
        &copy; {{ date('Y') }} <span class="text-white fw-semibold">{{ config('app.name') }}</span>. All rights reserved.
      </div>
      <div class="d-flex gap-3">
        <a href="{{ route('privacyPolicy') }}" class="text-white-50">Privacy</a>
        <a href="{{ route('termAndCondition') }}" class="text-white-50">Terms</a>
        <a href="{{ route('contactUs') }}" class="text-white-50">Help</a>
      </div>
    </div>
  </div>
</footer>