<div id="header" class="app-header app-header-inverse">
    <div class="navbar-header">
        {{-- Menggunakan variabel yang di-inject dari app.blade.php --}}
        <a href="{{ route('home') }}" class="navbar-brand">
            <img height="25" style="margin-right: 5px; border-radius: 10%;" src="{{ $logoSrc }}" alt="Brand Logo">
            <b>{{ $brandName }}</b> <br>
            {{-- CEK: Guard WEB dan Role MERCHANT --}}
            {{-- @if (Auth::guard('web')->check() && auth()->user()->role == 'merchant' && auth()->user()->merchantAdmin)
            <p style="font-size: 10px; margin-top: 5px; margin-bottom: 0;">
                {{ auth()->user()->merchantAdmin->name }}
            </p>
            @endif --}}
        </a>

        <button type="button" class="navbar-mobile-toggler" data-toggle="app-sidebar-mobile">
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <div class="navbar-nav">

        {{-- FORM UBAH STATUS TOKO KHUSUS MERCHANT (GUARD WEB) --}}
        @if (Auth::guard('web')->check() && auth()->user()->role == 'merchant' && auth()->user()->merchantAdmin)
        <div class="navbar-item d-flex align-items-center">
            <div class="form-check form-switch">
                <label class="form-check-label text-white" for="merchant-status-toggle" id="status-label">
                    <span class="badge bg-secondary">Loading...</span>
                </label>
                <input class="form-check-input" type="checkbox" id="merchant-status-toggle">

            </div>
        </div>
        @endif
        {{-- END FORM UBAH STATUS TOKO --}}

        {{-- ... Kode Notifikasi (Opsional) ... --}}

        <div class="navbar-item navbar-user dropdown">
            <a href="#" class="navbar-link dropdown-toggle d-flex align-items-center" data-bs-toggle="dropdown">
                {{-- Menggunakan Auth::user() karena middleware sudah menjamin user ada --}}
                <img src="{{
                    $authImage }}" alt="" />

                <div class="d-none d-md-inline text-start ms-2">
                    <div>{{ $authName }}</div>
                    <small class="text-white">{{ $authRole }}</small>
                </div>
                <b class="caret ms-6px"></b>
            </a>

            <div class="dropdown-menu dropdown-menu-end me-1">
                <a href="{{ route('profile.form') }}" class="dropdown-item">Account</a>

                @if (session('impersonating'))
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('admin.impersonate.stop') }}" method="POST" class="px-3 py-1">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100">Kembali ke Admin</button>
                    </form>
                @endif

                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="dropdown-item">Log Out</button>
                </form>
            </div>
        </div>
    </div>
</div>
