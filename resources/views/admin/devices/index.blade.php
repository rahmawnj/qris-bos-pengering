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
                    <tbody>
                        @foreach ($devices as $device)
                            @php
                                $outlet = $device->outlet;
                                $owner = $outlet?->owner;
                                $logo = $owner?->brand_logo ? asset($owner->brand_logo) : asset('assets/img/default-user.png');
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <img src="{{ $logo }}" alt="Gambar Pemilik" style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        <div>
                                            <div>{{ $owner?->brand_name }}</div>
                                            <div style="font-size: 0.9em; color: gray;">{{ $outlet?->outlet_name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>{{ $device->name }}</div>
                                    <div style="font-size: 0.9em; color: gray;">{{ $device->code }}</div>
                                </td>
                                <td>
                                    <select class="device-status-select form-control" data-device-id="{{ $device->id }}">
                                        <option value="off" @selected($device->device_status === 'off')>off</option>
                                        @foreach ($serviceTypes as $type)
                                            <option value="{{ $type->slug }}" @selected($device->device_status === $type->slug)>
                                                {{ $type->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <a href="{{ route('admin.devices.edit', $device) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Sunting
                                    </a>
                                    <form action="{{ route('admin.devices.destroy', $device) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus device ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
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
            var table = $('#data-table').DataTable({
                responsive: true,
                dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
                order: [],
                columnDefs: [
                    { targets: [0, 4], orderable: false, searchable: false },
                    { targets: [1, 2, 3], orderable: false }
                ],
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
                    ]
                }),
            });

            $('#data-table').on('change', '.device-status-select', function() {
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

                    // Kirim data ke API
                    fetch('/api/devices/' + deviceId + '/update-status', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                $.gritter.add({
                                    title: 'Success',
                                    text: data.message,
                                    sticky: false,
                                    time: 3000,
                                    class_name: 'my-sticky-class gritter-light'
                                });
                                $(this).data('original', newStatus); // Update original status
                            } else {
                                throw new Error(data.message || 'Update failed');
                            }
                        })
                        .catch(error => {
                            $.gritter.add({
                                title: 'Error',
                                text: error.message || 'Terjadi kesalahan saat mengupdate status',
                                sticky: false,
                                time: 3000,
                                class_name: 'my-sticky-class gritter-light'
                            });
                            $(this).val(originalStatus); // Kembalikan ke status awal
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
