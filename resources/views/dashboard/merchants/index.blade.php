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
            <h4 class="panel-title">Panel Title here</h4>
            <div class="panel-heading-btn">
                <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                        class="fa fa-expand"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                        class="fa fa-redo"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-warning" data-toggle="panel-collapse"><i
                        class="fa fa-minus"></i></a>
                <a href="javascript:;" class="btn btn-xs btn-icon btn-danger" data-toggle="panel-remove"><i
                        class="fa fa-times"></i></a>
                <a href="{{ route('merchants.create') }}" class="btn btn-xs btn-primary"><i class="fa fa-plus"></i></a>
            </div>
        </div>
        <div class="panel-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Address</th>
                            <th>Contact</th>
                            <th>User ID</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($merchants as $merchant)
                            <tr>
                                <td>{{ $merchant->id }}</td>
                                <td>{{ $merchant->name }}</td>
                                <td>{{ $merchant->address }}</td>
                                <td>{{ $merchant->contact }}</td>
                                <td>{{ $merchant->user_id }}</td>
                                <td>
                                    <a href="{{ route('merchants.edit', $merchant->id) }}"
                                        class="btn btn-sm btn-primary">Edit</a>
                                    <form action="{{ route('merchants.destroy', $merchant->id) }}" method="POST"
                                        style="display: inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this merchant?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- END panel -->
@endsection
