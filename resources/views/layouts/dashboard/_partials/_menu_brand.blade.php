 @php
     $hide = true;
 @endphp
<div class="menu-header">Navigasi Mitra</div>


                 <div class="menu-item {{ Request::is('partner/dashboard') ? 'active' : '' }}">
                     <a href="{{ route('partner.dashboard') }}" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-chart-line"></i></div> {{-- Dashboard: Grafik garis --}}
                         <div class="menu-text">Dashboard</div>
                     </a>
                 </div>

                 <div
                     class="menu-item has-sub {{ Request::is('partner/device*') || Request::is('partner/outlets*') || Request::is('partner/addons*') ? 'active' : '' }}">
                     <a href="javascript:;" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-cogs"></i></div> {{-- Manajemen: Gerigi --}}
                         <div class="menu-text">Manajemen</div>
                         <div class="menu-caret"></div>
                     </a>
                     <div class="menu-submenu">
                         <div class="menu-item {{ Request::is('partner/device/list') ? 'active' : '' }}">
                             <a href="{{ route('partner.device.list') }}" class="menu-link">
                                 <div class="menu-text">Daftar Perangkat</div>
                             </a>
                         </div>
                         <div class="menu-item {{ Request::is('partner/addons*') ? 'active' : '' }}">
                             <a href="{{ route('partner.addons.index') }}" class="menu-link">
                                 <div class="menu-text">Manajemen Add-ons</div>
                             </a>
                         </div>
                     </div>
                 </div>
                 @php
	                 @endphp
	                 <div class="menu-header">Pembayaran</div>
	                 <div
	                     class="menu-item has-sub {{ Request::is('partner/qris-transaction*') || Request::is('partner/transactions/qris*') || Request::is('partner/transactions/verifications*') || Request::is('partner/qris-billing*') ? 'active' : '' }}">
                     <a href="javascript:;" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-qrcode"></i></div> {{-- Self Service - QRIS: QR Code --}}
		                         <div class="menu-text">Self Service - QRIS</div>
	                         <div class="menu-caret"></div>
	                     </a>
	                     <div class="menu-submenu">
		                         <div class="menu-item {{ Request::is('partner/transactions/qris') || Request::is('partner/qris-transaction') ? 'active' : '' }}">
		                             <a href="{{ route('partner.transactions.qris') }}" class="menu-link">
		                                 <div class="menu-text">Riwayat Transaksi QRIS</div>
	                             </a>
	                         </div>
		                         <div class="menu-item {{ Request::is('partner/transactions/verifications') ? 'active' : '' }}">
	                             <a href="{{ route('partner.transactions.verifications') }}" class="menu-link">
		                                 <div class="menu-text">Pending & Verifikasi</div>
	                             </a>
                         </div>
                     </div>
                 </div>



                 @if ($hide == false)
                     <div
                         class="menu-item has-sub {{ Request::is('partner/members*') || Request::is('partner/topup*') || Request::is('partner/member-transaction') ? 'active' : '' }}">
                         <a href="javascript:;" class="menu-link">
                             <div class="menu-icon"><i class="fa fa-users"></i></div> {{-- Self Service - Member: Orang banyak --}}
                             <div class="menu-text">Self Service - Member</div>
                             <div class="menu-caret"></div>
                         </a>
                         <div class="menu-submenu">
                             <div class="menu-item {{ Request::is('partner/members/verified') ? 'active' : '' }}">
                                 <a href="{{ route('partner.members.verified') }}" class="menu-link">
                                     <div class="menu-text">Anggota Terverifikasi</div>
                                 </a>
                             </div>
                             <div class="menu-item {{ Request::is('partner/members/unverified') ? 'active' : '' }}">
                                 <a href="{{ route('partner.members.unverified') }}" class="menu-link">
                                     <div class="menu-text">Anggota Belum Terverifikasi</div>
                                 </a>
                             </div>
                             <div
                                 class="menu-item {{ Request::is('partner/topup') && !Request::is('partner/topup/histories') ? 'active' : '' }}">
                                 <a href="{{ route('partner.topup') }}" class="menu-link">
                                     <div class="menu-text">Isi Saldo Anggota</div>
                                 </a>
                             </div>
                             <div class="menu-item {{ Request::is('partner/topup/histories') ? 'active' : '' }}">
                                 <a href="{{ route('partner.topup.histories') }}" class="menu-link">
                                     <div class="menu-text">Riwayat Isi Saldo Anggota</div>
                                 </a>
                             </div>
                             <div class="menu-item {{ Request::is('partner/member-transaction') ? 'active' : '' }}">
                                 <a href="{{ route('partner.member.transactions') }}" class="menu-link">
                                     <div class="menu-text">Transaksi Anggota</div>
                                 </a>
                             </div>
                         </div>
                     </div>
                 @endif

                 @php
                     $finalStatuses = App\Constants\OrderStatus::finalStatuses();
                     $accessibleOutletIds = getData()->getOutletIds();
                     sort($accessibleOutletIds);
                     $serviceOrderCountCacheKey = 'service_order_count:' . (Auth::id() ?? 'guest') . ':' . md5(implode(',', $accessibleOutletIds));
                     $serviceOrderCount = \Illuminate\Support\Facades\Cache::remember(
                         $serviceOrderCountCacheKey,
                         now()->addSeconds(30),
                         fn () => getData()->transactions
                             ->pendingServiceOrders($finalStatuses)
                             ->count()
                     );
                     $totalNotifDropOff = $serviceOrderCount;
                 @endphp
                 <div
                     class="menu-item has-sub {{ Request::is('partner/manual-transactions*') || Request::is('partner/service-order*') || Request::is('partner/cashier-payment*') ? 'active' : '' }}">
                     <a href="javascript:;" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-hand-holding-usd"></i></div> {{-- Drop OFF: Tangan memegang uang --}}
                         <div class="menu-text">
                             Drop OFF
                             @if ($totalNotifDropOff > 0)
                                 <span class="menu-label">{{ $totalNotifDropOff }}</span>
                             @endif
                         </div>
                         <div class="menu-caret"></div>
                     </a>
                     <div class="menu-submenu">
                         <div class="menu-item {{ Request::is('partner/manual-transactions*') ? 'active' : '' }}">
                             <a href="{{ route('partner.transactions.manual') }}" class="menu-link">
                                 <div class="menu-text">Transaksi</div>
                             </a>
                         </div>

                         <div class="menu-item {{ Request::is('partner/cashier-payment*') ? 'active' : '' }}">
                             <a href="{{ route('partner.cashier.payment.create') }}" class="menu-link">
                                 <div class="menu-text">Pembayaran Kasir</div>
                             </a>
                         </div>

                         <div class="menu-item {{ Request::is('partner/service-order*') ? 'active' : '' }}">
                             <a href="{{ route('partner.service-orders.list') }}" class="menu-link">
                                 <div class="menu-text">Pesanan Layanan</div>
                                 @if ($serviceOrderCount > 0)
                                     <div class="menu-badge">{{ $serviceOrderCount }}</div>
                                 @endif
                             </a>
                         </div>

                     </div>
                 </div>


                 <div class="menu-header">Laporan & Aktivitas Mitra</div>


                 <div class="menu-item {{ Request::is('partner/transactions') ? 'active' : '' }}">
                     <a href="{{ route('partner.transactions.index') }}" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-chart-bar"></i></div> {{-- Semua Riwayat Transaksi: Bar chart --}}
                         <div class="menu-text">Semua Riwayat Transaksi</div>
                     </a>
                 </div>
                 <div class="menu-item {{ Request::is('partner/bypass/logs') ? 'active' : '' }}">
                     <a href="{{ route('partner.bypass.logs') }}" class="menu-link">
                         <div class="menu-icon"><i class="fa fa-clipboard-list"></i></div> {{-- Log Bypass: Daftar di clipboard --}}
                         <div class="menu-text">Log Bypass</div>
                     </a>
                 </div>

                 @if (Auth::user()->role === 'owner')
                     <div class="menu-header">Pengaturan Khusus Pemilik</div>

                     @if (Auth::user()->owner?->payment_account_type !== 'owner')
                         <div
                             class="menu-item has-sub {{ Request::is('partner/withdrawal*') ? 'active' : '' }}">
                             <a href="javascript:;" class="menu-link">
                                 <div class="menu-icon"><i class="fa fa-wallet"></i></div> {{-- Self Service - QRIS: QR Code --}}
                                 <div class="menu-text">Penarikan QRIS</div>
                                 <div class="menu-caret"></div>
                             </a>
                             <div class="menu-submenu">
                                 <div class="menu-item {{ Request::is('partner/withdrawal') ? 'active' : '' }}">
                                     <a href="{{ route('partner.withdrawal.request') }}" class="menu-link">
                                         <div class="menu-text">Permintaan Penarikan</div>
                                     </a>
                                 </div>
                                 <div class="menu-item {{ Request::is('partner/withdrawal/histories') ? 'active' : '' }}">
                                     <a href="{{ route('partner.withdrawal.histories') }}" class="menu-link">
                                         <div class="menu-text">Riwayat Penarikan</div>
                                     </a>
                                 </div>
                             </div>
                         </div>
                     @endif

	                     <div class="menu-item {{ Request::is('partner/qris-billing*') ? 'active' : '' }}">
	                         <a href="{{ route('partner.qris-billing.report') }}" class="menu-link">
	                             <div class="menu-icon"><i class="fa fa-file-invoice-dollar"></i></div>
	                             <div class="menu-text">Laporan Perpanjangan</div>
	                         </a>
	                     </div>

                     <div class="menu-item {{ Request::is('partner/cashiers*') ? 'active' : '' }}">
                         <a href="{{ route('partner.cashiers.list') }}" class="menu-link">
                             <div class="menu-icon"><i class="fa fa-cash-register"></i></div> {{-- Daftar Outlet: Toko --}}
                             <div class="menu-text">Daftar Kasir</div>
                         </a>
                     </div>
                     @php
                         $overdueBillingCount = 0;
                         if (Auth::user()->role === 'owner') {
                             $partnerOutletIds = getData()->getOutletIds();
                             $partnerOutlets = \App\Models\Outlet::with(['qrisBillingPayments'])
                                 ->whereIn('id', $partnerOutletIds)
                                 ->where('qris_billing_enabled', true)
                                 ->get();

                             foreach ($partnerOutlets as $pOutlet) {
                                 if ($pOutlet->qrisBillingUnpaidSummary()['count'] > 0) {
                                     $overdueBillingCount++;
                                 }
                             }
                         }
                     @endphp

                     <div class="menu-item {{ Request::is('partner/outlets*') ? 'active' : '' }}">
                         <a href="{{ route('partner.outlets.list') }}" class="menu-link">
                             <div class="menu-icon"><i class="fa fa-store"></i></div> {{-- Daftar Outlet: Toko --}}
                             <div class="menu-text">
                                 Daftar Outlet
                                 @if($overdueBillingCount > 0)
                                     <span class="text-danger ms-1" style="font-weight: 900;">!</span>
                                 @endif
                             </div>
                         </a>
                     </div>

                     <div class="menu-item {{ Request::is('partner/brand/profile*') ? 'active' : '' }}">
                         <a href="{{ route('partner.brand.profile.edit') }}" class="menu-link">
                             <div class="menu-icon"><i class="fa fa-building"></i></div> {{-- Profil Brand: Gedung/building --}}
                             <div class="menu-text">Profil Brand</div>
                         </a>
                     </div>

                     <div class="menu-item {{ Request::is('partner/receipt-config*') ? 'active' : '' }}">
                         <a href="{{ route('partner.receipt.config.edit') }}" class="menu-link">
                             <div class="menu-icon"><i class="fa fa-file-invoice"></i></div> {{-- Konfigurasi Struk: Faktur/invoice --}}
                             <div class="menu-text">Konfigurasi Struk</div>
                         </a>
                     </div>
                 @endif
