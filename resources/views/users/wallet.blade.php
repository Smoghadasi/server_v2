@extends('layouts.bearingDashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            اطلاعات کیف پول
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    <div class="container">
        <div class="col-md-12 text-center">
            <div class="col-sm-6">
                <div class="alert alert-primary" style="font-size: 24px">
                    <i class="fas fa-5x fa-wallet"></i>
                    <br>
                    موجودی کیف پول
                    <div>
                        {{ $wallet }}
                        تومان
                    </div>
                </div>
                <form method="post" action="{{ url('chargeWallet') }}">
                    @csrf
                    <input type="text" name="amount" id="amount" class="form-control col-md-6 offset-1"
                           placeholder="مبلغ مورد نظر به تومان">
                    <button type="submit" class="btn btn-primary col-md-5" href="{{ url('chargeWallet') }}"> +
                        افزایش موجودی
                    </button>
                </form>
            </div>
        </div>
        </div>
    </div>
@stop