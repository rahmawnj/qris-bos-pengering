@props([
    'items' => ['Admin', 'Member Management', 'Member List'],
    'title' => 'Member List',
    'subtitle' => 'Manage registered Members here'
])
@extends('layouts.dashboard.app')

@section('title', $title)
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
            <a href="{{ route('admin.members.create') }}" class="btn btn-xs btn-primary">
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
                        <th>Gambar</th>
                        <th>Nama</th>

                        <th>Email</th>
                        <th>Total Berlanggan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($members as $member)
                        <tr>{{--  --}}
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($member->user->image)
                                <div class="widget-img rounded bg-dark" style="background-image: url({{ asset($member->user->image) }})"></div>
                                @else
                                <div class="widget-img rounded bg-dark" style="background-image: url({{ asset('assets/img/default-user.png') }})"></div>
                                @endif
                            </td>
                            <td>
                                {{ $member->user->name }}
                            </td>
                            <td>
                                {{ $member->user->email }}
                            </td>
                            <td>
                                {{ $member->owners->count() }} Brand
                            </td>
                            <td>
                                <a href="{{ route('admin.members.edit', $member) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Sunting
                                </a>
                                <form action="{{ route('admin.members.destroy', $member) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Apakah Anda yakin ingin menghapus member ini?')">
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

@push('styles')
    <link href="{{ asset('assets/plugins/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet" />
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
        $(document).ready(function(){
            $('#data-table').DataTable({
                responsive: true,
                dom: '<"row"<"col-sm-5"B><"col-sm-7"fr>>t<"row"<"col-sm-5"i><"col-sm-7"p>>',
                buttons: fullExportButtons({
                    title: 'Member List',
                    columns: [
                        { title: '#', data: 'number', source: 0 },
                        { title: 'Nama', data: 'name', source: 2 },
                        { title: 'Email', data: 'email', source: 3 },
                        { title: 'Total Berlanggan', data: 'subscriptions', source: 4 }
                    ]
                })
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
