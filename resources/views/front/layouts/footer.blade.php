<!-- Footer -->
<footer class="mt-auto">
  <div class="container">
    <div class="row g-4">
      <div class="col-lg-4 col-md-6">
        <img src="{{ config('const.site_setting.logo') }}" alt="Logo" class="img-fluid" style="max-height: 40px;" loading="lazy">
        <p class="text-white-50">Empowering businesses with the right tools to grow and succeed online.</p>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white mb-3">Company</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="{{ route('aboutUs') }}">About Us</a></li>
          <li class="mb-2"><a href="{{ route('contactUs') }}">Contact Us</a></li>
          <li class="mb-2"><a href="{{ route('blog.index') }}">Blogs</a></li>
          <li class="mb-2"><a href="{{ route('why-join-with-us') }}" class="text-primary fw-bold">List Your Business</a></li>
          <li class="mb-2"><a href="{{ route('termAndCondition') }}">Terms & Conditions</a></li>
          <li class="mb-2"><a href="{{ route('privacyPolicy') }}">Privacy Policy</a></li>
          <li class="mb-2"><a href="{{ route('VendorPolicy') }}">Vendor Policy</a></li>
        </ul>
      </div>
      <div class="col-lg-2 col-md-3 col-6">
        <h6 class="text-white mb-3">Support</h6>
        <ul class="list-unstyled">
          <li class="mb-2"><a href="{{ route('faq') }}">FAQ</a></li>
          <li class="mb-2"><a href="{{ route('CancellationAndRefundPolicy') }}">Refund Policy</a></li>
          <li class="mb-2"><a href="#">Find Experts</a></li> {{-- TODO: Fix route to finding experts --}}
        </ul>
      </div>
      <div class="col-lg-4 col-md-6">
        <h6 class="text-white mb-3">Follow Us</h6>
        <div class="d-flex gap-3 mt-3">
          @if(config('const.social_links.facebook'))
          <a href="{{ config('const.social_links.facebook') }}" class="social-icon-btn" title="Facebook" target="_blank"><i class="fab fa-facebook-f"></i></a>
          @endif
          @if(config('const.social_links.twitter'))
          <a href="{{ config('const.social_links.twitter') }}" class="social-icon-btn" title="Twitter" target="_blank"><i class="fab fa-twitter"></i></a>
          @endif
          @if(config('const.social_links.instagram'))
          <a href="{{ config('const.social_links.instagram') }}" class="social-icon-btn" title="Instagram" target="_blank"><i class="fab fa-instagram"></i></a>
          @endif
          @if(config('const.social_links.linkedin'))
          <a href="{{ config('const.social_links.linkedin') }}" class="social-icon-btn" title="LinkedIn" target="_blank"><i class="fab fa-linkedin-in"></i></a>
          @endif
          @if(config('const.social_links.youtube'))
          <a href="{{ config('const.social_links.youtube') }}" class="social-icon-btn" title="YouTube" target="_blank"><i class="fab fa-youtube"></i></a>
          @endif
        </div>
      </div>
    </div>
    <hr class="border-secondary my-4">
    <div class="text-center text-white-50 small">
      &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
    </div>
  </div>
</footer>