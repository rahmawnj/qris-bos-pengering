<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alur Integrasi - QRIS Bos Pengering</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        code, pre { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">

@if(!session('api_docs_authenticated'))
    <div class="flex items-center justify-center min-h-screen px-4 bg-slate-900">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">Alur Integrasi</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan kunci akses yang sama dengan API Documentation</p>
            </div>

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm mb-6">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('api-docs.auth') }}" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="redirect_to" value="{{ request()->getRequestUri() }}">
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Kunci Akses</label>
                    <input type="password" name="password" id="password" required autofocus
                           placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-800 placeholder-slate-400">
                </div>
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200">
                    Buka Dokumentasi Alur
                </button>
            </form>
        </div>
    </div>
@else
    @php
        $apiTab = fn (string $tab) => route('api-docs.index', ['tab' => $tab]);
        $adminPanel = [
            'devices' => route('admin.devices.index'),
            'qrisTransactions' => route('admin.transactions.qris'),
            'manualTransactions' => route('admin.transactions.manual'),
            'bypassLogs' => route('admin.bypass.logs'),
            'owners' => route('admin.owners.index'),
        ];
        $partnerPanel = [
            'devices' => route('partner.device.list'),
            'cashierPayment' => route('partner.cashier.payment.create'),
            'serviceOrders' => route('partner.service-orders.list'),
            'qrisTransactions' => route('partner.transactions.qris'),
            'manualTransactions' => route('partner.transactions.manual'),
            'bypassLogs' => route('partner.bypass.logs'),
            'brandProfile' => route('partner.brand.profile.edit'),
        ];
    @endphp

    <header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 px-6 py-4">
        <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-200">
                    B
                </div>
                <div>
                    <h1 class="text-lg font-bold text-slate-900 leading-tight">Bos Pengering</h1>
                    <p class="text-xs text-slate-500 font-medium">Dokumentasi Alur Integrasi</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('api-docs.index') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg transition-all">
                    API Docs
                </a>
                <a href="{{ route('api-docs.logout') }}"
                   class="inline-flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-lg transition-all">
                    Kunci Kembali
                </a>
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-6 py-10 space-y-10">
        <section class="border-b border-slate-200 pb-8">
            <p class="text-xs font-bold uppercase tracking-wider text-indigo-600 mb-3">Flow Documentation</p>
            <h2 class="text-3xl md:text-4xl font-extrabold text-slate-950">Peta alur integrasi QRIS, bypass, drop-off, dan payment gateway.</h2>
            <p class="mt-3 text-slate-600 max-w-3xl text-base md:text-lg">
                Halaman ini menjelaskan urutan proses bisnisnya. Jika sebuah langkah memakai API, tombol di langkah tersebut akan membuka halaman API Docs pada tab endpoint yang sesuai, lengkap dengan debugger.
            </p>
        </section>

        <section class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="#alur-qris" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:border-indigo-200 hover:shadow-md transition">
                <p class="text-xs font-bold text-purple-600 uppercase tracking-wider">Payment</p>
                <h3 class="font-extrabold text-slate-900 mt-1">Alur Pembayaran QRIS</h3>
                <p class="text-sm text-slate-500 mt-2">Request QR, webhook gateway, polling status, lalu aktivasi mesin.</p>
            </a>
            <a href="#alur-bypass" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:border-indigo-200 hover:shadow-md transition">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Bypass</p>
                <h3 class="font-extrabold text-slate-900 mt-1">Alur Bypass</h3>
                <p class="text-sm text-slate-500 mt-2">Admin/kasir mengaktifkan bypass, IoT mengambil status, lalu status dikonsumsi.</p>
            </a>
            <a href="#alur-dropoff" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm hover:border-indigo-200 hover:shadow-md transition">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Operational</p>
                <h3 class="font-extrabold text-slate-900 mt-1">Alur Drop-off</h3>
                <p class="text-sm text-slate-500 mt-2">Order manual dari kasir membuat sesi mesin yang kemudian diambil oleh IoT.</p>
            </a>
        </section>

        <section id="alur-qris" class="space-y-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-purple-600">Flow 01</p>
                <h2 class="text-2xl font-extrabold text-slate-950">Alur Pembayaran QRIS</h2>
                <p class="text-slate-600 mt-2">Digunakan ketika mesin/aplikasi meminta QR dinamis untuk pembayaran layanan seperti washer atau dryer.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm divide-y divide-slate-100 overflow-hidden">
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-extrabold w-fit">Step 1</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Client meminta QRIS baru</h3>
                        <p class="text-sm text-slate-600 mt-1">Client mengirim nominal, tipe layanan, device code, dan optional provider. Untuk debugging default bisa pakai `DEV-WHNTZR` dan amount `11`.</p>
                        <code class="inline-block mt-2 text-xs bg-slate-100 px-2 py-1 rounded">POST /api/qr-request</code>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('qr-request') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Buka Tester API</a>
                        <a href="{{ $partnerPanel['devices'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Panel Device</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-extrabold w-fit">Step 2</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Payment gateway mengirim webhook</h3>
                        <p class="text-sm text-slate-600 mt-1">Gateway mengirim status pembayaran. Sistem mendeteksi provider, memvalidasi payload, lalu update transaksi menjadi sukses/pending/fail sesuai parser provider.</p>
                        <code class="inline-block mt-2 text-xs bg-slate-100 px-2 py-1 rounded">POST /api/payment-status-update</code>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('payment-status-update') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Buka Tester API</a>
                        <a href="{{ $adminPanel['qrisTransactions'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Panel Admin QRIS</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-extrabold w-fit">Step 3</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Client atau IoT melakukan polling status</h3>
                        <p class="text-sm text-slate-600 mt-1">Jika client menyimpan `order_id`, gunakan payment-check. Jika IoT hanya tahu device dan service type, gunakan payment-check-2.</p>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <code class="text-xs bg-slate-100 px-2 py-1 rounded">GET /api/payment-check</code>
                            <code class="text-xs bg-slate-100 px-2 py-1 rounded">GET /api/payment-check-2</code>
                        </div>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('payment-check') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Order ID</a>
                        <a href="{{ $apiTab('payment-check-2') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Device</a>
                        <a href="{{ $partnerPanel['qrisTransactions'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Panel QRIS</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-purple-50 text-purple-700 text-xs font-extrabold w-fit">Step 4</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Sesi aktivasi dikonsumsi satu kali</h3>
                        <p class="text-sm text-slate-600 mt-1">Saat pembayaran sukses terdeteksi, sistem menghapus QR image, menandai sesi sebagai sudah dipakai, dan IoT menjalankan mesin sesuai service type.</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur-bypass" class="space-y-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-amber-600">Flow 02</p>
                <h2 class="text-2xl font-extrabold text-slate-950">Alur Bypass</h2>
                <p class="text-slate-600 mt-2">Dipakai saat admin/kasir perlu menyalakan layanan tanpa pembayaran gateway, misalnya uji mesin atau koreksi operasional.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm divide-y divide-slate-100 overflow-hidden">
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-extrabold w-fit">Step 1</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Admin/kasir set status bypass</h3>
                        <p class="text-sm text-slate-600 mt-1">Sistem menyimpan status layanan, waktu aktivasi, dan catatan bypass pada device.</p>
                        <code class="inline-block mt-2 text-xs bg-slate-100 px-2 py-1 rounded">POST /api/devices/{device}/update-status</code>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('update-status') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Buka Tester API</a>
                        <a href="{{ $adminPanel['devices'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Admin Device</a>
                        <a href="{{ $partnerPanel['devices'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Partner Device</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-extrabold w-fit">Step 2</span>
                    <div>
                        <h3 class="font-bold text-slate-950">IoT polling status device</h3>
                        <p class="text-sm text-slate-600 mt-1">IoT mengirim device code dan service type. Jika ada aktivasi valid dalam 24 jam, API mengembalikan status layanan.</p>
                        <code class="inline-block mt-2 text-xs bg-slate-100 px-2 py-1 rounded">GET /api/check-device</code>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('check-device') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Buka Tester API</a>
                        <a href="{{ $adminPanel['bypassLogs'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Bypass Logs</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-amber-50 text-amber-700 text-xs font-extrabold w-fit">Step 3</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Bypass dicatat dan dimatikan</h3>
                        <p class="text-sm text-slate-600 mt-1">Setelah IoT mengambil aktivasi, sistem mencatat ke bypass record dan mengubah status asal menjadi tidak aktif agar tidak bisa dipakai berulang.</p>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $adminPanel['bypassLogs'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Admin Logs</a>
                        <a href="{{ $partnerPanel['bypassLogs'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Partner Logs</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="alur-dropoff" class="space-y-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Flow 03</p>
                <h2 class="text-2xl font-extrabold text-slate-950">Alur Drop-off</h2>
                <p class="text-slate-600 mt-2">Dipakai untuk order manual yang dibuat dari panel kasir/partner, lalu mesin dijalankan lewat sesi device transaction.</p>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl shadow-sm divide-y divide-slate-100 overflow-hidden">
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold w-fit">Step 1</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Kasir membuat transaksi manual/drop-off</h3>
                        <p class="text-sm text-slate-600 mt-1">Transaksi dibuat dari halaman internal. Sistem menyimpan detail layanan, device code, service type, estimasi selesai, dan status sesi aktif.</p>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $partnerPanel['cashierPayment'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Kasir Payment</a>
                        <a href="{{ $adminPanel['manualTransactions'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Admin Manual</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold w-fit">Step 2</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Operator memulai layanan</h3>
                        <p class="text-sm text-slate-600 mt-1">Panel service order mengisi `bypass_activation` untuk sesi drop-off. Setelah itu IoT bisa mengambil aktivasi lewat API check-device.</p>
                        <code class="inline-block mt-2 text-xs bg-slate-100 px-2 py-1 rounded">GET /api/check-device</code>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $apiTab('check-device') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Buka Tester API</a>
                        <a href="{{ $partnerPanel['serviceOrders'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Service Order</a>
                    </div>
                </div>
                <div class="p-5 grid md:grid-cols-[120px_1fr_auto] gap-4 items-start">
                    <span class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-extrabold w-fit">Step 3</span>
                    <div>
                        <h3 class="font-bold text-slate-950">Sesi drop-off dikonsumsi</h3>
                        <p class="text-sm text-slate-600 mt-1">API mengembalikan source `session`, lalu status device transaction diubah menjadi false agar satu sesi hanya menjalankan mesin satu kali.</p>
                    </div>
                    <div class="flex md:flex-col gap-2">
                        <a href="{{ $partnerPanel['manualTransactions'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Partner Manual</a>
                        <a href="{{ $partnerPanel['serviceOrders'] }}" class="text-sm font-bold text-slate-600 hover:text-slate-900">Service Order</a>
                    </div>
                </div>
            </div>
        </section>

        <section id="payment-gateway" class="space-y-5">
            <div>
                <p class="text-xs font-bold uppercase tracking-wider text-slate-500">Gateway Notes</p>
                <h2 class="text-2xl font-extrabold text-slate-950">Dokumentasi Payment Gateway</h2>
                <p class="text-slate-600 mt-2">Perbedaan utama provider ada pada cara pembuatan charge dan bentuk webhook. Endpoint aplikasi tetap sama untuk client.</p>
            </div>

            <div class="overflow-hidden border border-slate-200 rounded-2xl bg-white shadow-sm">
                <table class="w-full text-left border-collapse text-sm">
                    <thead class="bg-slate-50 border-b border-slate-200 text-slate-700">
                        <tr>
                            <th class="px-5 py-4 font-extrabold">Mode / Provider</th>
                            <th class="px-5 py-4 font-extrabold">Konfigurasi</th>
                            <th class="px-5 py-4 font-extrabold">Dampak Saldo</th>
                            <th class="px-5 py-4 font-extrabold">Menu Withdrawal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-600">
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <div class="font-extrabold text-slate-950">Midtrans Partner / Private</div>
                                <p class="mt-1 text-xs">Dipakai saat owner memakai akun pembayaran sendiri.</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                Owner harus memilih alur <code class="bg-slate-100 px-1 rounded">private</code> dan mengisi <code class="bg-slate-100 px-1 rounded">merchant_id</code>. Saat request memakai provider <code class="bg-slate-100 px-1 rounded">midtrans</code>, sistem mengarahkannya ke <code class="bg-slate-100 px-1 rounded">midtrans_partner</code>.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Saldo owner di aplikasi tidak ditambahkan, karena settlement masuk ke akun merchant milik owner.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Menu withdrawal disembunyikan karena dana tidak ditampung sebagai saldo aplikasi.
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <div class="font-extrabold text-slate-950">Midtrans General</div>
                                <p class="mt-1 text-xs">Dipakai saat provider QRIS di setting menggunakan Midtrans general.</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                Provider dipilih dari setting sistem dan menggunakan credential Midtrans milik aplikasi.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Saldo owner bertambah setelah transaksi sukses, karena dana dihitung sebagai saldo internal.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Menu withdrawal ditampilkan agar owner bisa menarik saldo dari aplikasi.
                            </td>
                        </tr>
                        <tr>
                            <td class="px-5 py-4 align-top">
                                <div class="font-extrabold text-slate-950">Xendit General</div>
                                <p class="mt-1 text-xs">Dipakai saat provider QRIS di setting menggunakan Xendit.</p>
                            </td>
                            <td class="px-5 py-4 align-top">
                                Provider dipilih dari setting sistem dan menggunakan credential Xendit milik aplikasi.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Saldo owner bertambah setelah transaksi sukses, sama seperti Midtrans general.
                            </td>
                            <td class="px-5 py-4 align-top">
                                Menu withdrawal ditampilkan agar owner bisa menarik saldo dari aplikasi.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
@endif

</body>
</html>
