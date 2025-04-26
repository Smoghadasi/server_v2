@extends('layouts.dashboard')
@section('content')
    <div class="card">
        <h5 class="card-header">گزارش فعالیت رانندگان غیر تکراری</h5>
        <div class="card-body">
            <div class="text-center h6"> گزارش فعالیت راننده ها از ماه قبل</div>
            <canvas id="activityReportOfDriversFromPreviousMonth" style="width:100%;max-width:100%"></canvas>
        </div>
    </div>
@endsection


@section('script')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            console.log('awdawd');
            fetch("{{ route('admin.reporting.nonRepeate.data') }}")
                .then(response => response.json())
                .then(data => {
                    new Chart("activityReportOfDriversFromPreviousMonth", {
                        type: "line",
                        data: {
                            labels: data.labels,
                            datasets: [{
                                data: data.values,
                                borderColor: "blue",
                                fill: false
                            }]
                        },
                        options: {
                            legend: {
                                display: false
                            }
                        }
                    });
                });
        });
    </script>
@endsection
