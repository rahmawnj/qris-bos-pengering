<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title> Topup | {{ $title ?? '' }}</title>
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" name="viewport" />
    <meta content="" name="description" />
    <meta content="" name="author" />

    <!-- ================== BEGIN core-css ================== -->
    <link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/facebook/app.min.css') }}" rel="stylesheet" />
    @stack('styles')
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('assets/img/logo.png') }}">

    <!-- ================== END core-css ================== -->
</head>

<body>
    <!-- BEGIN #loader -->
    {{-- <div id="loader" class="app-loader">
        <span class="spinner"></span>
    </div> --}}
    <!-- END #loader -->

    <!-- BEGIN #app -->
    <div id="app" class="app app-header-fixed app-sidebar-fixed">
        <!-- BEGIN #header -->
        @include('layouts.dashboard._partials.headbar')
        <!-- END #header -->

        <!-- BEGIN #sidebar -->
        @include('layouts.dashboard._partials.sidebar')
        <div class="app-sidebar-bg"></div>
        <div class="app-sidebar-mobile-backdrop"><a href="#" data-dismiss="app-sidebar-mobile"
                class="stretched-link"></a></div>
        <!-- END #sidebar -->

        <!-- BEGIN #content -->
        <div id="content" class="app-content">
            @yield('content')

            @include('layouts.dashboard._partials.footbar')

        </div>
        <!-- END #content -->


    </div>
    <!-- END #app -->

    <!-- ================== BEGIN core-js ================== -->
    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/js/app.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme/facebook.min.js') }}"></script>

    @stack('scripts')

    <!-- ================== END core-js ================== -->
</body>

</html>
