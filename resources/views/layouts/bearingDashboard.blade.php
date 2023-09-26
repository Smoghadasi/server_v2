<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ایران ترابر</title>

    <!-- Bootstrap core CSS-->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="../../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">

    <!-- Page level plugin CSS-->
    <link href="../../assets/vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">


    <link href="../../assets/vendor/bootstrap/css/bootstrap-rtl.min.css" rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../../assets/css/sb-admin.css" rel="stylesheet">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css">

    {{--<script src="../../assets/vendor/jquery/jquery.min.js"></script>--}}
    <script src="../../assets/js/jquery-2.1.0.js"></script>
    <script src="../../assets/js/sweetAlert.js"></script>

    <link href="../../assets/css/PersianDatePicker.min.css" rel="stylesheet">
    <script src="../../assets/js/PersianDatePicker.min.js"></script>

    <link rel="stylesheet" href="../../assets/css/persian-datepicker.css"/>
    <script src="../../assets/js/persian-date.js"></script>
    <script src="../../assets/js/persian-datepicker.js"></script>

    <link href="../../assets/css/select2.css" rel="stylesheet"/>


    <script type="text/javascript">
        $(document).ready(function () {
            $("#loadingDate").pDatepicker({
                observer: true,
                format: 'L'
            });
        });
    </script>


    <link rel="stylesheet" href="{{ asset("css/create_new_load_style.css") }}">

    <link rel="stylesheet" href="{{ asset("css/dashboard-style-v1.css") }}">

</head>

<body id="page-top">

<nav class="navbar navbar-expand navbar-dark bg-dark static-top">

    <a class="navbar-brand mr-1" href="{{ url('/dashboard') }}">ایران ترابر</a>

    <button class="btn btn-link btn-sm text-white order-1 order-sm-0" id="sidebarToggle" href="#">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Navbar Search -->
    <form class="d-none d-md-inline-block form-inline ml-auto mr-0 mr-md-3 my-2 my-md-0">
        <div class="input-group">
            <!--<input type="text" class="form-control" placeholder="جستجو..." aria-label="Search" aria-describedby="basic-addon2">-->
            <!--<div class="input-group-append">-->
            <!--<button class="btn btn-primary" type="button">-->
            <!--<i class="fas fa-search"></i>-->
            <!--</button>-->
            <!--</div>-->
        </div>
    </form>

    <!-- Navbar -->
    <ul class="navbar-nav ml-auto ml-md-0">
        {{--<li class="nav-item dropdown no-arrow mx-1">--}}
        {{--<a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"--}}
        {{--aria-haspopup="true" aria-expanded="false">--}}
        {{--<i class="fas fa-bell fa-fw"></i>--}}
        {{--<span class="badge badge-danger">9+</span>--}}
        {{--</a>--}}
        {{--<div class="dropdown-menu dropdown-menu-right" aria-labelledby="alertsDropdown">--}}
        {{--<a class="dropdown-item" href="#">Action</a>--}}
        {{--<a class="dropdown-item" href="#">Another action</a>--}}
        {{--<div class="dropdown-divider"></div>--}}
        {{--<a class="dropdown-item" href="#">Something else here</a>--}}
        {{--</div>--}}
        {{--</li>--}}
        {{--<li class="nav-item dropdown no-arrow mx-1">--}}
        {{--<a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown"--}}
        {{--aria-haspopup="true" aria-expanded="false">--}}
        {{--<i class="fas fa-envelope fa-fw"></i>--}}
        {{--<span class="badge badge-danger">7</span>--}}
        {{--</a>--}}
        {{--<div class="dropdown-menu dropdown-menu-right" aria-labelledby="messagesDropdown">--}}
        {{--<a class="dropdown-item" href="#">Action</a>--}}
        {{--<a class="dropdown-item" href="#">Another action</a>--}}
        {{--<div class="dropdown-divider"></div>--}}
        {{--<a class="dropdown-item" href="#">Something else here</a>--}}
        {{--</div>--}}
        {{--</li>--}}
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
               aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-user-circle fa-fw"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-left" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="{{ url('user/profile') }}">پروفایل</a>
                <div class="dropdown-divider"></div>
                {{--                <a class="dropdown-item" href="{{ url('logout') }}" data-toggle="modal"--}}
                {{--data-target="#logoutModal">خروج</a>--}}
                <a class="dropdown-item" href="{{ url('user/logout') }}">خروج</a>
            </div>
        </li>
    </ul>

</nav>

<div id="wrapper">
    <!-- Sidebar -->
    <ul class="sidebar navbar-nav">
        <li class="nav-item active">
            <a class="nav-link" href="{{ url('/user') }}">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>داشبورد</span>
            </a>
        </li>
        <li class="nav-item active">
            <a class="nav-link" href="{{ url('user/myLoads') }}">
                <i class="fas fa-fw fa-truck-loading"></i>
                <span>بارهای حمل شده</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('user/newLoads') }}">
                <i class="fas fa-fw fa-truck-loading"></i>
                <span>بارهای جدید</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('user/addNewLoadForm') }}">
                <i class="fas fa-fw fa-location-arrow"></i>
                <span>اعلام بار به راننده</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('user/wallet') }}">
                <i class="fas fa-fw fa-wallet"></i>
                <span>کیف پول</span>
            </a>
        </li>

        <li class="nav-item active">
            <a class="nav-link" href="{{ url('user/profile') }}">
                <i class="fas fa-fw fa-user"></i>
                <span>پروفایل</span>
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


            <script src="../../assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

            <!-- Core plugin JavaScript-->
            <script src="../../assets/vendor/jquery-easing/jquery.easing.min.js"></script>

            <!-- Page level plugin JavaScript-->
            <script src="../../assets/vendor/chart.js/Chart.min.js"></script>
            <script src="../../assets/vendor/datatables/jquery.dataTables.js"></script>
            <script src="../../assets/vendor/datatables/dataTables.bootstrap4.js"></script>

            <!-- Custom scripts for all pages-->
            <script src="../../assets/js/sb-admin.min.js"></script>

            <!-- Demo scripts for this page-->
            <script src="../../assets/js/demo/datatables-demo.js"></script>
            <script src="../../assets/js/demo/chart-area-demo.js"></script>

            <script src="../../assets/js/select2.js"></script>
            <script src="../../assets/js/fa.js"></script>


            <script>
                $('#destination_city_id').select2({
                    dir: "rtl",
                    language: "fa"
                });
                $('#origin_city_id').select2({
                    dir: "rtl",
                    language: "fa"
                });
            </script>

        </div>
    </div>
</div>
<script src="{{ asset('/assets/js/addNewLoadActions.js') }}"></script>

</body>

</html>
