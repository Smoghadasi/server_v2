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

    <title>ایران ترابر</title>

    <meta name="description" content=""/>

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

    <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>


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

        <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
            <div class="app-brand demo">
                <a href="{{ url('/dashboard') }}" class="app-brand-link">
              <span class="app-brand-logo demo">
                <img src="{{ asset('icons/irt.png') }}" width="40" height="40"/>
              </span>
                    <span class="app-brand-text demo menu-text fw-bolder ms-2">ایران ترابر</span>
                </a>
                <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
                    <i class="bx bx-chevron-left bx-sm align-middle"></i>
                </a>
            </div>

            <div class="menu-inner-shadow"></div>

            <ul class="menu-inner py-1">
                <!-- Dashboard -->
                @if(in_array('dashboard',auth()->user()->userAccess))
                    <li class="menu-item">
                        <a href="{{ url('/dashboard') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-home-circle"></i>
                            <div data-i18n="Analytics">داشبورد</div>
                        </a>
                    </li>
                @endif


                <li class="menu-item">
                    <a class="menu-link" href="{{ url('admin/finalApprovalAndStoreCargo') }}">
                        <i class="menu-icon tf-icons bx bx-box"></i>
                        <div data-i18n="Without menu">تایید و ثبت دسته ای بار</div>
                    </a>
                </li>
                @if(in_array('driversAuthentication',auth()->user()->userAccess))
                    <li class="menu-item ">
                        <a href="{{ url('admin/driversAuthenticationByOperator') }}" class="menu-link">
                            <i class="menu-icon tf-icons bx bx-user-check"></i>
                            <div data-i18n="Analytics">احراز هویت رانندگان</div>
                        </a>
                    </li>
                @endif

                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bxs-category-alt"></i>
                        <div data-i18n="pais">بار ها</div>
                    </a>

                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a class="menu-link" href="{{ url('admin/rejectedCargoFromCargoList') }}">
                                <div data-i18n="Without menu"> بارهای رد شده</div>
                            </a>
                        </li>


                        @if(in_array('loads',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/loads') }}">
                                    <div data-i18n="Without menu"> گزارش بار ها</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/storeCargoConvertForm') }}">
                                    <div data-i18n="Without menu">ثبت بار</div>
                                </a>
                            </li>
                            @if(in_array('loadOwner',auth()->user()->userAccess))
                                <li class="menu-item">
                                    <a class="menu-link" href="{{ route('admin.loadBackup') }}">
                                        <div data-i18n="Without menu">بار های ثبت شده توسط صاحبین بار</div>
                                    </a>
                                </li>
                            @endif

                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('accept.cargo.index') }}">
                                    <div data-i18n="Without menu">تایید بار ها</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/dictionary') }}">
                                    <div data-i18n="Without menu">کلمات معادل در ثبت بار</div>
                                </a>
                            </li>
                        @endif
                        @if(in_array('listOfLoadsByOperator',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/listOfLoadsByOperator') }}">
                                    <span>بارها به تفکیک اپراتور</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                        <div data-i18n="Layouts">گزارش ها</div>
                    </a>

                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a class="menu-link" href="{{ route('report.cargo.fleets') }}">
                                <span>گزارش بار ها به تفکیک ناوگان</span>
                            </a>
                        </li>
                        @if(in_array('freeSubscription',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('freeSubscription.index') }}">
                                    <span>اشتراک و تماس رایگان</span>
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->role == ROLE_ADMIN || auth()->id() == 29)
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/driverActivityReport') }}">
                                    <div data-i18n="Without menu">گزارش فعالیت رانندگان</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/driversPaymentReport') }}">
                                    <div data-i18n="Without menu">گزارش پرداخت رانندگان</div>
                                </a>
                            </li>
                        @endif

                        @if(auth()->user()->role == ROLE_ADMIN)
                            <li class="menu-item">
                                <a href="{{ url('admin/summaryOfDaysReport') }}" class="menu-link">
                                    <div data-i18n="Without menu">خلاصه گزارش روز</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a href="{{ route('report.driversContactCall') }}" class="menu-link">
                                    <div data-i18n="Without menu">گزارش رانندگان بر اساس تماس</div>
                                </a>
                            </li>


                            <li class="menu-item">
                                <a class="menu-link"
                                href="{{ url('admin/transportationCompaniesActivityReport') }}">
                                    <div data-i18n="Without menu">گزارش فعالیت باربری ها</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/cargoOwnersActivityReport') }}">
                                    <div data-i18n="Without menu"> فعالیت صاحب بارها</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/operatorsActivityReport') }}">
                                    <div data-i18n="Without menu"> فعالیت اپراتورها</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/combinedReports') }}">
                                    <div data-i18n="Without menu"> گزارش های ترکیبی</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/driverInstallationInLast30Days') }}">
                                    <div data-i18n="Without menu"> نصب رانندگان در 30 روز</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link"
                                href="{{ url('admin/fleetRatioToDriverActivityReport') }}">
                                    <div data-i18n="Without menu">نسبت راننده به بار</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>

                @if(in_array('paymentReport',auth()->user()->userAccess))

                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-credit-card"></i>
                            <div data-i18n="pais">پرداخت ها</div>
                        </a>

                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/paymentReport') }}/{{ ROLE_DRIVER }}/100">
                                    <div data-i18n="Without menu">راننده ها</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/mostPaidDriversReport') }}">
                                    <div data-i18n="Without menu">بیشترین پرداخت رانندگان</div>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/paymentByFleetReport') }}">
                                    <div data-i18n="Without menu">پرداخت براساس ناوگان</div>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if(in_array('complaints',auth()->user()->userAccess))


                    <li class="menu-item">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            <i class="menu-icon tf-icons bx bx-printer"></i>
                            <div data-i18n="pais">شکایات و انتقادات</div>
                        </a>

                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/complaintsDriversList') }}">
                                    <div data-i18n="Without menu"> رانندگان</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/complaintsTransportationCompanyList') }}">
                                    <div data-i18n="Without menu"> باربری ها</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/complaintsCustomerList') }}">
                                    <div data-i18n="Without menu">صاحب بار</div>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/blockedPhoneNumbers') }}">
                                    <span>شماره تلفن های مسدود</span>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/blockedIps') }}">
                                    <span>IP های های مسدود</span>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/messages') }}">
                                    <span>پیام ها</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                @endif
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bxs-car"></i>
                        <div data-i18n="Layouts">ناوگان</div>
                    </a>

                    <ul class="menu-sub">
                        @if(in_array('fleet',auth()->user()->userAccess) || auth()->user()->role == 'admin')
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('fleet.index') }}">
                                    <span>ناوگان ها</span>
                                </a>
                            </li>
                        @endif

                        <li class="menu-item">
                            <a class="menu-link" href="{{ url('admin/operatorFleets') }}">
                                <span>ناوگان من</span>
                            </a>
                        </li>
                    </ul>
                </li>
                <li class="menu-item">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        <i class="menu-icon tf-icons bx bx-check-shield"></i>
                        <div data-i18n="pais">امکانات</div>
                    </a>

                    <ul class="menu-sub">
                        @if(auth()->user()->role == ROLE_ADMIN || auth()->id() == 29)
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/operatorsWorkingHoursActivityReport') }}">
                                    <div data-i18n="Without menu">میزان فعالیت اپراتورها</div>
                                </a>
                            </li>
                        @endif

                        @if(in_array('searchLoads',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/searchLoadsForm') }}">
                                    <span>جستحوی بارها</span>
                                </a>
                            </li>
                        @endif

                        @if(in_array('operators',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/operators') }}">
                                    <span>اپراتورها</span>
                                </a>
                            </li>
                        @endif
                        @if(in_array('drivers',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('drivers') }}">
                                    <span>رانندگان</span>
                                </a>
                            </li>
                        @endif


                        @if(in_array('contactReportWithCargoOwners',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/contactReportWithCargoOwners') }}">
                                    <span>تماس با صاحب بار و باربری</span>
                                </a>
                            </li>

                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/contactingWithDrivers') }}">
                                    <span>تماس با رانندگان</span>
                                </a>
                            </li>
                        @endif


                        @if(in_array('appVersions',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/appVersions') }}">
                                    <span>ورژن اپلیکیشن ها</span>
                                </a>
                            </li>
                        @endif
                        @if(in_array('provincesAndCities',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/provincesAndCities') }}">
                                    <span>استان ها و شهرها</span>
                                </a>
                            </li>
                        @endif

                        @if(in_array('SOSList',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/SOSList/0') }}">
                                    <div data-i18n="Without menu">درخواست های امداد</div>
                                </a>
                            </li>
                        @endif


                        @if(in_array('services',auth()->user()->userAccess))
                            <li class="menu-item">
                                <a class="menu-link" href="{{ url('admin/services') }}">
                                    <div data-i18n="Without menu">خدمات</div>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>


            </ul>
        </aside>
        <!-- / Menu -->

        <!-- Layout container -->
        <div class="layout-page">
            <!-- Navbar -->

            <nav
                class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
                id="layout-navbar">
                <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                    <a class="menu-item menu-link px-0 me-xl-4" href="javascript:void(0)">
                        <i class="bx bx-menu bx-sm"></i>
                    </a>
                </div>
                <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                    <!-- Search -->
                    <div class="navbar-nav align-items-center">
                        <div class="menu-item d-flex align-items-center">
                            {{--                            <i class="bx bx-search fs-4 lh-0"></i>--}}
                            <input type="text" class="form-control border-0 shadow-none" placeholder="جستجو..."
                                   aria-label="Search...">
                        </div>
                    </div>
                    <!-- /Search -->
                    <ul class="navbar-nav flex-row align-items-center mr-auto f-ir">
                        <!-- Place this tag where you want the button to render. -->
                        <li class="menu-item lh-1 me-3 f-ir">
                            <a class="f-ir" href="#"> <i class="bx bx-star fs-4 lh-0"></i> امتیاز</a>
                        </li>
                        <!-- User -->
                        <li class="menu-item navbar-dropdown dropdown-user dropdown">
                            <a class="menu-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                               data-bs-toggle="dropdown">
                                <div class="avatar avatar-online">
                                    <img src="{{ asset('pictures/users/user.png') }}" alt
                                         class="w-px-40 h-auto rounded-circle">
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end new-style-13">
                                <li>
                                    <a class="dropdown-item" href="#">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 me-3">
                                                <div class="avatar avatar-online">
                                                    <img src="{{ asset('pictures/users/user.png') }}" alt
                                                         class="w-px-40 h-auto rounded-circle">
                                                </div>
                                            </div>
                                            <div class="flex-grow-1">
                                                <span class="fw-semibold d-block">
                                                    {{ auth()->user()->name }} {{ auth()->user()->lastName }}
                                                </span>
                                                <small class="text-muted">
                                                    @if(auth()->user()->role == ROLE_ADMIN)
                                                        مدیر
                                                    @elseif(auth()->user()->role == ROLE_OPERATOR)
                                                        کارشناس
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('admin/profile') }}">
                                        <i class="bx bx-user me-2"></i>
                                        <span class="align-middle">پروفایل من</span>
                                    </a>
                                    {{--                                </li>--}}
                                    {{--                                <li>--}}
                                    {{--                                    <a class="dropdown-item" href="#">--}}
                                    {{--                                        <i class="bx bx-cog me-2"></i>--}}
                                    {{--                                        <span class="align-middle">تنظیمات</span>--}}
                                    {{--                                    </a>--}}
                                    {{--                                </li>--}}
                                    {{--                                <li>--}}
                                    {{--                                    <a class="dropdown-item" href="#">--}}
                                    {{--                                    <span class="d-flex align-items-center align-middle">--}}
                                    {{--                                <i class="flex-shrink-0 bx bx-credit-card me-2"></i>--}}
                                    {{--                                <span class="flex-grow-1 align-middle">صورتحساب</span>--}}
                                    {{--                                    <span--}}
                                    {{--                                        class="flex-shrink-0 badge badge-center rounded-pill bg-danger w-px-20 h-px-20">4</span>--}}
                                    {{--                                    </span>--}}
                                    {{--                                    </a>--}}
                                </li>
                                <li>
                                    <div class="dropdown-divider"></div>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ url('admin/logout') }}">
                                        <i class="bx bx-power-off me-2"></i>
                                        <span class="align-middle">خروج</span>
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <!--/ User -->
                    </ul>
                </div>
            </nav>

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
