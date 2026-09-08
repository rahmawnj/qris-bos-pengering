@php
    $title = 'domicile';
@endphp
@extends('layouts.dashboard.app')
@section('title', $title ?? '')

@section('content')
    <!-- BEGIN breadcrumb -->
    {{-- <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="javascript:;">Home</a></li>
        <li class="breadcrumb-item"><a href="javascript:;">Page Options</a></li>
        <li class="breadcrumb-item active">Blank Page</li>
    </ol>
    <!-- END breadcrumb -->
    <!-- BEGIN page-header -->
    <h1 class="page-header">Blank Page <small>header small text goes here...</small></h1>
    <!-- END page-header -->
    <!-- BEGIN panel --> --}}
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
            <form action="{{ route('domiciles.update', $domicile) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="city" class a="form-label">City</label>
                    <input type="text" class="form-control @error('city') is-invalid @enderror" id="city"
                        name="city" value="{{ old('city', $domicile->city) }}" required>
                    @error('city')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <script>
                    document.getElementById('city').addEventListener('input', function() {
                        this.value = this.value.toLowerCase();
                    });
                </script>
                <button type="submit" class="btn btn-primary">Update</button>
                <a href="{{ route('domiciles.index') }}" class="btn btn-default">Cancel</a>
            </form>

        </div>
    </div>
    <!-- END panel -->
@endsection
