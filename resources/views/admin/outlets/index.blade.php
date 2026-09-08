@props([
    'items' => ['Admin', 'Outlet Management', 'Outlet List'],
    'title' => 'Outlet List',
    'subtitle' => 'Manage registered Outlet here',
])
@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i class="fa fa-redo"></i></a>
                <a href="{{ route('admin.outlets.create') }}" class="btn btn-xs btn-primary"> <i class="fa fa-plus"></i> Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle" id="data-table">
                    <thead>
                        <tr>
                            <th width="1%">#</th>
                            <th>Outlet & Code</th>
                            <th>Brand / Owner</th>
                            <th>Alamat</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">QRIS</th>
                            <th class="text-center">Perpanjangan</th>
                            <th class="text-center">Kasir</th>
                            <th class="text-center">Device</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="outletDetailModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="outletDetailModalTitle"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body p-0" id="outletDetailModalBody"></div>
                <div class="modal-footer">
                    <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Tutup</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
    <style>
        .gap-1 { gap: 0.25rem; }
        .ms-2 { margin-left: 0.5rem; }
        .fw-bold { font-weight: 600; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/dist/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/js/datatables-full-export.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';

            $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.outlets.index') }}',
                responsive: true,
                autoWidth: false,
                destroy: true,
                order: [],
                dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
                columns: [{
                    data: 'number',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'outlet',
                    orderable: false
                }, {
                    data: 'owner',
                    orderable: false
                }, {
                    data: 'address',
                    orderable: false
                }, {
                    data: 'status',
                    orderable: false
                }, {
                    data: 'qris',
                    orderable: false
                }, {
                    data: 'billing',
                    orderable: false
                }, {
                    data: 'cashiers',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'devices',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }],
                buttons: fullExportButtons({
                    title: 'Outlet List',
                    url: '{{ route('admin.outlets.export-data') }}',
                    columns: [
                        { title: '#', data: 'number' },
                        { title: 'Outlet & Code', data: 'outlet' },
                        { title: 'Brand / Owner', data: 'owner' },
                        { title: 'Alamat', data: 'address' },
                        { title: 'Status', data: 'status' },
                        { title: 'QRIS', data: 'qris' },
                        { title: 'Perpanjangan', data: 'billing' },
                        { title: 'Kasir', data: 'cashiers' },
                        { title: 'Device', data: 'devices' }
                    ],
                    error: showDataTableError
                })
            });

            $('#data-table').on('xhr.dt', function(e, settings, json, xhr) {
                if (xhr.status >= 400) {
                    showDataTableError(xhr);
                }
            });

            $('#data-table').on('error.dt', function(e, settings, techNote, message) {
                var xhr = settings && settings.jqXHR;

                if (xhr) {
                    showDataTableError(xhr);
                    return;
                }

                swal('DataTables Error', message, 'error');
            });

            $('#data-table').on('click', '.outlet-detail-modal', function() {
                $('#outletDetailModalTitle').text($(this).data('title'));
                $('#outletDetailModalBody').html($(this).attr('data-content'));
                $('#outletDetailModal').modal('show');
            });

            function showDataTableError(xhr) {
                var message = 'HTTP ' + xhr.status + ' ' + xhr.statusText;
                var response = xhr.responseJSON || null;

                if (response) {
                    message += '\n\n' + (response.message || response.error || JSON.stringify(response));
                } else if (xhr.responseText) {
                    var plainText = $('<div>').html(xhr.responseText).text().replace(/\s+/g, ' ').trim();
                    message += '\n\n' + plainText.substring(0, 700);
                }

                swal({
                    title: 'DataTables Error',
                    text: message,
                    icon: 'error',
                    button: {
                        text: 'OK',
                        className: 'btn btn-danger',
                        closeModal: true
                    }
                });
            }
        });
    </script>

    <script>
        @if (session('success'))
            swal({
                title: 'Berhasil!',
                text: '{{ session('success') }}',
                icon: 'success',
                confirmButtonClass: 'btn btn-primary'
            });
        @endif

        @if (session('error'))
            swal({
                title: 'Error!',
                text: '{{ session('error') }}',
                icon: 'error',
                confirmButtonClass: 'btn btn-danger'
            });
        @endif
    </script>
@endpush
