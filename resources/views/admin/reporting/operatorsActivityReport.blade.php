@extends('layouts.dashboard')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            گزارش فعالیت اپراتورها
        </li>
    </ol>

    <div class="container text-right">
        <table class="table table-bordered">

            <tr>
                <td>
                    <div class="text-center h6">تعداد بار ثبت شده 30 روز قبل</div>
                    <canvas id="countOfOperatorsLoadsInPrevious30Days"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="text-center h6">تعداد بار ثبت شده 30 روز قبل به تفکیک اپراتور</div>
                    <div id="countOfOperatorsLoadsInPrevious30DaysByOperator" style="height: 370px; width: 100%;"></div>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="text-center h6">تعداد بار ثبت شده بصورت هفته به هفته به تفکیک اپراتور</div>
                    <div id="operatorActivityReportOnAWeeklyBasis" style="height: 370px; width: 100%;"></div>
                </td>
            </tr>

{{--            <tr>--}}
{{--                <td>--}}
{{--                    <div class="text-center h6">ثبت بار به تفکیک اپراتور</div>--}}
{{--                    <canvas id="loadRegistrationByOperator"--}}
{{--                            style="width:100%;max-width:100%"></canvas>--}}
{{--                </td>--}}
{{--            </tr>--}}

            <tr>
                <td>
                    <div class="text-center h6">ثبت بار به تفکیک اپراتور در هفته گذشته</div>
                    <canvas id="loadRegistrationByOperatorInPastWeek"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>


            <tr>
                <td>
                    <div class="text-center h6">ثبت بار به تفکیک ناوگان</div>
                    <canvas id="operatorsLoadsByFleet"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>


        </table>
    </div>
    <script>
        //تعداد بار ثبت شده  30 روز قبل
        var label = [
            @foreach($countOfOperatorsLoadsInPrevious30Days as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($countOfOperatorsLoadsInPrevious30Days as $item)
                "{{ $item['value'] }}",
            @endforeach

        ];
        new Chart("countOfOperatorsLoadsInPrevious30Days", {
            type: "line",
            data: {
                labels: label,
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

    <script>

        //تعداد بار ثبت شده 30 روز قبل به تفکیک اپراتور

        var chart = new CanvasJS.Chart("countOfOperatorsLoadsInPrevious30DaysByOperator", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد بار"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [
                    @foreach($countOfOperatorsLoadsInPrevious30DaysByOperator as $item)
                {
                    type: "spline",
                    name: "{{ $item['name'] }}",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($item['data'] as $data)
                        {
                            label: "{{ $data['label'] }}", y: {{ $data['value'] }}
                        },
                        @endforeach
                    ]
                },
                @endforeach
            ]
        });

        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            chart.render();
        }

    </script>

    <script>

        //تعداد بار ثبت شده 30 روز قبل به تفکیک اپراتور

        var chart = new CanvasJS.Chart("operatorActivityReportOnAWeeklyBasis", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد بار"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [
                    @foreach($operatorActivityReportOnAWeeklyBasis as $item)
                {
                    type: "spline",
                    name: "{{ $item['name'] }}",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($item['data'] as $data)
                        {
                            label: "{{ $data['label'] }}", y: {{ $data['value'] }}
                        },
                        @endforeach
                    ]
                },
                @endforeach
            ]
        });

        chart.render();

        function toggleDataSeries(e) {
            if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else {
                e.dataSeries.visible = true;
            }
            chart.render();
        }

    </script>

{{--    <script>--}}
{{--        // ثبت بار به تفکیک اپراتور--}}
{{--        var labels = [--}}
{{--            @foreach($loadRegistrationByOperator as $item)--}}
{{--                "{{ $item->name }} {{ $item->lastName }}",--}}
{{--            @endforeach--}}
{{--        ];--}}
{{--        var data = [--}}
{{--            @foreach($loadRegistrationByOperator as $item)--}}
{{--                "{{ $item->total }}",--}}
{{--            @endforeach--}}
{{--        ];--}}

{{--        new Chart("loadRegistrationByOperator", {--}}
{{--            type: "bar",--}}
{{--            data: {--}}
{{--                labels: labels,--}}
{{--                datasets: [{--}}
{{--                    backgroundColor: "#006fff",--}}
{{--                    data: data--}}
{{--                }]--}}
{{--            },--}}
{{--            options: {--}}
{{--                legend: {display: false},--}}
{{--                title: {--}}
{{--                    display: true,--}}

{{--                }--}}
{{--            }--}}
{{--        });--}}
{{--    </script>--}}

    <script>
        // ثبت بار به تفکیک اپراتور
        var labels = [
            @foreach($getLoadRegistrationByOperatorInPastWeek as $item)
                "{{ $item->name }} {{ $item->lastName }}",
            @endforeach
        ];
        var data = [
            @foreach($getLoadRegistrationByOperatorInPastWeek as $item)
                "{{ $item->total }}",
            @endforeach
        ];

        new Chart("loadRegistrationByOperatorInPastWeek", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#005e7a",
                    data: data
                }]
            },
            options: {
                legend: {display: false},
                title: {
                    display: true,

                }
            }
        });
    </script>

    <script>
        // ثبت بار به تفکیک ناوگان
        var labels = [
            @foreach($operatorsLoadsByFleet as $key => $item)
                "{{ $key }}",
            @endforeach
        ];
        var data = [
            @foreach($operatorsLoadsByFleet as $item)
                "{{ $item }}",
            @endforeach
        ];

        new Chart("operatorsLoadsByFleet", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#1b6915",
                    data: data
                }]
            },
            options: {
                legend: {display: false},
                title: {
                    display: true,

                }
            }
        });
    </script>



@stop
