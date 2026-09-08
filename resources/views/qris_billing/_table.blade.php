@php
    $showOwner = $showOwner ?? false;
    $canConfirm = $canConfirm ?? false;
    $actionRouteName = $actionRouteName ?? null;
    $actionLabel = $actionLabel ?? 'Detail';
    $actionIcon = $actionIcon ?? 'fa-search';
    $actionClass = $actionClass ?? 'btn-info';
    $showDeviceCount = $showDeviceCount ?? true;
@endphp

<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th width="1%">#</th>
                <th>Outlet</th>
                @if ($showOwner)
                    <th>Owner</th>
                @endif
                <th class="text-center">Periode</th>
                <th class="text-center">Tgl. Bayar</th>
                <th class="text-end">Perpanjangan</th>
                <th class="text-center">Aktif Sampai</th>
                <th class="text-center">Status</th>
                <th class="text-center">Bukti</th>
                <th width="10%" class="text-center">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($outlets as $outlet)
                @php
                    $payment = $outlet->billing_payment;
                    $statusClass = [
                        'paid' => 'success',
                        'pending' => 'warning text-dark',
                        'due' => 'danger',
                        'not_due' => 'info',
                    ][$outlet->billing_status] ?? 'secondary';
                    $statusLabel = [
                        'paid' => 'Aktif',
                        'pending' => 'Menunggu Konfirmasi',
                        'due' => 'Perlu Perpanjangan',
                        'not_due' => 'Aktif',
                    ][$outlet->billing_status] ?? ucfirst($outlet->billing_status);
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $outlet->outlet_name }}</div>
                        <div class="text-muted small">
                            {{ $outlet->code }}
                            @if ($showDeviceCount)
                                &middot; {{ $outlet->devices_count ?? 0 }} device
                            @endif
                        </div>
                    </td>
                    @if ($showOwner)
                        <td>
                            <div class="fw-bold">{{ $outlet->owner->brand_name ?? $outlet->owner->name ?? '-' }}</div>
                            <div class="text-muted small">{{ $outlet->owner->user->email ?? '-' }}</div>
                        </td>
                    @endif
                    <td class="text-center">
                        {{ $outlet->billing_period }}
                        @if (($outlet->billing_month_count ?? 0) > 1)
                            <div class="text-muted small">{{ $outlet->billing_month_count }} bulan</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($payment?->paid_at)
                            <div class="fw-bold text-dark">{{ $payment->paid_at->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ $payment->paid_at->format('H:i') }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="fw-bold">Rp {{ number_format($outlet->billing_amount, 0, ',', '.') }}</div>
                        @if (($outlet->billing_month_count ?? 0) > 1)
                            <div class="text-muted small">Total {{ $outlet->billing_month_count }} bulan</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if (in_array($outlet->billing_status, ['paid', 'not_due'], true))
                            {{ $outlet->billing_due_date ? $outlet->billing_due_date->format('d/m/Y') : '-' }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge bg-{{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-center">
                        @if ($payment?->proof_of_payment)
                            <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-outline-primary btn-xs">
                                <i class="fa fa-image me-1"></i> Lihat
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($actionRouteName)
                            @php
                                if ($payment && str_ends_with($actionRouteName, '.payment.show')) {
                                    $routeName = $actionRouteName;
                                    $routeParams = [$payment->id];
                                } elseif ($payment && $actionRouteName === 'admin.qris-billing.show') {
                                    $routeName = 'admin.qris-billing.payment.show';
                                    $routeParams = [$payment->id];
                                } else {
                                    $routeName = $actionRouteName;
                                    $routeParams = [$outlet, 'period' => $outlet->billing_action_period ?? $outlet->billing_period];
                                }
                            @endphp
                            <a href="{{ route($routeName, $routeParams) }}" class="btn {{ $actionClass }} btn-xs">
                                <i class="fa {{ $actionIcon }} me-1"></i> {{ $actionLabel }}
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ $showOwner ? 10 : 9 }}" class="text-center text-muted py-4">Tidak ada data perpanjangan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
