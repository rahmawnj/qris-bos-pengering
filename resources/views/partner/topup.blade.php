@props([
    'items' => ['Partner', 'Topup Member'],
    'title' => 'Topup Member',
    'subtitle' => 'Input nominal topup untuk member dari outlet tertentu',
])

@extends('layouts.dashboard.app')

@push('styles')
    <!-- Include CSS select-picker -->
    <link href="{{ asset('assets/plugins/select-picker/dist/picker.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/plugins/select2/dist/css/select2.min.css') }}" rel="stylesheet" />

    <style>
        .ui-autocomplete {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            z-index: 1051;
        }

        * html .ui-autocomplete {
            height: 200px;
        }
    </style>
@endpush

@section('content')
    <x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />

    <div class="panel panel-inverse">
        <div class="panel-heading">
            <h4 class="panel-title">Topup Member</h4>
        </div>
        <div class="panel-body">
            {{-- Tampilkan pesan error atau success jika ada --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Form Topup --}}
            <form action="{{ route('partner.topup.store', ['out' => request()->get('out')]) }}" method="POST"
                id="topupForm">
                @csrf

                <div id="memberDetails" style="display: none; margin-bottom:15px;">
                    <div class="mb-3">
                        <label class="form-label">Nama Member:</label>
                        <p id="memberName" style="font-weight:bold; font-size: 18px;"></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nominal Asal:</label>
                        <p id="currentBalance" style="font-weight:bold; font-size: 18px;"></p>
                    </div>
                </div>



                <div class="mb-3">
                    <label for="outletSelect" class="form-label">Pilih Outlet</label>
                    <select class="form-control" id="outletSelect" name="out">
                        <option value="">-- Pilih Outlet --</option>
                        @foreach (getData()->outlets as $outlet)
                            <option value="{{ $outlet->code }}"
                                {{ request()->get('out') == $outlet->code ? 'selected' : '' }}>
                                {{ $outlet->outlet_name }} ({{ $outlet->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Select Picker untuk memilih Member -->
                <div class="mb-3">
                    <label for="member-search" class="form-label">Pilih Member</label>
                    <select class="selectpicker form-control" id="member-search" name="member_id" data-live-search="true">
                        <option value="">-- Pilih Member --</option>
                        @foreach ($members as $member)
                            <option value="{{ $member->id }}" data-subs-amount="{{ $member->pivot->amount }}">
                                {{ $member->user->name }} ({{ number_format($member->pivot->amount, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Input Nominal Topup, tampil namun disabled secara default -->
                <div class="mb-3">
                    <label for="nominal" class="form-label">Nominal Topup</label>
                    <input type="number" class="form-control form-control-lg" id="nominal" name="nominal"
                        placeholder="Masukkan nominal topup" disabled>
                </div>

                <!-- Perhitungan: Nominal Asal + Topup = Total Baru -->
                <div id="calculation" style="display:none; margin-bottom:15px;">
                    <p id="calculationText" style="font-weight:bold; font-size: 18px;"></p>
                </div>

                <button type="submit" class="btn btn-primary" id="submitBtn" disabled>
                    <i class="fas fa-save"></i> Proses Topup
                </button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- Include JS select-picker -->
    <script src="{{ asset('assets/plugins/select-picker/dist/picker.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/plugins/select2/dist/js/select2.min.js') }}"></script>


    <script>
        $(".selectpicker").select2();

        function toggleSubmit() {
            let memberSelected = $('#member-search').val() !== "";
            let nominalFilled = $('#nominal').val().trim() !== "";
            if (memberSelected && nominalFilled && parseFloat($('#nominal').val()) > 0) {
                $('#submitBtn').prop('disabled', false);
            } else {
                $('#submitBtn').prop('disabled', true);
            }
        }

        function updateCalculation() {
            let subsAmount = $('#member-search').find(':selected').data('subs-amount') || 0;
            subsAmount = parseFloat(subsAmount) || 0;
            let nominal = parseFloat($('#nominal').val()) || 0;
            let total = subsAmount + nominal;
            if (nominal > 0) {
                $('#calculationText').text("Total: " + total.toLocaleString('id-ID'));
                $('#calculation').show();
            } else {
                $('#calculation').hide();
            }
        }

        // Event listener untuk perubahan pada select member
        $(document).on('change', '#member-search', function() {
            console.log("Perubahan terdeteksi pada member:", $(this).val());
            if ($(this).val() !== "") {
                $('#memberDetails').show();
                $('#memberName').text($(this).find(':selected').text());
                let subsAmount = $(this).find(':selected').data('subs-amount') || 0;
                $('#currentBalance').text(parseFloat(subsAmount).toLocaleString('id-ID'));
                $('#nominal').prop('disabled', false);
            } else {
                $('#memberDetails').hide();
                $('#nominal').prop('disabled', true);
                $('#submitBtn').prop('disabled', true);
                $('#calculation').hide();
            }
            toggleSubmit();
            updateCalculation();
        });

        // Event listener untuk perubahan pada input nominal
        $(document).on('input', '#nominal', function() {
            console.log("Nominal diubah:", $(this).val());
            toggleSubmit();
            updateCalculation();
        });

        // Panggil toggleSubmit() di awal (pastikan skrip diletakkan di bawah DOM)
        toggleSubmit();
    </script>
@endpush
