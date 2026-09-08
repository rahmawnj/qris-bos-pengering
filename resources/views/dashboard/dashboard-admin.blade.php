@php
    $title = 'Dashboard';
@endphp
@extends('layouts.dashboard.app')
@section('title', $title ?? '')

@push('styles')
@endpush
@section('content')
<div class="row">
    <!-- BEGIN col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="widget widget-stats bg-blue">
            <div class="stats-icon"><i class="fa fa-user-shield"></i></div> <!-- Icon untuk Admin -->
            <div class="stats-info">
                <h4>TOTAL ADMIN</h4>
                <p>{{ $totalAdmins }}</p>
            </div>
            <div class="stats-link">
                <a href="{{ route('users.index') }}">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- END col-3 -->

    <!-- BEGIN col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="widget widget-stats bg-info">
            <div class="stats-icon"><i class="fa fa-briefcase"></i></div> <!-- Icon untuk Owner -->
            <div class="stats-info">
                <h4>TOTAL OWNER</h4>
                <p>{{ $totalOwners }}</p>
            </div>
            <div class="stats-link">
                <a href="{{ route('owners.index') }}">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- END col-3 -->

    <!-- BEGIN col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="widget widget-stats bg-orange">
            <div class="stats-icon"><i class="fa fa-home"></i></div> <!-- Icon untuk Domicile -->
            <div class="stats-info">
                <h4>TRANSAKSI HARI INI</h4>
                <p>{{ $todayTransaction }}</p>
            </div>
            <div class="stats-link">
                <a href="">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- END col-3 -->

    <!-- BEGIN col-3 -->
    <div class="col-xl-3 col-md-6">
        <div class="widget widget-stats bg-red">
            <div class="stats-icon"><i class="fa fa-store-alt"></i></div> <!-- Icon untuk Outlet -->
            <div class="stats-info">
                <h4>TOTAL OUTLET</h4>
                <p>{{ $totalOutlets }}</p>
            </div>
            <div class="stats-link">
                <a href="{{ route('outlets.index') }}">View Detail <i class="fa fa-arrow-alt-circle-right"></i></a>
            </div>
        </div>
    </div>
    <!-- END col-3 -->
</div>

    <!-- END row -->

    <!-- BEGIN row -->
    <div class="row d-flex align-items-stretch">
        <div class="col-xl-8">
            <div class="panel panel-inverse" data-sortable-id="index-1">
                <div class="panel-heading">
                    <h4 class="panel-title">Website Analytics (Last 10 Days)</h4>
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                                class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                                class="fa fa-redo"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-warning" data-toggle="panel-collapse"><i
                                class="fa fa-minus"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-danger" data-toggle="panel-remove"><i
                                class="fa fa-times"></i></a>
                    </div>
                </div>
                <div class="panel-body pe-1">
                    <div id="apex-area-chart"></div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="panel panel-inverse" data-sortable-id="index-6">
                <div class="panel-heading">
                    <h4 class="panel-title">Analytics Details</h4>
                    <div class="panel-heading-btn">
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-default" data-toggle="panel-expand"><i
                                class="fa fa-expand"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-success" data-toggle="panel-reload"><i
                                class="fa fa-redo"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-warning" data-toggle="panel-collapse"><i
                                class="fa fa-minus"></i></a>
                        <a href="javascript:;" class="btn btn-xs btn-icon btn-danger" data-toggle="panel-remove"><i
                                class="fa fa-times"></i></a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-panel align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($dailyTotals as $total)
                                <tr>
                                    <td>{{ $total['date'] }}</td>
                                    <td>{{ number_format($total['total'],0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

@endsection

@push('scripts')
    <script src="{{ asset('assets/plugins/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script>
        console.log({!! json_encode($chartData) !!});

        var chartOptions = {
            chart: {
                height: 350,
                type: 'line',
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: {!! json_encode($chartData) !!},
            xaxis: {
                type: 'datetime',
                labels: {
                    format: 'yyyy-MM-dd',
                },
            },
            tooltip: {
                x: {
                    format: 'dd/MM/yy'
                }
            }
        };

        var chart = new ApexCharts(
            document.querySelector('#apex-area-chart'),
            chartOptions
        );

        chart.render();
    </script>
@endpush
