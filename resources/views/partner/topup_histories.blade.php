@props([
    'items' => ['Partner', 'Manajemen Outlet', 'Riwayat Topup'],
    'title' => 'Riwayat Topup Outlet',
    'subtitle' => 'Lihat histori transaksi topup dari outlet',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />


    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Riwayat Topup</h4>
        </div>
        <div class="panel-body">
            <!-- Form Filter -->
            <form method="GET" action="{{ route('partner.topup.histories') }}" class="mb-4">
                <!-- Pastikan parameter 'out' ikut dikirim -->
                <input type="hidden" name="out" value="{{ request()->get('out') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" id="start_date"
                            value="{{ request()->get('start_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" class="form-control" name="end_date" id="end_date"
                            value="{{ request()->get('end_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label for="member">Nama Member</label>
                        <input type="text" class="form-control" name="member" id="member"
                            placeholder="Cari nama member" value="{{ request()->get('member') }}">
                    </div>
                </div>
                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('partner.topup.histories', ['out' => request()->get('out')]) }}"
                        class="btn btn-secondary">Reset</a>
                </div>
            </form>

            <!-- Tabel Riwayat Topup -->
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Outlet</th>
                            <th>Member</th>
                            <th>Jumlah Topup</th>
                            <th>Waktu</th>
                            <th>Nama Kasir</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($topupHistories as $history)
                            <tr>
                                <td>{{ $loop->iteration + ($topupHistories->currentPage() - 1) * $topupHistories->perPage() }}
                                </td>

                                <td>
                                    <div>
                                        <strong class="text-primary">{{ $history->outlet->code }}</strong>
                                    </div>

                                    <div>
                                        <small class="text-muted">{{ $history->outlet->outlet_name }}</small>
                                    </div>
                                </td>
                                <td>{{ $history->member->user ? $history->member->user->name : 'N/A' }}</td>
                                <td>Rp {{ number_format($history->amount, 0, ',', '.') }}</td>
                                <td>{{ $history->time . ' ' . strtoupper($history->timezone) }}</td>
                                <td>{{ $history->cashier_name ?? '-' }}</td>
                                <td>{{ $history->notes ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada data topup.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div>
                {{ $topupHistories->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
