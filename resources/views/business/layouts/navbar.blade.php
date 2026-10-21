@php
$businessFrontUrl = session('business_front_url');

@endphp
<nav class="navbar navbar-light border-bottom shadow-sm">
    <div class="container-fluid">
        <button class="btn btn-primary" id="menu-toggle"><i class="bi bi-list"></i></button>

        <div class="ms-auto d-flex align-items-center">
            <ul class="navbar-nav flex-row align-items-center">
                <li class="nav-item me-3">
                    <a class="btn btn-sm btn-view-store d-flex align-items-center gap-2 px-3 py-1.5 rounded-pill shadow-sm" href="{{ $businessFrontUrl ?? '#' }}" target="_blank" title="View Store">
                        <i class="bi bi-shop fs-6"></i>
                        <span class="d-none d-sm-inline">View Store</span>
                    </a>
                </li>

                <li class="nav-item me-3">
                    <button class="btn btn-link nav-link p-0" id="dark-mode-toggle">
                        <i class="bi bi-moon fs-5"></i>
                    </button>
                </li>

                <li class="nav-item ms-3">
                    <a class="nav-link p-0 text-danger" href="{{ route('business.logout') }}">
                        <i class="bi bi-box-arrow-right fs-5"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>