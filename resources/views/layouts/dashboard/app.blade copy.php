<!DOCTYPE html>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">


<!-- Mirrored from thewebmax.org/jobzilla/dash-candidates.html by HTTrack Website Copier/3.x [XR&CO'2017], Sun, 26 Mar 2023 09:27:28 GMT -->

<head>

    <!-- META -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    {{-- <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta name="robots" content="" />
    <meta name="description" content="" /> --}}

    <!-- FAVICONS ICON -->
    <link rel="icon" href="{{ asset('storage/' . App\Models\Setting::where('key', 'logo')->first()->value) }}"
        type="image/x-icon" />
    <link rel="shortcut icon" type="image/x-icon"
        href="{{ asset('storage/' . App\Models\Setting::where('key', 'logo')->first()->value) }}" />

    <!-- PAGE TITLE HERE -->
    <title>{{ App\Models\Setting::where('key', 'name')->first()->value ?? config('app.name', 'Rupa Karir') }} |
        @yield('title')</title>

    <!-- MOBILE SPECIFIC -->
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <!-- BOOTSTRAP STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/font-awesome.min.css') }}">
    <!-- FONTAWESOME STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/feather.css') }}"><!-- FEATHER ICON SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/owl.carousel.min.css') }}">
    <!-- OWL CAROUSEL STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/magnific-popup.min.css') }}">
    <!-- MAGNIFIC POPUP STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/lc_lightbox.css') }}"><!-- Lc light box popup -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/bootstrap-select.min.css') }}">
    <!-- BOOTSTRAP SLECT BOX STYLE SHEET  -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/dataTables.bootstrap5.min.css') }}">
    <!-- DATA table STYLE SHEET  -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/select.bootstrap5.min.css') }}">
    <!-- DASHBOARD select bootstrap  STYLE SHEET  -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/dropzone.css') }}"><!-- DROPZONE STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/scrollbar.css') }}">
    <!-- CUSTOM SCROLL BAR STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/datepicker.css') }}">
    <!-- DATEPICKER STYLE SHEET -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/flaticon.css') }}"> <!-- Flaticon -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/swiper-bundle.min.css') }}">
    <!-- Swiper Slider -->
    <link rel="stylesheet" type="text/css" href="{{ asset('assets/css/style.css') }}"><!-- MAIN STYLE SHEET -->

    @stack('styles')

</head>

<body>

    <!-- LOADING AREA START ===== -->
    <!-- <div class="loading-area">
        <div class="loading-box"></div>
        <div class="loading-pic">
            <div class="wrapper">
                <div class="cssload-loader"></div>
            </div>
        </div>
    </div> -->
    <!-- LOADING AREA  END ====== -->

    <div class="page-wraper">

        <!-- HEADER -->

        @include('layouts.dashboard._partials.headbar')

        <!-- Sidebar Holder -->
        <!-- SIDEBAR -->
        @include('layouts.dashboard._partials.sidebar')

        <!-- Page Content Holder -->
        <div id="content">

            @yield('container')

        </div>

        <!--Delete Profile Popup-->
        <div class="modal fade twm-model-popup" id="delete-dash-profile" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="modal-title">Do you want to delete your profile?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="site-button" data-bs-dismiss="modal">No</button>
                        <button type="button" class="site-button outline-primary">Yes</button>
                    </div>
                </div>
            </div>
        </div>


        <!--Logout Profile Popup-->
        <div class="modal fade twm-model-popup" id="logout-dash-profile" data-bs-backdrop="static"
            data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <h4 class="modal-title">Do you want to Logout your profile?</h4>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="site-button" data-bs-dismiss="modal">No</button>
                        <button type="button" class="site-button outline-primary">Yes</button>
                    </div>
                </div>
            </div>
        </div>

    </div>



    <!-- JAVASCRIPT  FILES ========================================= -->
    <script src="{{ asset('assets/js/jquery-3.6.0.min.js') }}"></script><!-- JQUERY.MIN JS -->
    <script src="{{ asset('assets/js/popper.min.js') }}"></script><!-- POPPER.MIN JS -->
    <script src="{{ asset('assets/js/bootstrap.min.js') }}"></script><!-- BOOTSTRAP.MIN JS -->
    <script src="{{ asset('assets/js/magnific-popup.min.js') }}"></script><!-- MAGNIFIC-POPUP JS -->
    <script src="{{ asset('assets/js/waypoints.min.js') }}"></script><!-- WAYPOINTS JS -->
    <script src="{{ asset('assets/js/counterup.min.js') }}"></script><!-- COUNTERUP JS -->
    <script src="{{ asset('assets/js/waypoints-sticky.min.js') }}"></script><!-- STICKY HEADER -->
    <script src="{{ asset('assets/js/isotope.pkgd.min.js') }}"></script><!-- MASONRY  -->
    <script src="{{ asset('assets/js/imagesloaded.pkgd.min.js') }}"></script><!-- MASONRY  -->
    <script src="{{ asset('assets/js/owl.carousel.min.js') }}"></script><!-- OWL  SLIDER  -->
    <script src="{{ asset('assets/js/theia-sticky-sidebar.js') }}"></script><!-- STICKY SIDEBAR  -->
    <script src="{{ asset('assets/js/lc_lightbox.lite.js') }}"></script><!-- IMAGE POPUP -->
    <script src="{{ asset('assets/js/bootstrap-select.min.js') }}"></script><!-- Form js -->
    <script src="{{ asset('assets/js/dropzone.js') }}"></script><!-- IMAGE UPLOAD  -->
    <script src="{{ asset('assets/js/jquery.scrollbar.js') }}"></script><!-- scroller -->
    <script src="{{ asset('assets/js/bootstrap-datepicker.js') }}"></script><!-- scroller -->
    <script src="{{ asset('assets/js/jquery.dataTables.min.js') }}"></script><!-- Datatable -->
    <script src="{{ asset('assets/js/dataTables.bootstrap5.min.js') }}"></script><!-- Datatable -->
    <script src="{{ asset('assets/js/chart.js') }}"></script><!-- Chart -->
    <script src="{{ asset('assets/js/bootstrap-slider.min') }}"></script><!-- Price range slider -->
    <script src="{{ asset('assets/js/swiper-bundle.min') }}"></script><!-- Swiper JS -->
    <script src="{{ asset('assets/js/custom.js') }}"></script><!-- CUSTOM FUCTIONS  -->
    @stack('scripts')


</body>


<!-- Mirrored from thewebmax.org/jobzilla/dash-candidates.html by HTTrack Website Copier/3.x [XR&CO'2017], Sun, 26 Mar 2023 09:27:28 GMT -->

</html>
