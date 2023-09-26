@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            حسابداری
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    <div class="container">
        <div class="col-md-12">
            <div class="text-right">
                <div class="row">
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <div class="card text-white bg-primary o-hidden">
                            <div class="card-body">
                                <h1 class="text-center">{{ $transactions }} </h1>
                                <h2 class="text-center">کل پرداختی ها</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <div class="card text-white bg-warning o-hidden">
                            <div class="card-body">
                                <h1 class="text-center">{{ $bearingsInitialCharges }} </h1>
                                <h2 class="text-center"> شارژهای اولیه</h2>
                            </div>

                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <div class="card text-white bg-success o-hidden h-100">
                            <div class="card-body">
                                <h1 class="text-center">{{ $bearingsCurrentCharges }} </h1>
                                <h2 class="text-center"> شارژ کاربران</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-sm-6 mb-3">
                        <div class="card text-white bg-danger o-hidden">
                            <div class="card-body">
                                <h1 class="text-center">{{ $loadPrice }} </h1>
                                <h2 class="text-center"> سهم 2 درصد</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <h3 style="direction: rtl;" class="text-right">
            درآمد از بارهای ثبت شده :
            {{ $loadPrice-($transactions+$bearingsInitialCharges) }}
            تومان
        </h3>
    </div>
@stop