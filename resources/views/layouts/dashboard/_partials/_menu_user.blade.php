 @php
     $hide = true;
 @endphp
<div class="menu-header">Navigasi Admin</div>

<div class="menu-item {{ Request::is('admin/dashboard') ? 'active' : '' }}">
    <a href="{{ route('admin.dashboard') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-tachometer-alt"></i></div> {{-- Dashboard: speedometer/gauge --}}
        <div class="menu-text">Dashboard</div>
    </a>
</div>


<div class="menu-header">Transaksi & Keuangan Admin</div>
<div class="menu-item {{ Request::is('admin/transactions/all') ? 'active' : '' }}">
    <a href="{{ route('admin.transactions.index') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-list-alt"></i></div> {{-- Semua Transaksi: Daftar dengan alt --}}
        <div class="menu-text">Semua Transaksi</div>
    </a>
</div>
	@php
	    $adminQrisBillingDueCount = App\Models\QrisBillingPayment::where('status', 'pending')->count();
	@endphp
<div
    class="menu-item has-sub {{ Request::is('admin/transactions*') ? 'active' : '' }}">
    <a href="javascript:;" class="menu-link">
        <div class="menu-icon"><i class="fa fa-receipt"></i></div> {{-- Transaksi: Bukti/Struk --}}
        <div class="menu-text">Transaksi</div>
        <div class="menu-caret"></div>
    </a>
    <div class="menu-submenu">
        <div class="menu-item {{ Request::is('admin/transaction/qris') ? 'active' : '' }}">
            <a href="{{ route('admin.transactions.qris') }}" class="menu-link">
                <div class="menu-text">Self Service (QRIS)</div>
            </a>
        </div>

        <div class="menu-item {{ Request::is('admin/transaction/manual') ? 'active' : '' }}">
            <a href="{{ route('admin.transactions.manual') }}" class="menu-link">
                <div class="menu-text">Drop Off</div>
            </a>
        </div>
        
	        <div class="menu-item {{ Request::is('admin/transactions/verifications') ? 'active' : '' }}">
	            <a href="{{ route('admin.transactions.verifications') }}" class="menu-link">
	                <div class="menu-text">Verifikasi QRIS</div>
	            </a>
	        </div>
	    </div>
	</div>

<div class="menu-item has-sub {{ Request::is('admin/qris-billing*') ? 'active' : '' }}">
    <a href="javascript:;" class="menu-link">
        <div class="menu-icon"><i class="fa fa-file-invoice-dollar"></i></div>
        <div class="menu-text">
            Perpanjangan QRIS
            @if ($adminQrisBillingDueCount > 0)
                <span class="menu-label">{{ $adminQrisBillingDueCount }}</span>
            @endif
        </div>
        <div class="menu-caret"></div>
    </a>
    <div class="menu-submenu">
        <div class="menu-item {{ Request::is('admin/qris-billing') ? 'active' : '' }}">
            <a href="{{ route('admin.qris-billing.index') }}" class="menu-link">
                <div class="menu-text">
                    Perpanjangan QRIS
                    @if ($adminQrisBillingDueCount > 0)
                        <span class="menu-badge">{{ $adminQrisBillingDueCount }}</span>
                    @endif
                </div>
            </a>
        </div>
        <div class="menu-item {{ Request::is('admin/qris-billing/report') ? 'active' : '' }}">
            <a href="{{ route('admin.qris-billing.report') }}" class="menu-link">
                <div class="menu-text">Laporan Perpanjangan QRIS</div>
            </a>
        </div>
    </div>
</div>

@php
    $adminWithdrawalRequest = App\Models\Withdrawal::with('owner.user')
        ->where('status', 'pending')
        ->count();
@endphp
<div class="menu-item has-sub {{ Request::is('admin/withdrawal*') ? 'active' : '' }}">
    <a href="javascript:;" class="menu-link">
        <div class="menu-icon"><i class="fa fa-wallet"></i></div> {{-- Penarikan Dana: Dompet --}}
        <div class="menu-text">
            Penarikan Dana
            @if ($adminWithdrawalRequest > 0)
                <span class="menu-label">{{ $adminWithdrawalRequest }}</span>
            @endif
        </div>
        <div class="menu-caret"></div>
    </a>
    <div class="menu-submenu">
        <div
            class="menu-item {{ Request::is('admin/withdrawal/*') && !Request::is('admin/withdrawal/histories') && !Request::is('admin/withdrawal') ? 'active' : '' }}">
            <a href="{{ route('admin.withdrawal.list') }}" class="menu-link">
                <div class="menu-text">Permintaan Penarikan</div>
                @if ($adminWithdrawalRequest > 0)
   <div class="menu-badge">{{ $adminWithdrawalRequest }}</div>
                @endif
            </a>
        </div>
        <div class="menu-item {{ Request::is('admin/withdrawal/histories') ? 'active' : '' }}">
            <a href="{{ route('admin.withdrawal.histories') }}" class="menu-link">
                <div class="menu-text">Riwayat Penarikan</div>
            </a>
        </div>
    </div>
</div>

<div class="menu-header">Manajemen Pengguna Admin</div>
<div class="menu-item {{ Request::is('admin/accounts*') ? 'active' : '' }}">
    <a href="{{ route('admin.accounts.all') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-users"></i></div> {{-- Semua Akun: Beberapa orang --}}
        <div class="menu-text">Semua Akun</div>
    </a>
</div>
<div
    class="menu-item has-sub {{ Request::is('admin/cashiers*') || Request::is('admin/owners*') || Request::is('admin/users*') || Request::is('admin/members*') ? 'active' : '' }}">
    <a href="javascript:;" class="menu-link">
        <div class="menu-icon"><i class="fa fa-user-friends"></i></div> {{-- Pengguna: Sekelompok orang --}}
        <div class="menu-text">Pengguna</div>
        <div class="menu-caret"></div>
    </a>
    <div class="menu-submenu">

        <div class="menu-item {{ Request::is('admin/users*') ? 'active' : '' }}">
            <a href="{{ route('admin.users.index') }}" class="menu-link">
                <div class="menu-text">Admin</div>
            </a>
        </div>
        <div class="menu-item {{ Request::is('admin/owners*') ? 'active' : '' }}">
            <a href="{{ route('admin.owners.index') }}" class="menu-link">
                <div class="menu-text">Pemilik (Partner)</div>
            </a>
        </div>
        <div class="menu-item {{ Request::is('admin/cashiers*') ? 'active' : '' }}">
            <a href="{{ route('admin.cashiers.index') }}" class="menu-link">
                <div class="menu-text">Kasir</div>
            </a>
        </div>
        <div class="menu-item {{ Request::is('admin/members*') ? 'active' : '' }}">
            <a href="{{ route('admin.members.index') }}" class="menu-link">
                <div class="menu-text">Member</div>
            </a>
        </div>

    </div>
</div>
<div class="menu-header">Master Data Admin</div>
<div class="menu-item {{ Request::is('admin/outlets*') ? 'active' : '' }}">
    <a href="{{ route('admin.outlets.index') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-store-alt"></i></div> {{-- Outlet: Toko/store --}}
        <div class="menu-text">Outlet</div>
    </a>
</div>
<div class="menu-item {{ Request::is('admin/devices*') ? 'active' : '' }}">
    <a href="{{ route('admin.devices.index') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-microchip"></i></div> {{-- Perangkat: Microchip/gear --}}
        <div class="menu-text">Perangkat</div>
    </a>
</div>
<div class="menu-item {{ Request::is('admin/service_types*') ? 'active' : '' }}">
    <a href="{{ route('admin.service_types.index') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-tools"></i></div> {{-- Jenis Layanan: Perkakas --}}
        <div class="menu-text">Jenis Layanan</div>
    </a>
</div>
<div class="menu-item {{ Request::is('admin/addons*') ? 'active' : '' }}">
    <a href="{{ route('admin.addons.index') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-puzzle-piece"></i></div> {{-- Add-ons: Sudah cukup tepat --}}
        <div class="menu-text">Add-ons</div>
    </a>
</div>
<div class="menu-header">Log Aktivitas Admin</div>
<div class="menu-item {{ Request::is('admin/bypass/logs') ? 'active' : '' }}">
    <a href="{{ route('admin.bypass.logs') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-file-alt"></i></div>
        <div class="menu-text">Log Bypass</div>
    </a>
</div>

<div class="menu-header">Pengaturan Sistem</div>
<div class="menu-item {{ Request::is('admin/setting*') ? 'active' : '' }}">
    <a href="{{ route('admin.setting.form') }}" class="menu-link">
        <div class="menu-icon"><i class="fa fa-cog"></i></div>
        <div class="menu-text">Pengaturan Sistem</div>
    </a>
</div>
