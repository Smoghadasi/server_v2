<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth('bearing')->check())

@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif

@extends($dashboard)


@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            انتخاب راننده
        </li>
    </ol>
    <div class="container">
        @if($load->proposedPriceForDriver>0)

            <div class="alert alert-info text-right">
                <h3>قیمت صافی : {{ $load->proposedPriceForDriver }} تومان</h3>
                توجه : قیمت صافی، قیمت مورد نظر شما می باشد
            </div>





            <div class="table-responsive">
                <table class="table table-bordered" cellspacing="0">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>تصویر</th>
                        <th>نام و نام خانوادگی</th>
                    </tr>
                    </thead>
                    <tbody class="text-right">
                    <?php $i = 1;?>
                    @foreach($drivers as $driver)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>
                                @if($driver->pic)
                                    <img src="{{ url($driver->pic) }}" class="img-thumbnail" width="100" height="100">
                                @else
                                    بدون تصویر
                                @endif
                            </td>
                            <td>{{ $driver->name}} {{ $driver->lastName}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>





        @else
            <div class="row mb-4 alert alert-info text-center">
                قیمت صافی خود را جهت نمایش به رانندگان ثبت نمایید
                توجه : قیمت صافی، قیمت مورد نظر شما می باشد
            </div>
            <div class="row">
                <form class="card card-body col-md-4" method="post" action="{{ url('user/requestDriver') }}">
                    @csrf
                    <input type="hidden" value="{{ $load->id }}" name="load_id">
                    <div class="form-group">
                        <input type="text" name="proposedPriceForDriver" id="proposedPriceForDriver"
                               class="form-control" placeholder="قیمت صافی به تومن">
                    </div>
                    <div class="form-group">
                        <button class="btn btn-primary">ارسال قیمت صافی برای راننده</button>
                    </div>
                </form>
            </div>
        @endif


    </div>
@stop