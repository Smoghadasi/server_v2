<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth('bearing')->check())

@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif

@extends($dashboard)

@section('content')

    <div class="container">
        <div class="col-md-12">
            <div class="text-center">

                @if(isset($message) && strlen($message))
                    <h5 class="alert {{ $alert }}">{{ $message }}</h5>
                    @if(isset($buttonUrl))
                        <a class="btn btn-primary" href="{{ url($buttonUrl) }}">بازگشت</a>
                    @endif
                @else
                    <div class="alert alert-danger">چنین صفحه ای وجود ندارد</div>
                @endif
            </div>
        </div>
    </div>
@stop
