<!DOCTYPE html>

<!-- =========================================================
* Sneat - Bootstrap 5 HTML Admin Template - Pro | v1.0.0
==============================================================

* Product Page: https://themeselection.com/products/sneat-bootstrap-html-admin-template/
* Created by: ThemeSelection
* License: You must have a valid license purchased in order to legally use the theme for your project.
* Copyright ThemeSelection (https://themeselection.com)

=========================================================
 -->
<!-- beautify ignore:start -->
<html
    lang="fa"
    class="light-style layout-menu-fixed"
    dir="rtl"
    data-theme="theme-default"
    data-assets-path="assets-sneat/"
    data-template="vertical-menu-template-free"
>
<head>
    <meta charset="utf-8"/>
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0"
    />

    <title>ایران ترابر @yield('title')</title>

    <meta name="description" content=""/>
    <meta name="robots" content="NOINDEX, NOFOLLOW">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('icons/irt.png') }}"/>


    <!-- Icons. Uncomment required icon fonts -->
    <link rel="stylesheet" href="{{ asset('assets-sneat/vendor/fonts/boxicons.css') }}"/>

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('assets-sneat/vendor/css/core.css') }}" class="template-customizer-core-css"/>
    <link rel="stylesheet" href="{{ asset('assets-sneat/vendor/css/theme-default.css') }}"
          class="template-customizer-theme-css"/>
    <link rel="stylesheet" href="{{ asset('assets-sneat/css/demo.css') }}"/>

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('assets-sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}"/>

    <link rel="stylesheet" href="{{ asset('assets-sneat/vendor/libs/apex-charts/apex-charts.css') }}"/>
    <link rel="stylesheet" href="{{ asset('css/persianDatepicker-default.css') }}"/>

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('assets-sneat/vendor/js/helpers.js') }}"></script>

    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
    <script src="{{ asset('assets-sneat/js/config.js') }}"></script>

    <script src="{{ asset('assets/js/sweetAlert.js') }}"></script>

    {{-- <link rel="stylesheet" href="{{ asset('css/persian-datepicker.css') }}"/>
    <script src="{{ asset('assets/js/persian-date.js') }}"></script>
    <script src="{{ asset('assets/js/persian-datepicker.js') }}"></script> --}}

    <link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet"/>

    {{-- <script type="text/javascript">
        $(document).ready(function () {
            $("#loadingDate").pDatepicker({
                observer: true
                , format: 'L'
            });
            $("#persianDate").pDatepicker({
                observer: true
                , format: 'L'
            });
        });

    </script> --}}


    @if(isset($sosInfo))

        <script>
            function loadMap() {
                var mapOptions = {
                    center: new google.maps.LatLng({
                {
                    $sosInfo->latitude
                }
            },
                {
                    {
                        $sosInfo->longitude
                    }
                }
            )
            ,
                zoom: 15
                    , mapTypeId
            :
                google.maps.MapTypeId.ROADMAP
            }
                ;
                var map = new google.maps.Map(document.getElementById("map"), mapOptions);
                var marker = new google.maps.Marker({
                    position: new google.maps.LatLng({
                {
                    $sosInfo - > latitude
                }
            },
                {
                    {
                        $sosInfo - > longitude
                    }
                }
            )
            ,
                map: map,
            })
                ;
            }

            google.maps.event.addDomListener(window, 'load', loadMap);

        </script>
    @endif

    {{-- <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script> --}}


    <style>
        body, span, div, ol, li, ul, th, td, textarea, input, .form-control {
            color: #000000;
        }
    </style>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css"/>


</head>
<script src="{{ asset('js/chart.js') }}"></script>
<body dir="rtl">
<!-- Layout wrapper -->
<div class="layout-wrapper layout-content-navbar">
    <div class="layout-container">
        <!-- Menu -->

        @include('partials.sidebar')
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            @include('partials.header')

            <!-- / Navbar -->

            <!-- Content wrapper -->
            <div class="content-wrapper">
                <!-- Content -->

                <div class="container-xxl flex-grow-1 container-p-y">

                    @if(session('success'))
                        <div class="alert alert-success text-center">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('danger'))
                        <div class="alert alert-danger text-center">
                            {{ session('danger') }}
                        </div>
                    @endif

                    @yield('content')


                </div>
                <!-- / Content -->

                <!-- Footer -->
                <footer class="content-footer footer bg-footer-theme">

                </footer>
                <!-- / Footer -->

                <div class="content-backdrop fade"></div>
            </div>
            <!-- Content wrapper -->
        </div>
        <!-- / Layout page -->
    </div>

    <!-- Overlay -->
    <div class="layout-overlay layout-menu-toggle"></div>
</div>
<!-- / Layout wrapper -->

<div class="buy-now">

</div>

<!-- Core JS -->
<!-- build:js assets/vendor/js/core.js -->
<script src="{{ asset('assets-sneat/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets-sneat/vendor/libs/popper/popper.js') }}"></script>
<script src="{{ asset('assets-sneat/vendor/js/bootstrap.js') }}"></script>
<script src="{{ asset('assets-sneat/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>

<script src="{{ asset('assets-sneat/vendor/js/menu.js') }}"></script>
<!-- endbuild -->

<!-- Vendors JS -->
<script src="{{ asset('assets-sneat/vendor/libs/apex-charts/apexcharts.js') }}"></script>

<!-- Main JS -->
<script src="{{ asset('assets-sneat/js/main.js') }}"></script>

<!-- Page JS -->
<script src="{{ asset('assets-sneat/js/dashboards-analytics.js') }}"></script>

<!-- Place this tag in your head or just before your close body tag. -->
<script async defer src="https://buttons.github.io/buttons.js"></script>


<script src="{{ asset('assets/js/select2.js') }}"></script>
<script src="{{ asset('assets/js/fa.js') }}"></script>
@yield('script')

<script>
    $('#destination_city_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#city_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#state_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#origin_city_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#bearing_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#customer_id').select2({
        dir: "rtl"
        , language: "fa"
    });
    $('#fleet_id').select2({
        dir: "rtl"
        , language: "fa"
    });

</script>
<script src="{{ asset('/assets/js/actions.js') }}"></script>
<script>
    function toggleMenu(menu) {
        if (menu === "complaint")
            $("#complaint").toggle();
        if (menu === "paymentReport")
            $("#paymentReport").toggle();
        if (menu === "reports")
            $("#reports").toggle();
        if (menu === "channels")
            $("#channels").toggle();

    }
</script>
<script src="{{ asset('/assets/js/addNewLoadActions.js') }}"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<script>

    let table = new DataTable('#myTable', {
        paging: false,
    });
</script>
<script>
    var url = window.location;
    // $('ul.list-unstyled a[href="' + url + '"]').parent().addClass('active');
    $('li.menu-item a').filter(function() {
        return this.href == url;
    }).parent().parent().parent().addClass('active open');
    $('li.menu-item a').filter(function() {
        return this.href == url;
    }).parent().addClass('active');
</script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
<script>

    let table = new DataTable('#myTable', {
        paging: false,
    });
