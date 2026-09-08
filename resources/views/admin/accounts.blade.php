@props([
    'items' => ['Admin', 'User Management', 'Admin List'],
    'title' => 'Admin List',
    'subtitle' => 'Manage registered admin here',
])

@extends('layouts.dashboard.app')
@section('content')
    <!-- BEGIN breadcrumb -->
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />
    <!-- BEGIN panel -->
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
            <div id="accounts-table-error" class="alert alert-danger d-none"></div>
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th>Image</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created At</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- END panel -->
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

            try {
                $('#data-table').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('admin.accounts.all') }}',
                        error: function(xhr) {
                            showDataTableError(xhr);
                        }
                    },
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
                        data: 'image',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'name',
                        orderable: false
                    }, {
                        data: 'email',
                        orderable: false
                    }, {
                        data: 'role',
                        orderable: false
                    }, {
                        data: 'created_at',
                        orderable: false,
                        searchable: false
                    }, {
                        data: 'actions',
                        orderable: false,
                        searchable: false
                    }],
                    buttons: fullExportButtons({
                        title: 'All Accounts',
                        url: '{{ route('admin.accounts.export-data') }}',
                        columns: [
                            { title: '#', data: 'number' },
                            { title: 'Nama', data: 'name' },
                            { title: 'Email', data: 'email' },
                            { title: 'Role', data: 'role' },
                            { title: 'Created At', data: 'created_at' }
                        ],
                        error: showDataTableError
                    }),
                });
            } catch (error) {
                showClientError(error.message || error);
                console.error(error);
            }

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

            function showClientError(message) {
                $('#accounts-table-error')
                    .removeClass('d-none')
                    .text('DataTables Error: ' + message);
            }

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

                showClientError(message);
            }
        });
    </script>
@endpush
