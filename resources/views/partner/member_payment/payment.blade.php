@props([
    'items' => ['Partner', 'Pembayaran Member', 'Kasir'],
    'title' => 'Pembayaran Kasir',
    'subtitle' => 'Lakukan pembayaran kasir untuk member terdaftar',
])

@extends('layouts.dashboard.app')

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />


    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Pembayaran Kasir</h4>
        </div>
        <div class="panel-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('partner.member.payment.store') }}" method="POST" id="paymentForm">
                @csrf

                <div class="mb-3">
                    <label for="cashier_name" class="form-label">Nama Kasir (opsional)</label>
                    <input type="text" class="form-control" id="cashier_name" name="cashier_name"
                        placeholder="Masukkan nama kasir">
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6">
                        <label for="deviceSelect" class="form-label">Pilih Device <span style="color:red">*</span></label>
                        <select class="form-control" id="deviceSelect" name="device_id" required>
                            <option value="">-- Pilih Device --</option>
                            @foreach ($devices as $device)
                                <option value="{{ $device->id }}">{{ $device->name }} ({{ $device->code }})

                                    @foreach ($device->serviceTypes as $item)
                                        {{ $item->name }}:
                                        @if ($item->pivot->price)
                                            Rp {{ number_format($item->pivot->price, 0, ',', '.') }}
                                        @else
                                            Tidak ada harga
                                        @endif
                                    @endforeach
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Service Type <span style="color:red">*</span></label>
                        <div>
                            @foreach (App\Models\ServiceType::all() as $serviceType)
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="service_type"
                                        id="{{ $serviceType->id }}" value="{{ $serviceType->id }}" required>
                                    <label class="form-check-label"
                                        for="{{ $serviceType->id }}">{{ $serviceType->name }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <label for="amount" class="form-label">Jumlah Pembayaran <span style="color:red">*</span></label>
                    <input type="number" readonly class="form-control" id="amount" name="amount"
                        placeholder="Masukkan jumlah pembayaran" required disabled>
                </div>
                <div class="mb-3">
                    <label for="member_id" class="form-label">Pilih Member <span style="color:red">*</span></label>
                    <select class="form-select" id="member_id" name="member_id" required>
                        <option value="">-- Pilih Member --</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}">{{ $member->user->name }} | {{ $member->pivot->amount }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan (opsional)</label>
                    <textarea class="form-control" id="notes" name="notes" placeholder="Masukkan catatan" rows="3"></textarea>
                </div>
                

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Proses Pembayaran Kasir
                </button>
            </form>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
@endpush
@push('scripts')
    <script src="{{ asset('assets/plugins/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jquery.maskedinput/src/jquery.maskedinput.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert/dist/sweetalert.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}"></script>

    <script>
        $(document).ready(function() {

            // Panggil checkEnable ketika terjadi perubahan pada device dan service type
            $("#deviceSelect").on("change", checkEnable);
            $("input[name='service_type']").on("change", checkEnable);

            // Mencegah form submit dengan enter
            $("#paymentForm input").on("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });

            // Konfirmasi sebelum submit form
            $("#paymentForm").on("submit", function(e) {
                e.preventDefault();
                swal({
                    title: 'Konfirmasi',
                    text: "Apakah Anda yakin ingin memproses pembayaran kasir?",
                    icon: 'warning',
                    buttons: {
                        cancel: {
                            text: "Batal",
                            visible: true,
                            closeModal: true,
                        },
                        confirm: {
                            text: "Ya, proses!",
                            closeModal: true
                        }
                    },
                    dangerMode: true,
                }).then((willSubmit) => {
                    if (willSubmit) {
                        var formatted = $("#amount").val();
                        var plainNumber = formatted.replace(/[^0-9]/g, '');
                        $("#amount").val(plainNumber);
                        $("#paymentForm").off("submit").submit();
                    }
                });
            });

            function checkEnable() {
                var deviceId = $("#deviceSelect").val();
                var serviceTypeId = $("input[name='service_type']:checked").val();


                if (deviceId && serviceTypeId) {
                    $("#amount").prop("disabled", false);

                    $.ajax({
                        url: "/api/device-price/" + deviceId + "/" + serviceTypeId,
                        method: "GET",
                        success: function(response) {
                            $("#amount").val(response.price);
                        },
                        error: function() {
                            $("#amount").val('');
                            alert('Gagal mengambil harga. Periksa device dan service type.');
                        }
                    });

                } else {
                    $("#amount").val('');
                    $("#amount").prop("disabled", true);
                }
            }

        });
        $('#member_id').picker({
            search: true
        });

        $(document).ready(function() {
            @if (session('success'))
                swal({
                    title: 'Sukses!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    timer: 3000,
                    showConfirmButton: false
                });
            @endif
        });
    </script>
@endpush
