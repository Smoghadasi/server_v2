@extends('layouts.bearingDashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            ایران ترابر
        </li>

    </ol>

    <div class="container">
        <div class="col-md-12">
            <div class="text-right">
                <div class="row text-center">
                    <div class="col-sm-4 mt-2">
                        <div>
                            <a href="{{ url('user/newLoads') }}"
                               class="dashboard-load-items-a-tag">
                                <div class="dashboard-load-items-bg">
                                    <img src="{{ asset("icons/saved-loads.png") }}">
                                </div>
                                <div>
                                    بارهای جدید
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-4 mt-2">
                        <div>
                            <a href="{{ url('user/myLoads') }}"
                               class="dashboard-load-items-a-tag">
                                <div class="dashboard-load-items-bg">
                                    <img src="{{ asset("icons/new-load-for-transportation-company.png") }}">
                                </div>
                                <div>
                                    بارهای حمل شده
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-sm-4 mt-2">
                        <div>
                            <a href="{{ url('user/addNewLoadForm') }}"
                               class="dashboard-load-items-a-tag">
                                <div class="dashboard-load-items-bg">
                                    <img src="{{ asset("icons/new-load-for-driver.png") }}">
                                </div>
                                <div>
                                    اعلام بار به راننده
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="row text-center mt-5">
                    <div class="col-sm-4 mt-2 dashboard-app-item">
                        <a href="{{ url('user/profile') }}">
                            <div class="fa fa-user dashboard-app-items-bg"></div>
                            <div class="col-lg-12">پروفایل</div>
                        </a>
                    </div>
                    <div class="col-sm-4 mt-2 dashboard-app-item">
                        <a href="{{ url('user/wallet') }}">
                            <div class="fa fa-wallet dashboard-app-items-bg"></div>
                            <div class="col-lg-12">کیف پول</div>
                        </a>
                    </div>
                    <div class="col-sm-4 mt-2 dashboard-app-item">
                        <a href="{{ url('user/userCriticismOrComplaints') }}">
                            <div class="fa fa-paper-plane dashboard-app-items-bg"></div>
                            <div class="col-lg-12">انتقاد یا شکایت</div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
