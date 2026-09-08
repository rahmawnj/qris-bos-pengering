<div class="menu-header">Navigasi</div>
<div class="menu-item {{ Request::is('partner/dashboard') ? 'active' : '' }}">
    <a href="{{ route('partner.dashboard') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-chart-line"></i></div>
        <div class="menu-text">Dashboard</div>
    </a>
</div>
<div class="menu-item {{ Request::is('profile') ? 'active' : '' }}">
    <a href="{{ route('profile.form') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-user"></i></div>
        <div class="menu-text">Profil</div>
    </a>
</div>
<div class="menu-item {{ Request::is('partner/outlets/list') ? 'active' : '' }}">
    <a href="{{ route('partner.outlets.list') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-store"></i></div>
        <div class="menu-text">Daftar Outlet</div>
    </a>
</div>
