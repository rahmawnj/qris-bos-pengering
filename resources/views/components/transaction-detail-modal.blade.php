@props(['transaction'])

@php
    $timezoneMap = [
        'wib' => 'Asia/Jakarta',
        'wita' => 'Asia/Makassar',
        'wit' => 'Asia/Jayapura',
    ];
    $tzKey = strtolower($transaction->timezone);
    $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';

    // Define badge classes based on status and type
    $statusBadgeClass = '';
    switch ($transaction->status) {
        case 'pending':
            $statusBadgeClass = 'badge-pending';
            break;
        case 'success':
            $statusBadgeClass = 'badge-success';
            break;
        case 'failed':
            $statusBadgeClass = 'badge-failed';
            break;
        default:
            $statusBadgeClass = 'bg-secondary'; // Fallback
            break;
    }

    $typeBadgeClass = '';
    switch ($transaction->type) {
        case 'qris':
            $typeBadgeClass = 'badge-qris';
            break;
        case 'manual':
            $typeBadgeClass = 'badge-manual';
            break;
        case 'member':
            $typeBadgeClass = 'badge-member';
            break;
        default:
            $typeBadgeClass = 'bg-info'; // Fallback
            break;
    }
@endphp

<div class="modal fade" id="transactionDetailModal{{ $transaction->id }}" tabindex="-1"
    aria-labelledby="transactionDetailModalLabel{{ $transaction->id }}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionDetailModalLabel{{ $transaction->id }}">Detail Transaksi
                    #{{ $transaction->order_id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                {{-- Isi detail transaksi di sini --}}
                <dl class="row">
                    <dt class="col-sm-4">ID Nota:</dt>
                    <dd class="col-sm-8">{{ $transaction->order_id }}</dd>

                    <dt class="col-sm-4">Pemilik:</dt>
                    <dd class="col-sm-8">{{ $transaction->owner->user->name ?? '-' }}
                        ({{ $transaction->owner->brand_name ?? '-' }})
                    </dd>

                    <dt class="col-sm-4">Outlet:</dt>
                    <dd class="col-sm-8">{{ $transaction->outlet->outlet_name ?? '-' }}</dd>

                    <dt class="col-sm-4">Perangkat:</dt>
                    <dd class="col-sm-8">{{ $transaction->device_code ?? '-' }}</dd>

                    <dt class="col-sm-4">Total Jumlah:</dt>
                    <dd class="col-sm-8">
                        Rp{{ number_format($transaction->amount, 0, ',', '.') }}</dd>

                    <dt class="col-sm-4">Jenis Transaksi:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-status {{ $typeBadgeClass }}">
                            {{ $transaction->type === 'manual' ? 'Kasir' : ucfirst($transaction->type) }}
                        </span>
                    </dd>

                    @if ($transaction->type === 'qris' && $transaction->qrisTransaction)
                        <dt class="col-sm-4">Provider:</dt>
                        <dd class="col-sm-8">{{ $transaction->qris_provider_label }}</dd>
                    @endif

                    <dt class="col-sm-4">Status Transaksi:</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-status {{ $statusBadgeClass }}">
                            {{ ucfirst($transaction->status) }}
                        </span>
                    </dd>

                    <dt class="col-sm-4">Waktu Transaksi:</dt>
                    <dd class="col-sm-8">
                        {{ \Carbon\Carbon::parse($transaction->created_at)->setTimezone($tz)->format('d F Y H:i:s') }}
                        ({{ strtoupper($transaction->timezone) }})
                    </dd>

                    {{-- Contoh detail lebih lanjut untuk transaksi 'manual' --}}
                    @if ($transaction->type == 'manual' && $transaction->manualTransaction)
                        <dt class="col-sm-4">Metode Pembayaran:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->manualTransaction->payment_method == 'cash' ? 'Tunai' : ($transaction->manualTransaction->payment_method == 'non_cash' ? 'Non-Tunai' : '-') }}
                        </dd>
                        <dt class="col-sm-4">Nama Kasir:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->manualTransaction->cashier_name ?? '-' }}</dd>
                        <dt class="col-sm-4">Layanan Manual:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->manualTransaction->service->name ?? '-' }}
                            (Rp{{ number_format($transaction->manualTransaction->service_price ?? 0, 0, ',', '.') }})
                        </dd>
                        @if (!empty($transaction->manualTransaction->addons))
                            <dt class="col-sm-4">Tambahan:</dt>
                            <dd class="col-sm-8">
                                <ul>
                                    @foreach ((array) $transaction->manualTransaction->addons as $addon)
                                        <li>{{ $addon['name'] ?? '-' }}
                                            (Rp{{ number_format($addon['price'] ?? 0, 0, ',', '.') }})
                                        </li>
                                    @endforeach
                                </ul>
                            </dd>
                        @endif
                        <dt class="col-sm-4">Catatan:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->manualTransaction->notes ?? '-' }}</dd>
                    @endif

                    {{-- Contoh detail lebih lanjut untuk transaksi 'member' --}}
                    @if ($transaction->type == 'member' && $transaction->memberTransaction)
                        <dt class="col-sm-4">Nama Member:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->memberTransaction->member->user->name ?? '-' }}
                        </dd>
                        <dt class="col-sm-4">Email Member:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->memberTransaction->member->user->email ?? '-' }}
                        </dd>
                        <dt class="col-sm-4">Jenis Langganan:</dt>
                        <dd class="col-sm-8">
                            {{ $transaction->memberTransaction->subscription->name ?? '-' }}
                        </dd>
                    @endif
                </dl>

                {{-- Breakdown Layanan Perangkat (Non-Tabel) --}}
                @if ($transaction->deviceTransactions->isNotEmpty())
                    <h6 class="mt-4 border-top pt-3">Detail Layanan Perangkat:</h6>
                    <div class="row">
                        @foreach ($transaction->deviceTransactions as $dt)
                            <div class="col-md-6 mb-3">
                                <div class="card card-body p-3">
                                    <h7 class="card-title text-primary mb-1">{{ $dt->service_type }}</h7>
                                    <p class="card-text text-sm mb-1">
                                        Status Mesin: {{$dt->device_code}}
                                        <span class="badge bg-{{ $dt->status == 0 ? 'success' : 'warning' }}">
                                            {{ $dt->status == 0 ? 'Sudah Dijalankan' : 'Belum Dijalankan' }}
                                        </span>
                                    </p>
                                    <p class="card-text text-sm mb-1">
                                        Waktu Kadaluarsa: {{ $dt->activated_at ? \Carbon\Carbon::parse($dt->activated_at)->setTimezone($tz)->format('d M H:i:s') : '-' }}
                                    </p>
                                    {{-- Jika bypass_activation relevan untuk ditampilkan --}}
                                    @if ($dt->bypass_activation)
                                        <p class="card-text text-sm mb-1">
                                            Bypass Aktivasi: {{ \Carbon\Carbon::parse($dt->bypass_activation)->setTimezone($tz)->format('d M H:i:s') }}
                                        </p>
                                    @endif
                                    @if ($dt->notes)
                                        <p class="card-text text-sm mb-0">Catatan: {{ $dt->notes }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted mt-4 border-top pt-3">Tidak ada detail layanan perangkat untuk transaksi ini.</p>
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary"
                    data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
