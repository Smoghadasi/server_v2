@extends('layouts.customerDashboard')

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
                            <a href="{{ url('user/getCustomerLoadsList') }}"
                               class="dashboard-load-items-a-tag">
                                <div class="dashboard-load-items-bg">
                                    <img src="{{ asset("icons/saved-loads.png") }}">
                                </div>
                                <div>
                                    بارهای ثبت شده من
                                </div>
                            </a>
                        </div>
                    </div>
                    <div class="col-sm-4 mt-2">
                        <div>
                            <a href="{{ url('user/createNewLoadForm') }}/{{ ROLE_TRANSPORTATION_COMPANY }}"
                               class="dashboard-load-items-a-tag">
                                <div class="dashboard-load-items-bg">
                                    <img src="{{ asset("icons/new-load-for-transportation-company.png") }}">
                                </div>
                                <div>
                                    اعلام بار به باربری
                                </div>
                            </a>
                        </div>
                    </div>

                    <div class="col-sm-4 mt-2">
                        <div>
                            <a href="{{ url('user/createNewLoadForm') }}/{{ ROLE_DRIVER }}"
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
                        <a href="#">
                            <div class="fa fa-mobile dashboard-app-items-bg"></div>
                            <div class="col-lg-12">راهنما</div>
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
