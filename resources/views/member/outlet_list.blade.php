@props([
    'items' => ['Admin', 'Owner Management', 'Owner List'],
    'title' => 'Owner List',
    'subtitle' => 'Manage registered Owner here'
])

@extends('layouts.dashboard.app')

@section('content')

<div class="row">
    <div class="col">
       @foreach (App\Models\Owner::all() as $owner)
        <div class="widget-list rounded">
        <!-- begin widget-list-item -->
        <a href="#" class="widget-list-item">
            <div class="widget-list-media icon">
                <div class="widget-img rounded bg-dark" style="background-image: url({{ $owner->brand_logo ? asset('storage/' . $owner->brand_logo) : asset('assets/img/placeholder/placeholder.png') }})"></div>
            </div>
            <div class="widget-list-content">
            <h2 class="widget-list-title">{{ $owner->brand_name }}</h2>
            <h5> {{ $owner->outlets()->count() }} Outlet</h5>
            </div>
            <div class="widget-list-action text-nowrap text-body text-opacity-50 fw-bold text-decoration-none">
       @php
    $subscription = auth()->user()->member->owners->firstWhere('id', $owner->id);
@endphp

@if(!$subscription)
    <form action="{{ route('member.subscription.store') }}" method="POST">
        @csrf
        <input type="hidden" name="owner_id" value="{{ $owner->id }}">
        <button type="submit" class="btn btn-primary btn-sm">
            <i class="fa fa-angle-right text-body text-opacity-30 fa-lg"></i> Subscribe
        </button>
    </form>
@elseif(!$subscription->pivot->is_verified)
    <button class="btn btn-warning btn-sm" disabled>
        <i class="fa fa-clock text-body text-opacity-30 fa-lg"></i> Menunggu Verifikasi
    </button>
@endif


            </div>
        </a>
        </div>
       @endforeach
    </div>
</div>

@endsection

