<div class="table-responsive">
    <table class="table table-striped table-bordered align-middle mb-0">
        <thead>
            <tr>
                <th width="1%">#</th>
                <th>Outlet</th>
                <th>Owner</th>
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
            @forelse ($payments as $payment)
                @php
                    $outlet = $payment->outlet;
                    $periodStart = $payment->period_start;
                    $periodEnd = $payment->period_end;
                    $period = $periodStart && $periodEnd && $periodStart->format('Y-m-d') !== $periodEnd->format('Y-m-d')
                        ? $periodStart->translatedFormat('d F Y') . ' - ' . $periodEnd->translatedFormat('d F Y')
                        : (($periodEnd ?? $periodStart)?->translatedFormat('d F Y') ?? '-');
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <div class="fw-bold text-dark">{{ $outlet->outlet_name ?? '-' }}</div>
                        <div class="text-muted small">{{ $outlet->code ?? '-' }}</div>
                    </td>
                    <td>
                        <div class="fw-bold">{{ $outlet->owner->brand_name ?? $outlet->owner->name ?? '-' }}</div>
                        <div class="text-muted small">{{ $outlet->owner->user->email ?? '-' }}</div>
                    </td>
                    <td class="text-center">{{ $period }}</td>
                    <td class="text-center">
                        @if ($payment->paid_at)
                            <div class="fw-bold text-dark">{{ $payment->paid_at->format('d/m/Y') }}</div>
                            <div class="text-muted small">{{ $payment->paid_at->format('H:i') }}</div>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-end">
                        <div class="fw-bold">Rp {{ number_format($payment->amount, 0, ',', '.') }}</div>
                    </td>
                    <td class="text-center">
                        {{ $payment->period_end?->format('d/m/Y') ?? '-' }}
                    </td>
                    <td class="text-center">
                        <span class="badge bg-success">Aktif</span>
                    </td>
                    <td class="text-center">
                        @if ($payment->proof_of_payment)
                            <a href="{{ asset('storage/' . $payment->proof_of_payment) }}" target="_blank" class="btn btn-outline-primary btn-xs">
                                <i class="fa fa-image me-1"></i> Lihat
                            </a>
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <a href="{{ route('admin.qris-billing.payment.show', ['payment' => $payment->id]) }}" class="btn btn-info btn-xs">
                            <i class="fa fa-search me-1"></i> Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">Tidak ada data perpanjangan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
