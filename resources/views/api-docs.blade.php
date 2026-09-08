<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dokumentasi API - QRIS & Device Bypass</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js CDN for interactive tabs/sidebar and copy function -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        code, pre {
            font-family: 'JetBrains Mono', monospace;
        }
    </style>
    <script>
	        function apiDocsPage() {
	            const apiUrl = (path) => `${window.location.origin}${path}`;
	            const serviceTypes = @json($serviceTypes ?? []);
            const fallbackServiceTypes = serviceTypes.length
                ? serviceTypes
                : [{ label: 'Washer', value: 'washer' }, { label: 'Dryer', value: 'dryer' }];
            const defaultServiceType = fallbackServiceTypes[0]?.value || 'washer';

            return {
	                sidebarOpen: false,
	                activeTab: 'overview',
	                serviceTypes: fallbackServiceTypes,
	                apiUrl,
	                init() {
                    const requestedTab = new URLSearchParams(window.location.search).get('tab');

                    if (requestedTab === 'overview' || this.testers[requestedTab]) {
                        this.activeTab = requestedTab;
                    }
                },
                testers: {
	                    'check-device': {
	                        title: 'Test Check Device',
	                        method: 'GET',
	                        url: apiUrl('/api/check-device'),
                        params: [
                            { name: 'device_code', value: 'DEV-WHNTZR' },
                            { name: 'service_type', value: defaultServiceType, options: fallbackServiceTypes },
                        ],
                        body: '',
                        loading: false,
                        result: null,
                    },
	                    'update-status': {
	                        title: 'Test Update Status Device',
	                        method: 'POST',
	                        url: apiUrl('/api/devices/1/update-status'),
                        params: [],
                        serviceTypeBodyField: 'device_status',
                        selectedServiceType: defaultServiceType,
                        body: JSON.stringify({
                            device_status: defaultServiceType,
                            bypass_note: 'Debug bypass dari halaman dokumentasi'
                        }, null, 2),
                        loading: false,
                        result: null,
                    },
	                    'qr-request': {
	                        title: 'Test QR Request',
	                        method: 'POST',
	                        url: apiUrl('/api/qr-request'),
                        params: [],
                        serviceTypeBodyField: 'type',
                        selectedServiceType: defaultServiceType,
                        body: JSON.stringify({
                            amount: 11,
                            type: defaultServiceType,
                            device_code: 'DEV-WHNTZR',
                        }, null, 2),
                        loading: false,
                        result: null,
                    },
	                    'payment-check': {
	                        title: 'Test Payment Check',
	                        method: 'GET',
	                        url: apiUrl('/api/payment-check'),
                        params: [
                            { name: 'order_id', value: '' },
                        ],
                        body: '',
                        loading: false,
                        result: null,
                    },
	                    'payment-check-2': {
	                        title: 'Test Payment Check 2',
	                        method: 'GET',
	                        url: apiUrl('/api/payment-check-2'),
                        params: [
                            { name: 'service_type', value: defaultServiceType, options: fallbackServiceTypes },
                            { name: 'device_code', value: 'DEV-WHNTZR' },
                        ],
                        body: '',
                        loading: false,
                        result: null,
                    },
	                    'payment-status-update': {
	                        title: 'Test Payment Status Update',
	                        method: 'POST',
	                        url: apiUrl('/api/payment-status-update'),
                        params: [],
                        body: JSON.stringify({
                            order_id: 'WASHER-OUT01-0000000000000TEST',
                            transaction_status: 'settlement',
                            gross_amount: '11.00',
                            signature_key: 'debug-signature'
                        }, null, 2),
	                        loading: false,
	                        result: null,
	                    },
	                },
		                normalizeTesterUrl(url) {
		                    const trimmedUrl = (url || '').trim();

	                    if (/^https?:\/\//i.test(trimmedUrl)) {
	                        return trimmedUrl;
	                    }

	                    if (trimmedUrl.startsWith('/')) {
	                        return `${window.location.origin}${trimmedUrl}`;
	                    }

	                    return `http://${trimmedUrl}`;
		                },
                get tester() {
                    return this.testers[this.activeTab] || null;
                },
                syncTesterServiceType() {
                    const tester = this.tester;

                    if (!tester?.serviceTypeBodyField) {
                        return;
                    }

                    try {
                        const payload = tester.body.trim() ? JSON.parse(tester.body) : {};
                        payload[tester.serviceTypeBodyField] = tester.selectedServiceType;
                        tester.body = JSON.stringify(payload, null, 2);
                    } catch (error) {
                        tester.result = {
                            ok: false,
                            status: 'ERROR',
                            statusText: 'Invalid JSON',
                            elapsedMs: 0,
                            url: tester.url,
                            body: 'Body JSON belum valid, jadi service type tidak bisa disinkronkan: ' + error.message,
                        };
                    }
                },
                async submitTester() {
                    const tester = this.tester;

                    if (!tester) {
                        return;
                    }

                    tester.loading = true;
                    tester.result = null;
                    this.syncTesterServiceType();

                    try {
	                        const requestUrl = new URL(this.normalizeTesterUrl(tester.url));

                        tester.params.forEach((param) => {
                            if (param.name && param.value !== '') {
                                requestUrl.searchParams.set(param.name, param.value);
                            }
                        });

                        const options = {
                            method: tester.method,
                            headers: {
                                Accept: 'application/json',
                            },
                        };

                        if (tester.method !== 'GET') {
                            let parsedBody = {};

                            if (tester.body.trim() !== '') {
                                parsedBody = JSON.parse(tester.body);
                            }

                            options.headers['Content-Type'] = 'application/json';
                            options.body = JSON.stringify(parsedBody);
                        }

                        const startedAt = performance.now();
                        const response = await fetch(requestUrl.toString(), options);
                        const elapsedMs = Math.round(performance.now() - startedAt);
                        const contentType = response.headers.get('content-type') || '';
                        const payload = contentType.includes('application/json')
                            ? await response.json()
                            : await response.text();

                        tester.result = {
                            ok: response.ok,
                            status: response.status,
                            statusText: response.statusText,
                            elapsedMs,
                            url: requestUrl.toString(),
                            body: payload,
                        };
                    } catch (error) {
                        tester.result = {
                            ok: false,
                            status: 'ERROR',
                            statusText: error.name || 'Request Error',
                            elapsedMs: 0,
                            url: tester.url,
                            body: error.message,
                        };
                    } finally {
                        tester.loading = false;
                    }
                },
                prettyResult(result) {
                    if (!result) {
                        return '';
                    }

                    return typeof result.body === 'string'
                        ? result.body
                        : JSON.stringify(result.body, null, 2);
                },
            };
        }
    </script>
</head>
<body class="bg-slate-50 text-slate-800 leading-relaxed min-h-screen">

@if(!session('api_docs_authenticated'))
    <!-- LOCK SCREEN VIEW -->
    <div class="flex items-center justify-center min-h-screen px-4 bg-slate-900">
        <div class="w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden border border-slate-100 p-8">
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-indigo-50 text-indigo-600 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-slate-800">API Documentation</h2>
                <p class="text-sm text-slate-500 mt-1">Masukkan kunci akses untuk membuka dokumentasi</p>
            </div>

            @if(session('error'))
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-lg text-sm mb-6 flex items-start gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('api-docs.auth') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Kunci Akses</label>
                    <input type="password" name="password" id="password" required autofocus
                           placeholder="••••••••"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-slate-800 placeholder-slate-400">
                </div>
                <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5">
                    Buka Kunci Dokumen
                </button>
            </form>

            <div class="mt-8 text-center text-xs text-slate-400">
                &copy; {{ date('Y') }} QRIS - Bos Pengering. All rights reserved.
            </div>
        </div>
    </div>
@else
    <!-- MAIN DOCUMENTATION VIEW -->
    <div x-data="apiDocsPage()" class="min-h-screen flex flex-col">
        <!-- HEADER -->
        <header class="sticky top-0 z-40 bg-white border-b border-slate-200/80 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden text-slate-600 hover:text-slate-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-indigo-200">
                        B
                    </div>
                    <div>
                        <h1 class="text-lg font-bold text-slate-900 leading-tight">Bos Pengering</h1>
                        <p class="text-xs text-slate-500 font-medium">API Documentation June 2026</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-4">

                <a href="{{ route('api-docs.flows') }}"
                   class="hidden md:inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-3 py-2 rounded-lg transition-all">
                    Alur Integrasi
                </a>
                <a href="{{ route('api-docs.logout') }}"
                   class="flex items-center gap-1.5 text-xs font-semibold text-rose-600 hover:text-rose-700 bg-rose-50 hover:bg-rose-100 px-3 py-2 rounded-lg transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Keluar
                </a>
            </div>
        </header>

        <div class="flex flex-1 relative">
            <!-- SIDEBAR -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                   class="fixed lg:sticky top-[69px] bottom-0 left-0 z-30 w-72 bg-white border-r border-slate-200 overflow-y-auto transition-transform duration-300 ease-in-out">
                <nav class="p-6 space-y-7">
                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Memulai</h3>
                        <div class="space-y-1">
                            <button @click="activeTab = 'overview'; sidebarOpen = false"
                                    :class="activeTab === 'overview' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center gap-2.5 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Ringkasan & Base URL
                            </button>
                        </div>
                    </div>

                    <div>
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Endpoint API</h3>
                        <div class="space-y-1.5">
                             <button @click="activeTab = 'update-status'; sidebarOpen = false"
                                    :class="activeTab === 'update-status' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">POST</span>
                                    update-status
                                </span>
                            </button>

                            <button @click="activeTab = 'check-device'; sidebarOpen = false"
                                    :class="activeTab === 'check-device' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">GET</span>
                                    check-device
                                </span>
                            </button>

<hr>

                            <button @click="activeTab = 'qr-request'; sidebarOpen = false"
                                    :class="activeTab === 'qr-request' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">POST</span>
                                    qr-request
                                </span>
                            </button>

                            <button @click="activeTab = 'payment-check'; sidebarOpen = false"
                                    :class="activeTab === 'payment-check' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">GET</span>
                                    payment-check
                                </span>
                            </button>

                            <button @click="activeTab = 'payment-check-2'; sidebarOpen = false"
                                    :class="activeTab === 'payment-check-2' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-blue-100 text-blue-700">GET</span>
                                    payment-check-2
                                </span>
                            </button>

                            <button @click="activeTab = 'payment-status-update'; sidebarOpen = false"
                                    :class="activeTab === 'payment-status-update' ? 'bg-indigo-50 text-indigo-700 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900'"
                                    class="w-full text-left px-3 py-2 rounded-lg text-sm flex items-center justify-between transition-all">
                                <span class="flex items-center gap-2.5 truncate">
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">POST</span>
                                    payment-status-update
                                </span>
                            </button>
                        </div>
                    </div>
                </nav>
            </aside>

            <!-- BACKDROP FOR MOBILE SIDEBAR -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false"
                 class="fixed inset-0 z-20 bg-slate-900/40 lg:hidden transition-opacity"></div>

            <!-- MAIN CONTENT AREA -->
            <main class="flex-1 min-w-0 p-6 md:p-10 max-w-5xl">

                <!-- TAB: OVERVIEW -->
                <div x-show="activeTab === 'overview'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <h2 class="text-3xl font-extrabold text-slate-900">Ringkasan Integrasi API</h2>
                        <p class="text-slate-600 mt-2 text-lg">Dokumentasi resmi integrasi IoT mesin, bypass status, dan gateway pembayaran QRIS Bos Pengering.</p>
                    </div>

                    <!-- BASE URL CARD -->
                    <div class="bg-gradient-to-r from-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-xl relative overflow-hidden">
                        <div class="absolute right-0 top-0 translate-x-12 -translate-y-12 w-64 h-64 bg-indigo-500/10 rounded-full blur-2xl"></div>
                        <h3 class="text-xs font-bold uppercase tracking-wider text-indigo-300">Base URL Server</h3>
                        <div class="mt-3 flex flex-wrap items-center gap-3">
                            <span class="bg-indigo-500/20 text-indigo-200 font-mono text-lg md:text-xl px-4 py-2 rounded-xl border border-indigo-500/30 font-semibold select-all">
                                https://qris.laundrytapkartu.com
                            </span>
                            <button onclick="navigator.clipboard.writeText('https://qris.laundrytapkartu.com'); alert('Base URL disalin!')"
                                    class="bg-white/10 hover:bg-white/20 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                Salin
                            </button>
                        </div>
                    </div>

                    <!-- SUMMARY LIST -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Daftar Endpoint API</h3>
                        <div class="overflow-hidden border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-4">Fungsi Utama</th>
                                        <th class="px-6 py-4">Method</th>
                                        <th class="px-6 py-4">Endpoint</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'check-device'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Pengecekan Status & Bypass (IoT)</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700">GET</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/check-device</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'update-status'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Trigger Bypass Manual (Admin/Kasir)</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700">POST</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/devices/{device}/update-status</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'qr-request'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Request Pembuatan QRIS Pembayaran</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700">POST</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/qr-request</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'payment-check'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Polling Status (Berdasarkan Order ID)</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700">GET</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/payment-check</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'payment-check-2'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Polling Status (Berdasarkan Mesin/IoT)</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-blue-50 text-blue-700">GET</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/payment-check-2</td>
                                    </tr>
                                    <tr class="hover:bg-slate-50/50 cursor-pointer" @click="activeTab = 'payment-status-update'">
                                        <td class="px-6 py-4 font-semibold text-slate-900">Callback Webhook Payment Gateway</td>
                                        <td class="px-6 py-4"><span class="px-2 py-1 rounded-md text-xs font-bold bg-purple-50 text-purple-700">POST</span></td>
                                        <td class="px-6 py-4 font-mono text-slate-500">/api/payment-status-update</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB: CHECK DEVICE -->
                <div x-show="activeTab === 'check-device'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-blue-50 text-blue-700 mb-3">GET</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Pengecekan Status Device (IoT Polling)</h2>
                        <p class="text-slate-600 mt-2">Dijalankan oleh mikrokontroler IoT untuk memeriksa adanya perintah bypass aktif dalam kurun waktu 24 jam terakhir.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/check-device')"></span>
                        </div>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Parameter Query</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Nama Parameter</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">device_code</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Kode unik mesin terdaftar (misal: `DEV001`).</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">service_type</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Opsional</span></td>
                                        <td class="px-6 py-4 text-slate-700">Jenis layanan spesifik (misal: `washer`, `dryer`). Membantu pembagian aktivasi saat kode device dibagi.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- BUSINESS LOGIC -->
                    <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-6 text-amber-900 space-y-3">
                        <h4 class="font-bold flex items-center gap-2 text-amber-800">
                            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                            Alur Khusus & Aturan Konsumsi Sesi (One-Time Bypass)
                        </h4>
                        <ul class="list-disc list-inside space-y-1.5 text-sm text-amber-950/80">
                            <li><strong>Verifikasi Perpanjangan QRIS:</strong> Jika perpanjangan QRIS outlet pemilik mesin sudah jatuh tempo (`has_overdue_billing` = true), server membalas dengan status <code class="bg-amber-100 px-1 rounded">status_device: "off"</code> dan <code class="bg-amber-100 px-1 rounded">source: "qris_billing"</code>.</li>
                            <li><strong>Pencarian Sesi Aktif:</strong> Sistem mencari data bypass aktif 24 jam terakhir dari 3 sumber:
                                <ul class="list-decimal list-inside pl-5 mt-1 space-y-1">
                                    <li>`bypass` (Device Manual Bypass): `device_status` berubah dan status di database bukan `'off'`.</li>
                                    <li>`session` (Drop-off Aktif): Pesanan dropship kasir (`DeviceTransaction` bernilai true).</li>
                                    <li>`qris_bypass` (Bypass dari QRIS): Transaksi sukses melalui bypass manual oleh admin.</li>
                                </ul>
                            </li>
                            <li><strong>Konsumsi Langsung:</strong> Jika aktivasi ditemukan, server segera menonaktifkan status di database untuk mencegah penggunaan ganda oleh IoT pada request berikutnya.</li>
                        </ul>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- B1 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">A. Berhasil - Ada Aktivasi Terdeteksi</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "status_device": "washer",
  "source": "qris_bypass",
  "activation_date": "2026-06-19 14:30:00",
  "message": "Status diterima"
}</code></pre>
                            </div>
                            <!-- B2 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">B. Berhasil - Tidak Ada Aktivasi (Mesin Off)</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "status_device": "off",
  "source": null,
  "activation_date": null,
  "message": "Status diterima"
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: UPDATE STATUS -->
                <div x-show="activeTab === 'update-status'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-purple-50 text-purple-700 mb-3">POST</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Bypass / Update Status Device Manual</h2>
                        <p class="text-slate-600 mt-2 font-medium">Digunakan oleh panel admin atau sistem kasir untuk menyalakan bypass atau mengubah status mesin secara langsung.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/devices/{device}/update-status')"></span>
                        </div>
                        <div class="mt-2 flex flex-wrap gap-3 text-xs font-bold">
                            <a href="{{ route('api-docs.index', ['tab' => 'update-status']) }}" class="text-indigo-600 hover:text-indigo-700">Buka tab ini langsung</a>
                            <a :href="apiUrl('/api/devices/1/update-status')" target="_blank" rel="noopener" class="text-slate-600 hover:text-slate-900">Buka URL sample</a>
                            <a href="{{ route('api-docs.flows') }}#alur-bypass" class="text-slate-600 hover:text-slate-900">Lihat alur bypass</a>
                        </div>
                        <p class="text-xs text-slate-500 mt-1.5">Ganti `{device}` di URL dengan ID numerik database milik mesin tersebut. Endpoint ini memakai method POST, jadi untuk test gunakan Debug Request di bawah.</p>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Parameter Body (Form / JSON)</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Nama Parameter</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">device_status</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Status bypass target (misal: `'washer'`, `'dryer'`, `'off'`).</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">bypass_note</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Catatan/Alasan bypass diaktifkan (misal: `'Bypass manual uji coba mesin'`).</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="space-y-2">
                            <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800 max-w-lg"><code>{
  "status": "success",
  "message": "Device DEV-01 berhasil diperbarui menjadi \"washer\""
}</code></pre>
                        </div>
                    </div>
                </div>

                <!-- TAB: QR REQUEST -->
                <div x-show="activeTab === 'qr-request'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-purple-50 text-purple-700 mb-3">POST</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Request Pembuatan QRIS</h2>
                        <p class="text-slate-600 mt-2 font-medium">Digunakan oleh web kasir atau aplikasi laundry untuk mengajukan pembuatan barcode pembayaran QRIS dinamis.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/qr-request')"></span>
                        </div>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Parameter Body (Form / JSON)</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Nama Parameter</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">amount</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">integer</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Nominal pembayaran dalam Rupiah. Minimal senilai **10** rupiah.</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">type</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Nama/tipe layanan mesin (contoh: `'washer'`, `'dryer'`).</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">device_code</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Kode unik mesin tempat transaksi dituju (contoh: `'DEV001'`).</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">provider</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Opsional</span></td>
                                        <td class="px-6 py-4 text-slate-700">Payment Gateway target (contoh: `'midtrans'`, `'xendit'`). Menggunakan nilai default di sistem jika kosong.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SYSTEM NOTES -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 text-indigo-900 space-y-3">
                        <h4 class="font-bold flex items-center gap-2 text-indigo-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Logika Bisnis Khusus (Private Merchant & QR Template)
                        </h4>
                        <ul class="list-disc list-inside space-y-1.5 text-sm text-indigo-950/80">
                            <li><strong>Logika Akun Privat Owner:</strong> Untuk provider Midtrans, jika owner outlet mengatur tipe akun pembayaran mereka (`payment_account_type`) ke `'owner'`, sistem otomatis mengalihkan proses pembayaran ke `'midtrans_partner'` untuk didaftarkan di bawah ID Merchant khusus owner tersebut.</li>
                            <li><strong>Desain Gambar QRIS:</strong> Gambar QR Code yang dihasilkan tidak polos, tetapi digabungkan dengan background resmi (`assets/img/qristempl.jpg`) yang mencantumkan nama outlet di bagian bawah dan nominal terformat di bagian atas.</li>
                        </ul>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="space-y-2">
                            <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800 max-w-xl"><code>{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "transaction_id": "MID-REF-99887766",
    "payment_status": "pending",
    "provider": "midtrans_partner",
    "api_url": "https://api.sandbox.midtrans.com/v2/qris/...",
    "qr_image": "https://qris.laundrytapkartu.com/storage/qrcodes/WASHER-OUT01-1689000000123ABCD.jpg",
    "expiresAt": "2026-06-19T14:45:00+07:00"
  }
}</code></pre>
                        </div>
                    </div>
                </div>

                <!-- TAB: PAYMENT CHECK -->
                <div x-show="activeTab === 'payment-check'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-blue-50 text-blue-700 mb-3">GET</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Cek Status Pembayaran (Berdasarkan Order ID)</h2>
                        <p class="text-slate-600 mt-2 font-medium">Digunakan untuk polling status pembayaran berdasarkan Order ID yang didapat dari request QRIS.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/payment-check')"></span>
                        </div>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Parameter Query</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Nama Parameter</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">order_id</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">ID Pesanan transaksi QRIS yang ingin diperiksa status lunasnya.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- POST-SUCCESS ACTIONS -->
                    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 text-emerald-950 space-y-3">
                        <h4 class="font-bold flex items-center gap-2 text-emerald-900">
                            <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Tindakan Pasca Transaksi Sukses
                        </h4>
                        <p class="text-sm">Ketika pembayaran dideteksi sukses oleh sistem, server secara otomatis akan menjalankan langkah berikut:</p>
                        <ul class="list-disc list-inside space-y-1 text-xs text-emerald-900/80">
                            <li><strong>Penghapusan QR Code:</strong> Gambar QR terkait di storage publik dihapus demi menghemat space server.</li>
                            <li><strong>Konsumsi Sesi:</strong> Sesi device transaction diubah statusnya menjadi <code class="bg-emerald-100 px-1 rounded">status: false</code> dan tanggal aktivasi diisi waktu sekarang.</li>
                            <li><strong>Pembaruan Status Bypass:</strong> Jika merupakan bypass, data status bypass QRIS diperbarui menjadi `'activated'`.</li>
                        </ul>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- B1 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">A. Response Transaksi Lunas / Sukses</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "success",
    "device_status": true,
    "qr_code_deleted": true,
    "description": "Pembayaran Berhasil."
  }
}</code></pre>
                            </div>
                            <!-- B2 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider block">B. Response Masih Belum Lunas / Pending</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "pending",
    "qr_code_deleted": false,
    "description": "Pembayaran tidak berhasil."
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: PAYMENT CHECK 2 -->
                <div x-show="activeTab === 'payment-check-2'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-blue-50 text-blue-700 mb-3">GET</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Cek Status Pembayaran (Device-Centric Polling)</h2>
                        <p class="text-slate-600 mt-2 font-medium">Polling alternatif yang dipanggil oleh IoT dengan hanya mengirimkan kode mesin serta layanan untuk mencari transaksi sukses terbaru yang belum diproses dalam 1 jam terakhir.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/payment-check-2')"></span>
                        </div>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Parameter Query</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Nama Parameter</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">service_type</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Jenis layanan mesin terdaftar (misal: `'washer'`, `'dryer'`).</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">device_code</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-rose-50 text-rose-700">Wajib</span></td>
                                        <td class="px-6 py-4 text-slate-700">Kode unik mesin yang dicek lunas transaksinya.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- B1 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">A. Response Pembayaran Ditemukan & Sukses</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "message": {
    "order_id": "WASHER-OUT01-1689000000123ABCD",
    "payment_status": "success",
    "device_status": true,
    "amount": 15000,
    "description": "Pembayaran Berhasil."
  }
}</code></pre>
                            </div>
                            <!-- B2 -->
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider block">B. Response Transaksi Belum Ada/Lunas</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "error",
  "message": {
    "order_id": null,
    "description": "Order not found."
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TAB: PAYMENT STATUS UPDATE -->
                <div x-show="activeTab === 'payment-status-update'" class="space-y-8 animate-fadeIn">
                    <div class="border-b border-slate-200 pb-6">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-extrabold bg-purple-50 text-purple-700 mb-3">POST</span>
                        <h2 class="text-3xl font-extrabold text-slate-900">Callback Status Pembayaran</h2>
                        <p class="text-slate-600 mt-2 font-medium">Endpoint webhook untuk menerima notifikasi status pembayaran dari payment gateway dan memperbarui transaksi QRIS secara otomatis.</p>
                    </div>

                    <div>
                        <h3 class="text-sm font-bold uppercase text-slate-400 tracking-wider mb-2.5">Endpoint URL</h3>
                        <div class="bg-slate-900 text-slate-100 px-4 py-3 rounded-xl font-mono text-sm break-all select-all flex justify-between items-center border border-slate-800">
                            <span x-text="apiUrl('/api/payment-status-update')"></span>
                        </div>
                    </div>

                    <!-- PARAMETERS -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-3">Payload Body (JSON)</h3>
                        <div class="overflow-x-auto border border-slate-200 rounded-xl bg-white shadow-sm">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead class="bg-slate-50 border-b border-slate-200 font-bold text-slate-700">
                                    <tr>
                                        <th class="px-6 py-3.5">Field</th>
                                        <th class="px-6 py-3.5">Tipe Data</th>
                                        <th class="px-6 py-3.5">Status</th>
                                        <th class="px-6 py-3.5">Deskripsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 text-slate-600">
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">order_id</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Gateway</span></td>
                                        <td class="px-6 py-4 text-slate-700">ID pesanan transaksi. Sistem juga bisa membaca dari field gateway seperti <code>qr_code.external_id</code>, <code>qr_code.reference_id</code>, <code>reference_id</code>, atau <code>data.reference_id</code>.</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">transaction_status</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Gateway</span></td>
                                        <td class="px-6 py-4 text-slate-700">Status dari Midtrans atau Midtrans Partner. Dipakai untuk mengenali provider dan menentukan status akhir transaksi.</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">qr_code / data</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">object</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Gateway</span></td>
                                        <td class="px-6 py-4 text-slate-700">Payload Xendit QRIS. Sistem membaca bentuk payload ini untuk mengenali provider Xendit secara otomatis.</td>
                                    </tr>
                                    <tr>
                                        <td class="px-6 py-4 font-mono font-semibold text-indigo-600">signature_key</td>
                                        <td class="px-6 py-4 font-mono text-slate-500">string</td>
                                        <td class="px-6 py-4"><span class="px-2 py-0.5 rounded text-xs font-bold bg-slate-100 text-slate-500">Gateway</span></td>
                                        <td class="px-6 py-4 text-slate-700">Signature dari Midtrans untuk validasi keamanan webhook.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- SYSTEM NOTES -->
                    <div class="bg-indigo-50 border border-indigo-100 rounded-2xl p-6 text-indigo-900 space-y-3">
                        <h4 class="font-bold flex items-center gap-2 text-indigo-800">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>
                            Cara Sistem Memproses Callback
                        </h4>
                        <ul class="list-disc list-inside space-y-1.5 text-sm text-indigo-950/80">
                            <li>Endpoint ini auto-detect provider berdasarkan transaksi tersimpan atau bentuk payload gateway.</li>
                            <li>Provider yang didukung: <code class="bg-indigo-100 px-1 rounded">xendit</code>, <code class="bg-indigo-100 px-1 rounded">midtrans</code>, dan <code class="bg-indigo-100 px-1 rounded">midtrans_partner</code>.</li>
                            <li>Jika transaksi ditemukan, sistem memfinalisasi transaksi berdasarkan <code class="bg-indigo-100 px-1 rounded">order_id</code>, status gateway, nominal, dan reference gateway.</li>
                            <li>Route ini dikecualikan dari CSRF agar bisa dipanggil langsung oleh payment gateway.</li>
                        </ul>
                    </div>

                    <!-- RESPONSE SAMPLES -->
                    <div>
                        <h3 class="text-lg font-bold text-slate-900 mb-4">Contoh Response</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-emerald-600 uppercase tracking-wider block">A. Callback Berhasil Diproses</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "success",
  "message": "Transaction updated successfully"
}</code></pre>
                            </div>
                            <div class="space-y-2">
                                <span class="text-xs font-semibold text-rose-600 uppercase tracking-wider block">B. Provider Tidak Terdeteksi</span>
                                <pre class="bg-slate-900 text-slate-100 p-4 rounded-xl text-xs overflow-x-auto border border-slate-800"><code>{
  "status": "error",
  "message": "Payment provider could not be resolved."
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API DEBUGGER -->
                <div x-show="tester" class="mt-10 border-t border-slate-200 pt-8 animate-fadeIn">
                    <form @submit.prevent="submitTester()" class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Debug Request</p>
                                <h3 class="text-lg font-extrabold text-slate-900" x-text="tester?.title"></h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-2.5 py-1 rounded-md text-xs font-extrabold"
                                      :class="tester?.method === 'GET' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700'"
                                      x-text="tester?.method"></span>
                                <button type="submit"
                                        class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-bold text-white shadow-sm transition hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        :disabled="tester?.loading">
                                    <svg x-show="!tester?.loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14m-7-7l7 7-7 7"></path>
                                    </svg>
                                    <svg x-show="tester?.loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                    </svg>
                                    <span x-text="tester?.loading ? 'Mengirim...' : 'Submit Test'"></span>
                                </button>
                            </div>
                        </div>

                        <div class="p-6 space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-slate-700 mb-2">URL Request</label>
                                <input type="url"
                                       x-model="tester.url"
                                       class="w-full rounded-xl border border-slate-200 bg-white px-4 py-3 font-mono text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                            </div>

                            <template x-if="tester.params.length">
                                <div>
                                    <h4 class="text-sm font-bold text-slate-700 mb-3">Query Parameter</h4>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <template x-for="param in tester.params" :key="param.name">
                                            <label class="block">
                                                <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1" x-text="param.name"></span>
                                                <template x-if="param.options">
                                                    <select x-model="param.value"
                                                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-mono text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                                        <template x-for="option in param.options" :key="option.value">
                                                            <option :value="option.value" x-text="option.label + ' (' + option.value + ')'"></option>
                                                        </template>
                                                    </select>
                                                </template>
                                                <template x-if="!param.options">
                                                    <input type="text"
                                                           x-model="param.value"
                                                           class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-mono text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                                </template>
                                            </label>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="tester.method !== 'GET'">
                                <div class="space-y-4">
                                    <template x-if="tester.serviceTypeBodyField">
                                        <label class="block">
                                            <span class="block text-xs font-bold uppercase tracking-wider text-slate-400 mb-1" x-text="tester.serviceTypeBodyField"></span>
                                            <select x-model="tester.selectedServiceType"
                                                    @change="syncTesterServiceType()"
                                                    class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 font-mono text-sm text-slate-800 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100">
                                                <template x-for="option in serviceTypes" :key="option.value">
                                                    <option :value="option.value" x-text="option.label + ' (' + option.value + ')'"></option>
                                                </template>
                                            </select>
                                        </label>
                                    </template>
                                    <div>
                                        <div class="mb-2 flex items-center justify-between gap-3">
                                            <label class="text-sm font-bold text-slate-700">Body JSON</label>
                                            <span class="text-xs text-slate-400">Editable</span>
                                        </div>
                                        <textarea x-model="tester.body"
                                                  rows="9"
                                                  spellcheck="false"
                                                  class="w-full resize-y rounded-xl border border-slate-200 bg-slate-950 px-4 py-3 font-mono text-xs leading-relaxed text-slate-100 outline-none transition focus:border-indigo-500 focus:ring-2 focus:ring-indigo-100"></textarea>
                                    </div>
                                </div>
                            </template>

                            <template x-if="tester.result">
                                <div class="border border-slate-200 rounded-xl overflow-hidden">
                                    <div class="flex flex-col gap-2 border-b border-slate-200 bg-slate-50 px-4 py-3 md:flex-row md:items-center md:justify-between">
                                        <div class="flex flex-wrap items-center gap-2">
                                            <span class="px-2.5 py-1 rounded-md text-xs font-extrabold"
                                                  :class="tester.result.ok ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'"
                                                  x-text="tester.result.status + ' ' + tester.result.statusText"></span>
                                            <span class="px-2.5 py-1 rounded-md bg-slate-100 text-xs font-bold text-slate-600"
                                                  x-text="tester.result.elapsedMs + ' ms'"></span>
                                        </div>
                                        <button type="button"
                                                class="text-xs font-bold text-indigo-600 hover:text-indigo-700"
                                                @click="navigator.clipboard.writeText(prettyResult(tester.result))">
                                            Salin Response
                                        </button>
                                    </div>
                                    <div class="border-b border-slate-200 bg-white px-4 py-3">
                                        <p class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Final URL</p>
                                        <p class="break-all font-mono text-xs text-slate-600" x-text="tester.result.url"></p>
                                    </div>
                                    <pre class="max-h-[420px] overflow-auto bg-slate-950 p-4 text-xs leading-relaxed text-slate-100"><code x-text="prettyResult(tester.result)"></code></pre>
                                </div>
                            </template>
                        </div>
                    </form>
                </div>

            </main>
        </div>
    </div>
@endif

<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.25s ease-out forwards;
    }
</style>
</body>
</html>
