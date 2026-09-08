@props([
    'items' => ['Partner', 'Cashier Management', 'Cashier List'],
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
                <button type="button" class="btn btn-xs btn-primary" data-bs-toggle="modal" data-bs-target="#cashierModal"
                    data-mode="create">
                    <i class="fa fa-plus"></i> Tambah
                </button>
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
                                    </div>
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
                                    <button type="button" class="btn btn-primary btn-sm edit-cashier-btn"
                                        data-bs-toggle="modal" data-bs-target="#cashierModal" data-mode="edit"
                                        data-id="{{ $cashier->id }}" data-name="{{ $cashier->user->name }}"
                                        data-email="{{ $cashier->user->email }}"
                                        data-outlet-id="{{ $cashier->outlet_id }}"
                                        data-image="{{ $cashier->user->image ? asset($cashier->user->image) : asset('assets/img/default-user.png') }}">
                                        <i class="fas fa-edit"></i> Sunting
                                    </button>
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

    <div class="modal fade" id="cashierModal" tabindex="-1" aria-labelledby="cashierModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="cashierForm" method="POST" enctype="multipart/form-data" action="">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">

                    <div class="modal-header">
                        <h5 class="modal-title" id="cashierModalLabel">Tambah Kasir</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 text-center">
                            <img id="modalImagePreview" src="{{ asset('assets/img/default-user.png') }}"
                                style="width: 80px; height: 80px; cursor: pointer; border-radius: 50%; object-fit: cover;"
                                onclick="document.getElementById('modalImage').click();">
                            <input type="file" class="d-none @error('image') is-invalid @enderror" id="modalImage"
                                name="image" accept="image/*" onchange="previewModalImage(event)">
                            <button type="button" class="btn btn-xs btn-purple btn-secondary mt-2"
                                onclick="document.getElementById('modalImage').click();">Ganti Foto</button>
                            @error('image')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modalName" class="form-label">Nama Kasir <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="modalName"
                                name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="modalEmail" class="form-label">Email Kasir <span
                                    class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                id="modalEmail" name="email" value="{{ old('email') }}" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="modalPassword" class="form-label">Kata Sandi <span
                                        class="text-danger password-required">*</span></label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="modalPassword" name="password">
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="modalPasswordConfirmation" class="form-label">Konfirmasi Sandi <span
                                        class="text-danger password-required">*</span></label>
                                <input type="password" class="form-control" id="modalPasswordConfirmation"
                                    name="password_confirmation">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalOutletId" class="form-label">Pilih Outlet <span
                                    class="text-danger">*</span></label>
                            <select name="outlet_id"
                                class="form-control default-select2 @error('outlet_id') is-invalid @enderror"
                                id="modalOutletId" required>
                                <option value="">-- Pilih Outlet --</option>
                                @foreach ($outlets as $outlet)
                                    <option value="{{ $outlet->id }}">
                                        {{ $outlet->owner->brand_name }} - {{ $outlet->outlet_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('outlet_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="modalSubmitBtn">Simpan</button>
                    </div>
                </form>
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
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />
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
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#data-table').DataTable();
            $(".default-select2").select2({
                dropdownParent: $('#cashierModal') // Crucial for Select2 within a modal
            });

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

            // Function to preview image in the modal
            window.previewModalImage = function(event) {
                const reader = new FileReader();
                reader.onload = function() {
                    document.getElementById('modalImagePreview').src = reader.result;
                };
                reader.readAsDataURL(event.target.files[0]);
            };

            // Handle modal show event to populate data for editing or reset for adding
            $('#cashierModal').on('show.bs.modal', function(event) {
                const button = $(event.relatedTarget); // Button that triggered the modal
                const mode = button.data('mode'); // Extract info from data-* attributes

                const modal = $(this);
                const form = modal.find('#cashierForm');
                const modalTitle = modal.find('.modal-title');
                const passwordRequiredSpans = modal.find('.password-required');


                // Clear previous validation errors (if any)
                $('.is-invalid').removeClass('is-invalid');
                $('.invalid-feedback').remove();

                if (mode === 'create') {
                    modalTitle.text('Tambah Kasir');
                    form.attr('action', '{{ route('partner.cashiers.store') }}');
                    modal.find('#formMethod').val('POST');
                    form[0].reset(); // Reset form fields
                    modal.find('#modalImagePreview').attr('src',
                        '{{ asset('assets/img/default-user.png') }}'); // Reset image
                    modal.find('#modalPassword').prop('required',
                    true); // Make password required for create
                    modal.find('#modalPasswordConfirmation').prop('required',
                    true); // Make password confirmation required for create
                    passwordRequiredSpans.show(); // Show required asterisks
                    $(".default-select2").val('').trigger('change'); // Reset select2
                } else if (mode === 'edit') {
                    modalTitle.text('Edit Kasir');
                    const cashierId = button.data('id');
                    const cashierName = button.data('name');
                    const cashierEmail = button.data('email');
                    const cashierOutletId = button.data('outlet-id');
                    const cashierImage = button.data('image');

                    form.attr('action',
                    `/partner/cashiers/${cashierId}`); // Assuming /partner/cashiers/{id} for update
                    modal.find('#formMethod').val('PATCH');

                    modal.find('#modalName').val(cashierName);
                    modal.find('#modalEmail').val(cashierEmail);
                    modal.find('#modalImagePreview').attr('src', cashierImage);
                    modal.find('#modalPassword').val(''); // Clear password field for edit
                    modal.find('#modalPasswordConfirmation').val(
                    ''); // Clear password confirmation field for edit
                    modal.find('#modalPassword').prop('required', false); // Password not required for edit
                    modal.find('#modalPasswordConfirmation').prop('required',
                    false); // Password confirmation not required for edit
                    passwordRequiredSpans.hide(); // Hide required asterisks

                    // Set Select2 value
                    modal.find('#modalOutletId').val(cashierOutletId).trigger('change');
                }
            });
        });
    </script>
@endpush
