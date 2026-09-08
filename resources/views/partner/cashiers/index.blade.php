@props([
    'items' => ['Admin', 'Cashier Management', 'Cashier List'],
    'title' => 'Cashier List',
    'subtitle' => 'Manage registered Cashiers here',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">{{ $title }}</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>
                <a href="{{ route('partner.cashiers.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i>
                    Tambah</a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table" id="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nama Kasir</th>
                            <th>Outlet</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cashiers as $cashier)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        @if ($cashier->user->image)
                                            <img src="{{ asset($cashier->user->image) }}" alt="Foto"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        @else
                                            <img src="{{ asset('assets/img/default-user.png') }}" alt="Foto"
                                                style="width: 50px; height: 50px; object-fit: cover; border-radius: 50%;">
                                        @endif
                                        <div>
                                            <div>{{ $cashier->user->name }}</div>
                                            <div style="font-size: 0.9em; color: gray;">{{ $cashier->user->email }}</div>
                                        </div>
                                    </div> {{ $cashier->user->name }}
                                </td>
                                <td>{{ $cashier->outlet->outlet_name ?? '-' }}</td>
                                <td>{{ $cashier->user->email }}</td>
                                <td>
                                    @if ($cashier->status)
                                        <span class="badge bg-success">Aktif</span>
                                    @else
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('partner.cashiers.edit', $cashier->id) }}"
                                        class="btn btn-primary btn-sm"><i class="fas fa-edit"></i> Sunting</a>
                                    <form action="{{ route('partner.cashiers.destroy', $cashier->id) }}" method="POST"
                                        class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Yakin ingin menghapus kasir ini?')"><i
                                                class="fas fa-trash"></i> Hapus</button>
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
    <script src="{{ asset('assets/plugins/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/build/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/dist/jszip.min.js') }}"></script>
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
@endpush
