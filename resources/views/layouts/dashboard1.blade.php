<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ایران ترابر</title>

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.css') }}" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="{{ asset('assets/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">

    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap-rtl.min.css') }}" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="{{ asset('assets/css/sb-admin.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">

    {{--<script src="../../assets/vendor/jquery/jquery.min.js"></script>--}}
    <script src="{{ asset('assets/js/jquery-2.1.0.js') }}"></script>

    <script src="{{ asset('assets/js/sweetAlert.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('css/persian-datepicker.css') }}"/>
    <script src="{{ asset('assets/js/persian-date.js') }}"></script>
    <script src="{{ asset('assets/js/persian-datepicker.js') }}"></script>

    <link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet"/>

    <script type="text/javascript">
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

    </script>


    @if(isset($sosInfo))

        <script src="http://maps.googleapis.com/maps/api/js?key=AIzaSyDCmuNkf2V7_3HESXCy4XQwuNbTSS30ank"></script>

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

        @font-face {
            font-family: iran-sans1;
            src: url('{{ asset('css/font/iran-sans.eot') }}');
            src: url('{{ asset('css/font/iran-sans.eot?#iefix') }}') format('{{ 'css/font/iran-sans-opentype' }}'),
            url('{{ asset('css/font/iran-sans.woff') }}') format('woff'),
            url('{{ asset('css/font/iran-sans.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        body {
            font-family: iran-sans1;
            font-size: 14px;
        }

    </style>

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.css"/>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.js"></script>
</head>

<script src="{{ asset('js/chart.js') }}"></script>

<body id="page-top">

<nav class="navbar navbar-expand navbar-dark bg-dark static-top">

    <a class="navbar-brand mr-1" href="{{ url('/dashboard') }}">
        ایران ترابر
    </a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
        <div class="input-group">

        </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle fa-fw"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-left" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ url('admin/profile') }}">پروفایل</a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="{{ url('admin/logout') }}">خروج</a>
            </div>
        </li>
    </ul>

</nav>

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
        <li class="nav-item active">
            <a class="nav-link" href="{{ url('/dashboard') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>داشبورد</span>
            </a>
        </li>

        @if(auth()->user()->role == ROLE_ADMIN)
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/rejectedCargoFromCargoList') }}">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>بارهای رد شده</span>
                </a>
            </li>
        @endif


        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/driversAuthenticationByOperator') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>احراز هویت رانندگان</span>
            </a>
        </li>


        <div @if(auth()->id() != 21) class="d-none" @endif>
            @if(auth()->user()->role == ROLE_ADMIN)

                <li class="nav-item active">
                    <a class="nav-link" href="#" onclick="toggleMenu('reports')">
                        <i class="fas fa-fw fa-globe"></i>
                        <span>گزارش ها</span>
                        <i class="fas fa-fw fa-arrow-down float-left"></i>
                    </a>
                    <ol style="display: none" id="reports">
                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/summaryOfDaysReport') }}">
                                <span style="color: #ffffff">خلاصه گزارش روز</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/driverActivityReport') }}">
                                <span style="color: #ffffff">گزارش فعالیت رانندگان</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link small"
                               href="{{ url('admin/transportationCompaniesActivityReport') }}">
                                <span style="color: #ffffff">گزارش فعالیت باربری ها</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/cargoOwnersActivityReport') }}">
                                <span style="color: #ffffff"> فعالیت صاحب بارها</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/operatorsActivityReport') }}">
                                <span style="color: #ffffff"> فعالیت اپراتورها</span>
                            </a>
                        </li>
                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/combinedReports') }}">
                                <span style="color: #ffffff"> گزارش های ترکیبی</span>
                            </a>
                        </li>

                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/driverInstallationInLast30Days') }}">
                                <span style="color: #ffffff"> نصب رانندگان در 30 روز</span>
                            </a>
                        </li>


                        <li class="nav-item active">
                            <a class="nav-link small" href="{{ url('admin/fleetRatioToDriverActivityReport') }}">
                                <span style="color: #ffffff">نسبت راننده به بار</span>
                            </a>
                        </li>

                    </ol>
                </li>

            @endif
        </div>

        <li @if(!(auth()->id() == 21 || auth()->id() == 9 || auth()->id() == 29 || auth()->user()->role == ROLE_ADMIN)) class="d-none"
            @endif class="nav-item active">
            <a class="nav-link" href="#" onclick="toggleMenu('paymentReport')">
                <i class="fas fa-fw fa-globe"></i>
                <span>پرداخت ها</span>
                <i class="fas fa-fw fa-arrow-down float-left"></i>
            </a>
            <ol style="display: none" id="paymentReport">

                <li class="nav-item active">
                    <a class="nav-link small" href="{{ url('admin/paymentReport') }}/{{ ROLE_DRIVER }}/100">
                        <span style="color: #ffffff">راننده ها</span>
                    </a>
                </li>


                <li class="nav-item active">
                    <a class="nav-link small" href="{{ url('admin/mostPaidDriversReport') }}">
                        <span style="color: #ffffff">بیشترین پرداخت رانندگان</span>
                    </a>
                </li>
                <li class="nav-item active">
                    <a class="nav-link small" href="{{ url('admin/paymentByFleetReport') }}">
                        <span style="color: #ffffff">پرداخت براساس ناوگان</span>
                    </a>
                </li>
            </ol>
        </li>

        @if(auth()->user()->role == ROLE_ADMIN)
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/operatorsWorkingHoursActivityReport') }}">
                    <i class="fas fa-fw fa-clock"></i>
                    <span style="color: #ffffff">میزان فعالیت اپراتورها</span>
                </a>
            </li>
        @endif


        @if(in_array('loads',auth()->user()->userAccess))

            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/storeCargoConvertForm') }}">
                    <i class="fas fa-fw fa-truck-loading"></i>
                    <span style="color: #ffffff">ثبت بار</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/finalApprovalAndStoreCargo') }}">
                    <i class="fas fa-fw fa-truck-loading"></i>
                    <span style="color: #ffffff">تایید نهایی و ثبت دسته ای بار</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/dictionary') }}">
                    <i class="fas fa-fw fa-book"></i>
                    <span style="color: #ffffff">کلمات معادل در ثبت بار</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/loads') }}">
                    <i class="fas fa-fw fa-truck-loading"></i>
                    <span style="color: #ffffff">بارها</span>
                </a>
            </li>

        @endif

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/profile') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>پروفایل</span>
            </a>
        </li>

        @if(in_array('operators',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/operators') }}">
                    <i class="fas fa-fw fa-user"></i>
                    <span>اپراتورها</span>
                </a>
            </li>
        @endif


        @if(in_array('contactReportWithCargoOwners',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/contactReportWithCargoOwners') }}">
                    <i class="fas fa-fw fa-phone"></i>
                    <span>تماس با صاحبان بار و باربری ها</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/contactingWithDrivers') }}">
                    <i class="fas fa-fw fa-phone"></i>
                    <span>تماس با رانندگان</span>
                </a>
            </li>
        @endif


        @if(in_array('appVersions',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/appVersions') }}">
                    <i class="fas fa-fw fa-mobile"></i>
                    <span>ورژن اپلیکیشن ها</span>
                </a>
            </li>
        @endif
        @if(in_array('provincesAndCities',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/provincesAndCities') }}">
                    <i class="fas fa-fw fa-map"></i>
                    <span>استان ها و شهرها</span>
                </a>
            </li>
        @endif
        @if(in_array('complaints',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="#" onclick="toggleMenu('complaint')">
                    <i class="fas fa-fw fa-globe"></i>
                    <span>شکایات و انتقادات</span>
                </a>
                <ol style="display: none" id="complaint">
                    <li class="nav-item active">
                        <a class="nav-link" href="{{ url('admin/complaintsDriversList') }}">
                            <i class="fas fa-fw fa-globe"></i>
                            <span> رانندگان</span>
                        </a>
                    </li>

                    <li class="nav-item active">
                        <a class="nav-link" href="{{ url('admin/complaintsTransportationCompanyList') }}">
                            <i class="fas fa-fw fa-globe"></i>
                            <span> باربری ها</span>
                        </a>
                    </li>

                    <li class="nav-item active">
                        <a class="nav-link" href="{{ url('admin/complaintsCustomerList') }}">
                            <i class="fas fa-fw fa-globe"></i>
                            <span>صاحب بار</span>
                        </a>
                    </li>
                </ol>
            </li>
        @endif
        @if(in_array('SOSList',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/SOSList/0') }}">
                    <i class="fas fa-fw fa-bell"></i>
                    <span>درخواست های امداد</span>
                </a>
            </li>
        @endif
        @if(in_array('listOfLoadsByOperator',auth()->user()->userAccess))
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/listOfLoadsByOperator') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span>بارها به تفکیک اپراتور</span>
                </a>
            </li>

            <li class="nav-item active">
                <a class="nav-link small" href="{{ url('admin/operatorsActivityReport') }}">
                    <i class="fas fa-fw fa-users"></i>
                    <span style="color: #ffffff"> فعالیت اپراتورها</span>
                </a>
            </li>
        @endif
        @if(in_array('fleet',auth()->user()->userAccess) || auth()->user()->role == 'admin')
            <li class="nav-item active">
                <a class="nav-link" href="{{ url('admin/fleet') }}">
                    <i class="fas fa-fw fa-car"></i>
                    <span>ناوگان</span>
                </a>
            </li>
        @endif

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/operatorFleets') }}">
                <i class="fas fa-fw fa-car"></i>
                <span>ناوگان من</span>
            </a>
        </li>


        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/blockedPhoneNumbers') }}">
                <i class="fas fa-fw fa-mobile-alt"></i>
                <span>شماره تلفن های لیست ممنوعه</span>
            </a>
        </li>


        {{--        <li class="nav-item active">--}}
        {{--            <a class="nav-link" href="{{ url('admin/packingType') }}">--}}
        {{--                <i class="fas fa-fw fa-boxes"></i>--}}
        {{--                <span>نوع بسته بندی</span>--}}
        {{--            </a>--}}
        {{--        </li>--}}
        {{--        <li class="nav-item active">--}}
        {{--            <a class="nav-link" href="index.html">--}}
        {{--                <i class="fas fa-fw fa-wrench"></i>--}}
        {{--                <span>تنظیمات سامانه</span>--}}
        {{--            </a>--}}
        {{--        </li>--}}
        {{--        <li class="nav-item active">--}}
        {{--            <a class="nav-link" href="{{ url('admin/loadType') }}">--}}
        {{--                <i class="fas fa-fw fa-boxes"></i>--}}
        {{--                <span>نوع بار</span>--}}
        {{--            </a>--}}
        {{--        </li>--}}

        {{--        <li class="nav-item active">--}}
        {{--            <a class="nav-link" href="{{ url('admin/Marketers') }}">--}}
        {{--                <i class="fas fa-fw fa-users"></i>--}}
        {{--                <span>بازاریابها</span>--}}
        {{--            </a>--}}
        {{--        </li>--}}

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('admin/messages') }}">
                <i class="fas fa-fw fa-mobile"></i>
                <span>پیام ها</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="#">
                <i class="fas fa-fw fa-"></i>
            </a>
        </li>
    </ul>
    <div id="content-wrapper">

        <div class="container-fluid">
            <div id="content-wrapper">

                <div class="container-fluid">

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
                <!-- /.content-wrapper -->

                    <!-- Sticky Footer -->
                    <footer class="sticky-footer">
                        <div class="container my-auto">
                            <div class="copyright text-center my-auto">
                                <span>Copyright © iran-tarabar.com 2022</span>
                            </div>
                        </div>
                    </footer>

                </div>

            </div>
            <!-- /#wrapper -->

            <!-- Scroll to Top Button-->
            <a class="scroll-to-top rounded" href="#page-top">
                <i class="fas fa-angle-up"></i>
            </a>

            <!-- Logout Modal-->
            <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                            <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">×</span>
                            </button>
                        </div>
                        <div class="modal-body">Select "Logout" below if you are ready to end your current session.
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                            <a class="btn btn-primary" href="login.html">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('assets/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('assets/js/sb-admin.min.js') }}"></script>
<script src="{{ asset('assets/js/select2.js') }}"></script>
<script src="{{ asset('assets/js/fa.js') }}"></script>


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

</body>

</html>
