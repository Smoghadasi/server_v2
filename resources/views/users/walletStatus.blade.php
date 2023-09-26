@extends('layouts.bearingDashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            کیف پول
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    <div class="container">
        <div class="col-sm-12 text-center">

            <div class="col-sm-6">
                <div class="alert {{ $alert }}" style="font-size: 24px">
                    <i class="fas fa-5x fa-wallet"></i>
                    <br>
                    @foreach($message as $item)
                        <span>{{ $item }}</span>
                    @endforeach
                </div>
                <br>
                <a class="btn btn-primary" href="{{ url('user/wallet') }}">
                    بازگشت به کیف پول
                </a>
            </div>

        </div>
    </div>

@stop