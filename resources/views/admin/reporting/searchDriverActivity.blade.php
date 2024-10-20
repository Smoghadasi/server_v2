@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش فعالیت رانندگان بر اساس تاریخ
        </h5>
        <div class="card-body">

            <div class="text-right">
                <form method="get" action="{{ route('home.searchDriverActivityReport') }}" class="mt-3 mb-3 card card-body">
                    <h5>جستجو :</h5>


                    <div class="form-group">
                        <div class="col-md-12 row">

                            <div class="col-md-3">
                                <input class="form-control" type="text" id="fromDate" name="fromDate"
                                    placeholder="از تاریخ" autocomplete="off" value="{{ $start }}" />
                                <span id="span1"></span>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="toDate" id="fromDate"
                                    placeholder="تا تاریخ" autocomplete="off" value="{{ $end }}" />
                                <span id="span2"></span>
                            </div>
                        </div>
                        <button class="btn btn-primary m-2">جستجو</button>
                    </div>
                </form>
            </div>
            <table class="table">
                <tr>
                    <td>
                        <div class="text-center h6"> گزارش فعالیت راننده ها از تاریخ {{ $start }} تا
                            {{ $end }}</div>
                        <canvas id="activityReportOfDriversFromPreviousMonth" style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
            </table>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #span1").persianDatepicker();
        $("#toDate, #span2").persianDatepicker();
    </script>

    <script>
        // گزارش فعالیت راننده ها از ماه قبل
        var xValues = [
            @foreach ($activityReportOfDriversFromPreviousMonth as $value)
                "{{ $value['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach ($activityReportOfDriversFromPreviousMonth as $value)
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
                legend: {
                    display: false
                }
            }
        });
    </script>
@endsection
