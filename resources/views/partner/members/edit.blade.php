@props([
    'items' => ['Admin', 'Manajemen Outlet', 'Edit Outlet'],
    'title' => 'Edit Outlet',
    'subtitle' => 'Perbarui informasi Outlet'
])
@extends('layouts.dashboard.app')

@section('content')
<x-breadcrumb :items="$items" :title="$title" :subtitle="$subtitle" />


<div class="panel panel-inverse">
    <div class="panel-heading">
        <h4 class="panel-title">{{ $title }}</h4>
        <div class="panel-heading-btn">
            <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand">
                <i class="fa fa-expand"></i>
            </a>
            <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload">
                <i class="fa fa-redo"></i>
            </a>
        </div>
    </div>
    <div class="panel-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('partner.members.update', [ 'member' => $member, 'out' => request()->get('out')]) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nama Member <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                       name="name" value="{{ old('name', $member->name) }}" required>
                @error('name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="rfid" class="form-label">RFID <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('rfid') is-invalid @enderror" id="rfid"
                       name="rfid" value="{{ old('rfid', $member->rfid) }}" required>
                @error('rfid')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update
            </button>
            <a href="{{ route('admin.members.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Batal
            </a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var inputIds = ['rfid', 'name'];
        inputIds.forEach(function(id) {
            var input = document.getElementById(id);
            if (input) {
                input.addEventListener('keydown', function(event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });
            }
        });
    });
</script>

@endpush
