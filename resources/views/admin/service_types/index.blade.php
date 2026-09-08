@props([
    'items' => ['Admin', 'Tipe Layanan', 'Daftar Tipe Layanan'],
    'title' => 'Daftar Tipe Layanan',
    'subtitle' => 'Kelola dan pantau seluruh tipe layanan yang terdaftar.'
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

                <a href="{{ route('admin.service_types.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i> Add</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Servis</th>
                            <th>Slug</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($serviceTypes as $serviceType)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $serviceType->name }}</td>
                                <td>{{ Str::snake($serviceType->name) }}</td>
                                <td>
                                    <a href="{{ route('admin.service_types.edit', $serviceType->id) }}" class="btn btn-primary btn-sm"> <i class="fas fa-edit"></i> Edit</a>
                                    <form action="{{ route('admin.service_types.destroy', $serviceType) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this service type?')"> <i class="fas fa-trash"></i> Delete</button>
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
        $('#data-table').DataTable({
            responsive: true,
            dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
            buttons: fullExportButtons({
                title: 'Daftar Tipe Layanan',
                columns: [
                    { title: '#', data: 'number', source: 0 },
                    { title: 'Nama Servis', data: 'name', source: 1 },
                    { title: 'Slug', data: 'slug', source: 2 }
                ]
            }),
        });
    </script>
@endpush
