@php
    $showDue = $showDue ?? true;
    $totalLabel = $totalLabel ?? 'Total Outlet';
    $columnClass = $showDue ? 'col-md-2 col-6' : 'col-md-4 col-6';
@endphp

<div class="row mb-4">
    <div class="{{ $columnClass }} mb-3">
        <div class="qris-billing-summary">
            <div class="summary-icon bg-primary"><i class="fa fa-file-invoice-dollar"></i></div>
            <div>
                <div class="summary-label">{{ $totalLabel }}</div>
                <div class="summary-value">{{ $summary['total'] }}</div>
            </div>
        </div>
    </div>
    @if ($showDue)
        <div class="{{ $columnClass }} mb-3">
            <div class="qris-billing-summary">
                <div class="summary-icon bg-danger"><i class="fa fa-exclamation-triangle"></i></div>
                <div>
                    <div class="summary-label">Perlu Perpanjangan</div>
                    <div class="summary-value">{{ $summary['due'] }}</div>
                </div>
            </div>
        </div>
    @endif
    <div class="{{ $columnClass }} mb-3">
        <div class="qris-billing-summary">
            <div class="summary-icon bg-warning text-dark"><i class="fa fa-clock"></i></div>
            <div>
                <div class="summary-label">Pending</div>
                <div class="summary-value">{{ $summary['pending'] }}</div>
            </div>
        </div>
    </div>
    <div class="{{ $columnClass }} mb-3">
        <div class="qris-billing-summary">
            <div class="summary-icon bg-success"><i class="fa fa-check"></i></div>
            <div>
                <div class="summary-label">Aktif</div>
                <div class="summary-value">{{ $summary['paid'] }}</div>
            </div>
        </div>
    </div>
    @if ($showDue)
        <div class="col-md-4 col-12 mb-3">
            <div class="qris-billing-summary">
                <div class="summary-icon bg-info"><i class="fa fa-money-bill-wave"></i></div>
                <div>
                    <div class="summary-label">Total Perpanjangan Pending</div>
                    <div class="summary-value">Rp {{ number_format($summary['unpaid_amount'] ?? $summary['amount'] ?? 0, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    @endif
</div>
