@extends('layouts.dashboard')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            گزارش نصب رانندگان از 30 روز گذشته
        </li>
    </ol>

    <div class="container text-right">
        <div class="h4">گزارش نصب رانندگان از 30 روز گذشته</div>
        <canvas id="driverInstallInLast30Days" style="width:100%;max-width:100%"></canvas>
    </div>



    <script>
        // گزارش فعالیت راننده ها از ماه قبل
        var xValues = [
            @foreach($driverInstallInLast30Days as $value)
                "{{ $value['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($driverInstallInLast30Days as $value)
                {{ $value['value'] }},
            @endforeach

        ];
        new Chart("driverInstallInLast30Days", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    data: data,
                    borderColor: "blue",
                    fill: false
                }]
            },
            options: {
                legend: {display: false}
            }
        });
    </script>

@stop



