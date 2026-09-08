@extends('layouts.dashboard.app')
@php
    $feature = getData(); // Dapatkan instance DataFetcher sekali
@endphp

@push('styles')
    <style>
        /* Custom style untuk nav-tabs */
        .nav-tabs .nav-link {
            color: #000;
            background-color: transparent;
            border: 1px solid #000;
            border-bottom: none;
            border-radius: 0;
            margin-right: 2px;
            transition: all 0.3s ease;
        }

        .nav-tabs .nav-link:hover {
            background-color: #f8f9fa;
        }

        .nav-tabs .nav-link.active {
            color: #fff;
            background-color: #000;
            border-color: #000;
            font-weight: bold;
        }

        /* Style tambahan untuk panel dengan tabs agar rapi */
        .panel-with-tabs .panel-heading {
            border-bottom: 0;
        }

        .panel-with-tabs .nav-tabs {
            margin-bottom: -1px;
        }

        .btn-action {
            width: 36px;
            /* Ukuran seragam untuk tombol aksi */
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            /* Membuat tombol bulat */
            font-size: 0.9rem;
        }

        .btn-danger {
            background-color: #dc3545;
            /* Bootstrap danger red */
            border-color: #dc3545;
            color: #fff;
        }

        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="['Partner', 'Manajemen Member', 'Terverifikasi']" title="Member Terverifikasi" subtitle="Daftar member yang telah diverifikasi" />

    <div class="panel panel-inverse panel-with-tabs">
        <div class="panel-heading p-0">
            @include('partner.members.tab', [
                'verifiedMembersCount' => $verifiedMembersCount ?? 0,
                'unverifiedMembersCount' => $unverifiedMembersCount ?? 0,
            ])
            <div class="panel-heading-btn me-2 ms-2 d-flex align-items-center">
                {{-- Optional: Add a button for quick action like "Add Member" if applicable --}}
                {{-- <a href="{{ route('partner.members.create') }}" class="btn btn-sm btn-primary me-2"><i class="fa fa-plus me-1"></i> Tambah Member</a> --}}
                <a href="javascript:;" class="btn btn-xs btn-icon btn-circle btn-secondary" data-bs-toggle="panel-expand">
                    <i class="fa fa-expand"></i>
                </a>
            </div>
        </div>
        <div class="panel-body tab-content">
            <div class="tab-pane fade show active" id="nav-tab-1">
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="fas fa-check-circle fa-2x me-3"></i>
                    <div>
                        Ini adalah daftar member yang telah **berhasil diverifikasi**. Anda dapat mengelola mereka di sini.
                    </div>
                </div>
                <div class="table-responsive mt-3">
                    <table class="table table-bordered table-hover" id="data-table-member">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">No</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Tanggal Pendaftaran</th>
                                <th class="text-center">Tanggal Verifikasi</th>
                                <th class="text-end">Saldo</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($verifiedMembers as $member)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $member->user->name }}</td>
                                    <td>{{ $member->user->email }}</td>
                                    <td>{{ $member->created_at->format('d M Y H:i') }}</td>
                                    <td class="text-center">{{ $member->updated_at->format('d M Y H:i') }}</td>
                                    <td class="text-end">Rp {{ number_format($member->pivot->amount ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="text-center">
                                        <form
                                            action="{{ route('partner.members.subscription.destroy', ['member' => $member->id]) }}"
                                            method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-action"
                                                title="Hapus Subscription"
                                                onclick="return confirm('Hapus subscription member ini?')"
                                                @disabled(!$feature->can('partner.members.subscription.destroy'))>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-users-slash fa-3x text-muted mb-3"></i><br>
                                        <p class="lead">Tidak ada member terverifikasi saat ini.</p>
                                        <p>Silakan verifikasi member dari tab "Belum Diverifikasi".</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/gritter/js/jquery.gritter.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
@endpush
