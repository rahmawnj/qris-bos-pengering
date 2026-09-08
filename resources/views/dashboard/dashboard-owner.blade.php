@php
    $title = 'Dashboard';
@endphp
@extends('layouts.dashboard.app')
@section('title', $title ?? '')

@push('styles')
@endpush
@section('content')

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
                                    <td>{{ number_format($total['total'], 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- END panel -->


            <!-- END panel -->


            <!-- END panel -->
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
                type: 'line', // Using line chart
            },
            dataLabels: {
                enabled: false
            },
            stroke: {
                curve: 'smooth',
                width: 3
            },
            series: {!! json_encode($chartData) !!}, // Insert chart data from the controller
            xaxis: {
                type: 'datetime',
                labels: {
                    format: 'yyyy-MM-dd', // Format the date labels on x-axis
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
