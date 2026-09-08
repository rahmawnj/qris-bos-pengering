@props([
    'title' => 'Member Dashboard',
])

@php

@endphp

@extends('layouts.dashboard.app')

@push('styles')
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
@endpush

@section('content')
    <div class="row">
<div class="col-12">
    	<div class="card border-0 mb-3 bg-gray-900 text-white">
						<!-- BEGIN card-body -->
						<div class="card-body" style="background: no-repeat bottom right; background-image: url(../assets/img/svg/img-4.svg); background-size: auto 60%;">
							<!-- BEGIN title -->
							<div class="mb-3 text-gray-500 ">
								<b>Membership</b>
								<span class="text-gray-500 ms-2"><i class="fa fa-info-circle" data-bs-toggle="popover" data-bs-trigger="hover" data-bs-title="Sales by social source" data-bs-placement="top" data-bs-content="Total online store sales that came from a social referrer source."></i></span>
							</div>
							<!-- END title -->
							<!-- BEGIN sales -->
							<h3 class="mb-10px"><span data-animation="number" data-value="{{ auth()->user()->member->owners->count() }}">{{auth()->user()->member->owners->count()}}</span></h3>

						</div>
						<!-- END card-body -->
						<!-- BEGIN widget-list -->
						<div class="widget-list rounded-bottom" data-bs-theme="dark">

                            @foreach (auth()->user()->member->owners as $item)
							<a href="#" class="widget-list-item">
								<div class="widget-list-media icon">
									<div class="widget-img rounded bg-dark" style="background-image: url({{ $item->brand_logo ? asset('storage/' . $item->brand_logo) : asset('assets/img/placeholder/placeholder.png') }})">
                                        <iconify-icon icon="mdi:account" width="24" height="24"></iconify-icon></div>
								</div>
								<div class="widget-list-content">
									<div class="widget-list-title">{{ $item->brand_name }}</div>
								</div>
								<div class="widget-list-action text-nowrap text-gray-500">
									Rp<span data-animation="number" data-value="{{ $item->pivot->amount }}">{{ $item->pivot->amount }}</span>
								</div>
							</a>
                            @endforeach

						</div>
						<!-- END widget-list -->
					</div>
</div>
    </div>
@endsection

