@props([
    'items' => ['Partner', 'Transaction Management', 'Transaction List'],
    'title' => 'Transaction List',
    'subtitle' => 'Manage all payment transactions here',
])




@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
            </div>
        </div>
        <div class="panel-body">
            <!-- Filter Form -->
            <form method="GET" action="{{ route('partner.member.transactions') }}" class="mb-3">
                <div class="d-flex justify-content-between align-items-center gap-5">
                    <!-- Input Group untuk filter select dan tanggal -->
                    <div class="input-group input-group-sm flex-fill">
                        <select name="status" class="form-control" style="max-width: 120px;">
                            <option value="">-- All Status --</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>success</option>
                            <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                        <input type="date" name="start_date" class="form-control" style="max-width: 140px;"
                            value="{{ request('start_date') }}">
                        <input type="date" name="end_date" class="form-control" style="max-width: 140px;"
                            value="{{ request('end_date') }}">
                    </div>
                    <!-- Input Search -->
                    <div class="flex-fill">
                        <input type="text" name="search" class="form-control form-control-sm"
                            placeholder="Search (Order ID, Owner, Amount)" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="mt-2 text-right">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="{{ route('admin.transactions.index') }}" class="btn btn-default btn-sm">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th width="30">Order ID</th>
                            <th>Owner</th>
                            <th>Amount</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($memberTransactions as $memberTransaction)
                            @php
                                $timezoneMap = [
                                    'wib' => 'Asia/Jakarta',
                                    'wita' => 'Asia/Makassar',
                                    'wit' => 'Asia/Jayapura',
                                ];
                                $tzKey = strtolower($memberTransaction->transaction->timezone);
                                $tz = $timezoneMap[$tzKey] ?? 'Asia/Jakarta';
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $memberTransaction->transaction->order_id }}</td>
                                <td>
                                    <strong>{{ $memberTransaction->transaction->owner->brand_name ?? '-' }}</strong><br>
                                    <small class="text-muted">
                                        Outlet: {{ $memberTransaction->transaction->outlet->address ?? 'N/A' }}<br>
                                        Device: {{ $memberTransaction->transaction->device_code }}
                                    </small>
                                </td>
                                <td>Rp {{ number_format($memberTransaction->transaction->amount, 0) }}</td>
                                <td>{{ ucfirst($memberTransaction->transaction->type) }}</td>
                                <td>{{ ucfirst($memberTransaction->transaction->status) }}</td>
                                <td>
                                    {{ $memberTransaction->transaction->created_at->setTimezone($tz)->format('d-m-Y H:i') }}
                                    {{ strtoupper($memberTransaction->transaction->timezone) }}
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning edit-status-btn"
                                        data-transaction-id="{{ $memberTransaction->transaction->id }}"
                                        data-current-status="{{ $memberTransaction->transaction->status }}">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination and Label Count -->
            <div class="d-flex justify-content-between align-items-center">
                <div>{{ $memberTransactions->total() }} transactions found</div>
                <div>{{ $memberTransactions->links() }}</div>
            </div>
        </div>
    </div>

    <!-- Modal Edit Status -->
    <div class="modal fade" id="editStatusModal" tabindex="-1" role="dialog" aria-labelledby="editStatusModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form method="GET" action="{{ route('admin.transactions.index') }}" id="updateStatusForm">
                <input type="hidden" name="update_status" value="1">
                <input type="hidden" name="transaction_id" id="modalTransactionId">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStatusModalLabel">Edit Status Transaksi</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="modalStatus">Status Baru</label>
                            <select class="form-control" name="status" id="modalStatus" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="pending">Pending</option>
                                <option value="success">success</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    {{-- <script src="{{ asset('assets/plugins/jquery/dist/jquery.min.js') }}"></script> --}}
    <!-- Pastikan sudah include Bootstrap JS -->
    <script>
        $(document).ready(function() {
            $('.edit-status-btn').on('click', function() {
                var transactionId = $(this).data('transaction-id');
                var currentStatus = $(this).data('current-status');

                $('#modalTransactionId').val(transactionId);
                $('#modalStatus').val(currentStatus);

                $('#editStatusModal').modal('show');
            });
        });
    </script>
@endpush
