@extends('layouts.dashboard.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        @foreach($outlets as $outlet)
            <div class="col-md-6 mb-4 d-flex align-items-stretch">
                <div class="card w-100 shadow-sm">
                    <div class="row no-gutters h-100">
                        <div class="col-md-4 bg-light d-flex align-items-center">
                            <div class="p-3 text-center w-100">
                                <div class="mb-3">
                                    <i class="fa fa-store-alt fa-2x text-primary"></i>
                                </div>
                                <h6 class="mb-1 text-uppercase text-secondary">
                                    Outlet
                                </h6>
                                <h2 class="h5 mb-0">
                                    {{ $outlet->outlet_name }}
                                </h2>
                                <p class="text-muted small mb-0">#{{ $outlet->code }}</p>
                            </div>
                        </div>
                        <div class="col-md-8 d-flex">
                            <div class="card-body d-flex flex-column">

                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><strong>Jumlah Mesin</strong></span>
                                    <span>{{ $outlet->devices()->count() }}</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span><strong>Timezone</strong></span>
                                    <span>{{ $outlet->timezone }}</span>
                                </div>
                                <div class="d-flex justify-content-center small text-muted mb-1">
                                    <span>{{ $outlet->address }}</span>
                                </div>

                                <a href="{{ route('partner.dashboard', ['out' => $outlet->code]) }}" class="btn btn-primary btn-block mt-auto">
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div><!-- end row no-gutters -->
                </div><!-- end card -->
            </div><!-- end col -->
        @endforeach
    </div><!-- end row -->
</div><!-- end container-fluid -->
@endsection
