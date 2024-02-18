@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش فعالیت رانندگان
        </h5>
        <div class="card-body">

            <table class="table">

                <tr>
                    <td>
                        <div class="text-center h6"> گزارش فعالیت راننده ها از ماه قبل</div>
                        <canvas id="activityReportOfDriversFromPreviousMonth" style="width:100%;max-width:100%"></canvas>

                    </td>
                </tr>

            </table>

        </div>
    </div>

    <script>
        // گزارش فعالیت راننده ها از ماه قبل
        var xValues = [
            @foreach($activityReportOfDriversFromPreviousMonth as $value)
                "{{ $value['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($activityReportOfDriversFromPreviousMonth as $value)
                {{ $value['value'] }},
            @endforeach

        ];
        new Chart("activityReportOfDriversFromPreviousMonth", {
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



