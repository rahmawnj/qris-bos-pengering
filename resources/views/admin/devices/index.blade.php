@props([
    'items' => ['Admin', 'Manajemen Device', 'Daftar Device'],
    'title' => 'Daftar Device',
    'subtitle' => 'Kelola Device yang terdaftar',
])
@extends('layouts.dashboard.app')

@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/switchery/dist/switchery.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/gritter/css/jquery.gritter.css') }}" rel="stylesheet" />
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                    <i class="fa fa-redo"></i>
                </a>
                <a href="{{ route('admin.devices.create') }}" class="btn btn-xs btn-primary">
                    <i class="fa fa-plus"></i> Tambah
                </a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th width="1">#</th>
                            <th>Brand</th>
                            <th>Device</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/switchery/dist/switchery.min.js') }}"></script>
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
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset(path: 'assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $.fn.dataTable.ext.errMode = 'none';

            var table = $('#data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('admin.devices.index') }}',
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
                    data: 'brand',
                    orderable: false
                }, {
                    data: 'device',
                    orderable: false
                }, {
                    data: 'status',
                    orderable: false
                }, {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }],
                buttons: fullExportButtons({
                    title: 'Daftar Device',
                    columns: [
                        { title: '#', data: 'number', source: 0 },
                        { title: 'Brand', data: 'brand', source: 1 },
                        { title: 'Device', data: 'device', source: 2 },
                        {
                            title: 'Status',
                            data: 'status',
                            source: function(row) {
                                return $('<div>').html(row[3]).find('option:selected').text().trim();
                            }
                        }
                    ],
                    error: showDataTableError
                })
            });

            $('#data-table').on('xhr.dt', function(e, settings, json, xhr) {
                if (xhr && xhr.status >= 400) {
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

            function showDataTableError(xhr) {
                var message = 'HTTP ' + xhr.status + ' ' + (xhr.statusText || '');
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

            $('#data-table').on('submit', '.device-delete-form', function(event) {
                if (!window.confirm('Apakah Anda yakin ingin menghapus device ini?')) {
                    event.preventDefault();
                }
            });

            $('#data-table').on('change', '.device-status-select', function() {            $('#data-table').on('change', '.device-status-select', function() {
                var newStatus = $(this).val();
                var deviceId = $(this).data('device-id');
                var originalStatus = $(this).data('original');

                // Tampilkan SweetAlert dengan input field
                swal({
                    title: 'Berikan Catatan Bypass',
                    text: 'Silakan masukkan catatan mengapa status device ini diubah.',
                    content: {
                        element: "input",
                        attributes: {
                            placeholder: "Catatan Bypass",
                            type: "text",
                        },
                    },
                    buttons: {
                        cancel: "Batal",
                        confirm: {
                            text: "Ubah Status",
                            className: "btn btn-primary"
                        }
                    },
                    dangerMode: false,
                }).then((value) => {
                    if (value === null) {
                        // Jika user klik 'Batal' atau di luar modal
                        $(this).val(originalStatus);
                        return;
                    }

                    // Lanjutkan jika ada catatan
                    var bypassNote = value.trim();
                    if (bypassNote === '') {
                        swal('Peringatan', 'Catatan tidak boleh kosong!', 'warning');
                        $(this).val(originalStatus);
                        return;
                    }

                    // Siapkan data untuk dikirim
                    var formData = new FormData();
                    formData.append('device_status', newStatus);
                    formData.append('bypass_note', bypassNote);
                    formData.append('_token', '{{ csrf_token() }}');

                    // Kirim perubahan status menggunakan jQuery AJAX.
                    var $select = $(this);
                    $.ajax({
                        url: '/api/devices/' + deviceId + '/update-status',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json'
                    }).done(function(data) {
                        if (data.status === 'success') {
                            $.gritter.add({
                                title: 'Success',
                                text: data.message,
                                sticky: false,
                                time: 3000,
                                class_name: 'my-sticky-class gritter-light'
                            });
                            $select.data('original', newStatus);
                            return;
                        }

                        $.gritter.add({
                            title: 'Error',
                            text: data.message || 'Gagal memperbarui status device.',
                            sticky: false,
                            time: 3000,
                            class_name: 'my-sticky-class gritter-light'
                        });
                        $select.val(originalStatus);
                    }).fail(function(xhr) {
                        var response = xhr.responseJSON || {};
                        $.gritter.add({
                            title: 'Error',
                            text: response.message || 'Terjadi kesalahan saat mengupdate status',
                            sticky: false,
                            time: 3000,
                            class_name: 'my-sticky-class gritter-light'
                        });
                        $select.val(originalStatus);
                    });
                });
            });

            // Simpan status awal saat halaman dimuat
            $('.device-status-select').each(function() {
                $(this).data('original', $(this).val());
            });

            // Inisialisasi kembali status awal setelah tabel digambar ulang
            table.on('draw.dt', function() {
                $('.device-status-select').each(function() {
                    $(this).data('original', $(this).val());
                });
            });
        });
    </script>

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
@endpush
