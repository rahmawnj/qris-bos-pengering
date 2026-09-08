@props([
    'items' => ['Admin', 'Tipe Layanan', 'Ubah Tipe Layanan'],
    'title' => 'Ubah Tipe Layanan',
    'subtitle' => 'Perbarui Tipe Layanan'
])
@extends('layouts.dashboard.app')
@section('title', $title ?? '')
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
        </div>
    </div>
    <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.service_types.update', $serviceType) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label for="name" class="form-label">Service Type Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" value="{{ old('name', $serviceType->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

    

            <button type="submit" class="btn btn-primary">Update</button>
            <a href="{{ route('admin.service_types.index') }}" class="btn btn-default">Cancel</a>
        </form>
    </div>
</div>
@endsection
