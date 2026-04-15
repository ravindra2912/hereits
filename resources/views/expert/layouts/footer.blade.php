@if(Auth::guard('expert')->check())
<nav class="bottom-nav">
    <a href="{{ route('expert.dashboard') }}" class="nav-item {{ Route::is('expert.dashboard') ? 'active' : '' }}">
        <i class="bi {{ Route::is('expert.dashboard') ? 'bi-house-fill' : 'bi-house-door' }}"></i>
        <span>Home</span>
    </a>
    <a href="{{ route('expert.appointments.create') }}" class="nav-item {{ Route::is('expert.appointments.create') ? 'active' : '' }}">
        <i class="bi bi-plus-circle{{ Route::is('expert.appointments.create') ? '-fill' : '' }}"></i>
        <span>Create</span>
    </a>
    <a href="{{ route('expert.history') }}" class="nav-item {{ Route::is('expert.history') ? 'active' : '' }}">
        <i class="bi {{ Route::is('expert.history') ? 'bi-clock-history' : 'bi-clock' }}"></i>
        <span>History</span>
    </a>
    <a href="{{ route('expert.profile.edit') }}" class="nav-item {{ Route::is('expert.profile.edit') ? 'active' : '' }}">
        <i class="bi {{ Route::is('expert.profile.edit') ? 'bi-person-fill' : 'bi-person' }}"></i>
        <span>Profile</span>
    </a>
</nav>
@endif