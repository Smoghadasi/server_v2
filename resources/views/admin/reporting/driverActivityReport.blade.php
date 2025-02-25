@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش فعالیت رانندگان
        </h5>
        <div class="card-body">
            <form method="get" action="{{ route('home.searchDriverActivityReport') }}" class="mt-3 mb-3 card card-body">
                <h5>جستجو :</h5>


                <div class="form-group">
                    <div class="col-md-12 row">

                        <div class="col-md-3">
                            <input class="form-control" type="text" id="fromDate" name="fromDate" placeholder="از تاریخ"
                                autocomplete="off" />
                            <span id="span1"></span>
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" type="text" name="toDate" id="fromDate" placeholder="تا تاریخ"
                                autocomplete="off" />
                            <span id="span2"></span>
                        </div>
                    </div>
                    <button class="btn btn-primary m-2">جستجو</button>
                </div>
            </form>
            <table class="table">

                <tr>
                    <td class="h5">
                        تعداد کل رانندگان تا امروز :
                        {{ number_format($totalDrivers) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6">روند افزایش راننده ها از 12 ماه قبل</div>
                        <canvas id="increaseOfDriversSince12MonthsAgo" style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6">تفکیک ناوگان ثبت نامی از ابتدا</div>
                        <canvas id="separationOfTheFleetsFromTheFirst" style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6"> گزارش فعالیت راننده ها از ماه قبل</div>
                        <canvas id="activityReportOfDriversFromPreviousMonth" style="width:100%;max-width:100%"></canvas>

                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6"> فعالیت به تفکیک ناوگان از ماه قبل</div>
                        <div id="activityReportOfDriversFromPreviousMonthByFleets" style="height: 370px; width: 100%;">
                        </div>
                    </td>
                </tr>
                @if (auth()->user()->role == 'admin')
                    <tr>
                        <td>
                            <div class="text-center h6"> هزینه وایزی توسط راننده ها از 60 روز قبل</div>
                            <canvas id="feesPaidByDrivers60DaysInAdvance" style="width:100%;max-width:100%"></canvas>
                        </td>
                    </tr>
                @endif


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
        // روند افزایش راننده ها از 12 ماه قبل
        var xValues = [
            "{{ $increaseOfDriversSince12MonthsAgo[11]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[10]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[9]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[8]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[7]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[6]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[5]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[4]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[3]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[2]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[1]['label'] }}",
            "{{ $increaseOfDriversSince12MonthsAgo[0]['label'] }}"
        ];
        var data = [
            {{ $increaseOfDriversSince12MonthsAgo[11]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[10]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[9]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[8]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[7]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[6]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[5]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[4]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[3]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[2]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[1]['value'] }},
            {{ $increaseOfDriversSince12MonthsAgo[0]['value'] }}
        ];
        new Chart("increaseOfDriversSince12MonthsAgo", {
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

    <script>
        // تفکیک ناوگان ثبت نامی از ابتدا
        var labels = [
            @foreach ($separationOfTheFleetsFromTheFirst as $key => $value)
                "{{ $key }}",
            @endforeach
        ];
        var yValues = [
            @foreach ($separationOfTheFleetsFromTheFirst as $value)
                {{ $value }},
            @endforeach
        ];

        new Chart("separationOfTheFleetsFromTheFirst", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#134ebb",
                    data: yValues
                }]
            },
            options: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: ""
                }
            }
        });
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

    <script>
        // فعالیت به تفکیک ناوگان از ماه قبل
        var chart = new CanvasJS.Chart("activityReportOfDriversFromPreviousMonthByFleets", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "میزان فعالیت"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [
                @foreach ($activityReportOfDriversFromPreviousMonthByFleets as $item)
                    {
                        type: "spline",
                        name: "{{ $item['title'] }}",
                        showInLegend: true,
                        dataPoints: [
                            @foreach ($item['data'] as $data)
                                {
                                    label: "{{ $data['label'] }}",
                                    y: {{ $data['value'] }}
                                },
                            @endforeach
                        ]
                    },
                @endforeach
            ]
        });
        chart.render();

        function toggleDataSeries(e) {
            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            chart.render();
        }
    </script>

    <script>
        // هزینه وایزی توسط راننده ها از 60 روز قبل
        var xValues = [
            @foreach ($feesPaidByDrivers60DaysInAdvance as $value)
                "{{ $value['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach ($feesPaidByDrivers60DaysInAdvance as $value)
                {{ $value['value'] }},
            @endforeach

        ];
        new Chart("feesPaidByDrivers60DaysInAdvance", {
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
