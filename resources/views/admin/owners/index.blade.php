@props([
    'items' => ['Admin', 'Brand Management', 'Brand List'],
    'title' => 'Brand List',
    'subtitle' => 'Manage registered Brand here',
])

@extends('layouts.dashboard.app')
@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title ?? '' }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>

                <a href="{{ route('admin.owners.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i>
                    Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Pemilik</th>
                            <th>Brand</th>
                            <th>QRIS Type</th>
                            <th>Outlet</th>
                            <th>Balance</th>
                            <th>Masa Aktif</th>
                            <th>Status Akun</th>
                            <th>Alamat</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>

            </div>
        </div>
    </div>

    <div class="modal fade" id="ownerReusableModal">
        <div class="modal-dialog modal-lg" id="ownerReusableModalDialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="ownerReusableModalTitle"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <div class="modal-body" id="ownerReusableModalBody"></div>
                <div class="modal-footer" id="ownerReusableModalFooter">
                    <a href="javascript:;" class="btn btn-white" data-bs-dismiss="modal">Tutup</a>
                </div>
            </div>
        </div>
    </div>
    @endsection
@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}"
        rel="stylesheet" />
@endpush

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
    <script src="{{ asset('assets/js/datatables-full-export.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script>
        @if (session('success'))
            swal({
                title: 'Success',
                text: '{{ session('success') }}',
                icon: 'success',
                button: {
                    text: 'OK',
                    className: 'btn btn-primary',
                    closeModal: true
                }
            });
        @endif

        @if (session('error'))
            swal({
                title: 'Error',
                text: '{{ session('error') }}',
                icon: 'error',
                button: {
                    text: 'OK',
                    className: 'btn btn-danger',
                    closeModal: true
                }
            });
        @endif
    </script>

    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';

            var table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.owners.index') }}',
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
                    data: 'owner',
                    orderable: false
                }, {
                    data: 'brand',
                    orderable: false
                }, {
                    data: 'qris_type',
                    orderable: false
                }, {
                    data: 'outlets',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'balance',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'expires',
                    orderable: false,
                    searchable: false
                }, {
                    data: 'status',
                    orderable: false
                }, {
                    data: 'address',
                    orderable: false
                }, {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }],
                buttons: fullExportButtons({
                    title: 'Brand List',
                    url: '{{ route('admin.owners.export-data') }}',
                    columns: [
                        { title: '#', data: 'number' },
                        { title: 'Pemilik', data: 'owner' },
                        { title: 'Brand', data: 'brand' },
                        { title: 'QRIS Type', data: 'qris_type' },
                        { title: 'Outlet', data: 'outlets' },
                        { title: 'Balance', data: 'balance' },
                        { title: 'Masa Aktif', data: 'expires' },
                        { title: 'Status Akun', data: 'status' },
                        { title: 'Alamat', data: 'address' }
                    ],
                    error: showDataTableError
                }),
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

            $('#data-table').on('click', '.owner-outlets-modal, .owner-update-modal', function() {
                var isUpdate = $(this).hasClass('owner-update-modal');

                $('#ownerReusableModalTitle').text($(this).data('title'));
                $('#ownerReusableModalBody').html($(this).attr('data-content'));
                $('#ownerReusableModalFooter').toggle(!isUpdate);
                $('#ownerReusableModalDialog').toggleClass('modal-lg', !isUpdate);
                $('#ownerReusableModal').modal('show');
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
@endpush
