@if(Auth::guard('expert')->check())
<header class="top-header">
    <div class="profile-card" style="cursor:pointer">
        @if(Auth::guard('expert')->user()->expert_image)
        <img onerror="this.src='{{ getImage(null) }}'" src="{{ getImage(Auth::guard('expert')->user()->expert_image) }}" class="avatar" alt="Avatar" loading="lazy">
        @else
        <div class="avatar" style="background:#ddd"></div>
        @endif
        <div class="expert-info">
            <h2>{{ Auth::guard('expert')->user()->expert_name }}</h2>
            <span>{{ Auth::guard('expert')->user()->title ?? 'Expert' }}</span>
        </div>
    </div>

    <form method="POST" action="{{ route('expert.logout') }}">
        @csrf
        <button type="submit" class="logout-btn">Log Out</button>
    </form>
</header>
@endif