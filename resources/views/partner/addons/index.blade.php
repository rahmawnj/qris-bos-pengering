@props([
    'items' => ['Admin', 'Manajemen Add-on', 'Daftar Add-on'],
    'title' => 'Daftar Add-on',
    'subtitle' => 'Kelola Add-on yang terdaftar',
])
@php
    $feature = getData();
@endphp


@extends('layouts.dashboard.app')
@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.css') }}" rel="stylesheet" />
@endpush

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

                <a href="{{ route('partner.addons.create') }}"
                    class="btn btn-xs btn-primary{{ !$feature->can('partner.addons.create') ? ' disabled' : '' }}"><i
                        class="fa fa-plus"></i> Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Outlet</th>
                            <th>Nama Add-on</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($addons as $addon)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{-- Display Owner Brand and Outlet Name --}}
                                    <div>{{ $addon->outlet->owner->brand_name ?? 'N/A Owner' }}</div>
                                    <div style="font-size: 0.9em; color: gray;">
                                        {{ $addon->outlet->outlet_name ?? 'N/A Outlet' }}</div>
                                </td>
                                <td>{{ $addon->name }}</td>
                                <td>{{ $addon->category ?? '-' }}</td> {{-- Display category, default to '-' if null --}}
                                <td>Rp {{ number_format($addon->price, 0, ',', '.') }}</td>
                                <td>
                                    @if ($addon->is_active)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-danger">Non-Aktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('partner.addons.edit', $addon->id) }}"
                                        class="btn btn-primary btn-sm{{ !$feature->can('partner.addons.edit') ? ' disabled' : '' }}">
                                        <i class="fas fa-edit"></i> Sunting
                                    </a>
                                    <form action="{{ route('partner.addons.destroy', $addon->id) }}" method="POST"
                                        class="d-inline delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn btn-danger btn-sm delete-button{{ !$feature->can('partner.addons.destroy') ? ' disabled' : '' }}"
                                            data-addon-name="{{ $addon->name }}">
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
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#data-table').DataTable({
                responsive: true,
                dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: [{
                    extend: 'copy',
                    className: 'btn-sm'
                }, {
                    extend: 'csv',
                    className: 'btn-sm'
                }, {
                    extend: 'excel',
                    className: 'btn-sm'
                }, {
                    extend: 'pdf',
                    className: 'btn-sm'
                }, {
                    extend: 'print',
                    className: 'btn-sm'
                }],
            });

            // SweetAlert for delete confirmation
            $(document).on('click', '.delete-button', function(e) {
                e.preventDefault(); // Prevent default form submission
                var form = $(this).closest('form');
                var addonName = $(this).data('addon-name');

                swal({
                    title: "Apakah Anda yakin?",
                    text: "Anda akan menghapus add-on '" + addonName + "' secara permanen!",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                }).then((willDelete) => {
                    if (willDelete) {
                        form.submit(); // Submit the form if confirmed
                    }
                });
            });
        });
    </script>

    <script>
        // Display success/error messages using SweetAlert from session
        @if (session('success'))
            swal({
                title: 'Berhasil!',
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
                title: 'Gagal!',
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
