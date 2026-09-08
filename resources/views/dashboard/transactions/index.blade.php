@php
    $title = 'Transaksi';
@endphp
@extends('layouts.dashboard.app')
@section('title', $title ?? '')
@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
@endpush
@section('content')
    <!-- BEGIN breadcrumb -->
    {{-- <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Page Options</a></li>
        <li class="breadcrumb-item active">Blank Page</li>
    </ol>
    <!-- END breadcrumb -->
    <!-- BEGIN page-header -->
    <h1 class="page-header">Blank Page <small>header small text goes here...</small></h1>
    <!-- END page-header -->
    <!-- BEGIN panel --> --}}
    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>

            </div>
        </div>
        <div class="panel-body">
            <div class="d-flex justify-content-between">

                <div class="">
                    <a href="{{ route('transactions.export', [
                        'outlet_id' => request('outlet_id'),
                        'start_date' => request('start_date'),
                        'end_date' => request('end_date'),
                    ]) }}"
                        class="btn btn-success">Export to Excel</a>
                </div>
            </div>

            <div class="table-responsive">

                <table id="transactions-table" class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Outlet</th>
                            <th>Order ID</th>

                            <th>Harga</th>

                            <th>Waktu</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $transaction->outlet->name ?? '-' }} | {{ $transaction->outlet->code ?? '-' }}</td>
                                <td>{{ $transaction->order_id }}</td>

                                <td>Rp {{ floatval($transaction->amount) }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaction->time)->format('d-m-Y H:i:s') }}</td>
                                <td>{{ $transaction->status }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">No transactions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                {{ $transactions->links() }} <!-- Ensure to use links() on paginator instance -->
            </div>
        </div>
    </div>
    <!-- END panel -->

@endsection
@push('scripts')
    <script src="{{ asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <script src="{{ asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/dist/jszip.min.js') }}"></script>

    <script>
        $('#data-table').DataTable({
            responsive: true,
            dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: [{
                    extend: 'csv',
                    className: 'btn-sm'
                },
                {
                    extend: 'excel',
                    className: 'btn-sm'
                },
                {
                    extend: 'pdf',
                    className: 'btn-sm'
                },
            ],
        });
    </script>
@endpush
