@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            نمدار فعالیت رانندگان بر اساس تماس
        </h5>
        <div class="card-body">
            <table class="table">
                <tr>
                    <td>
                        <div class="text-center h6" id="basedCalls" style="height: 370px; width: 100%;"></div>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6" id="bassedFleets" style="height: 370px; width: 100%;"></div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <script>
        var chart = new CanvasJS.Chart("basedCalls", {
            animationEnabled: true,
            title: {
                text: "به تفکیک لیست تماس"
            },

            axisY: {
                title: "بر اساس لیست تماس",
            },
            legend: {
                cursor: "pointer",
                fontSize: 16,
                itemclick: toggleDataSeries
            },
            toolTip: {
                shared: true
            },
            data: [{
                name: "تماس روزانه رانندگان",
                type: "spline",
                yValueFormatString: "#0.##",
                showInLegend: true,
                dataPoints: [
                    @foreach ($basedCalls as $item)
                        {
                            label: "{{ $item->persian_date }}",
                            y: {{ $item->countOfCalls }}
                        },
                    @endforeach
                ]
            }]
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
        var chart = new CanvasJS.Chart("bassedFleets", {
            animationEnabled: true,
            title: {
                text: "به تفکیک ناوگان"
            },

            axisY: {
                title: "بر اساس ناوگان",
            },
            legend: {
                cursor: "pointer",
                fontSize: 16,
                itemclick: toggleDataSeries
            },
            toolTip: {
                shared: true
            },
            data: [
                @foreach ($groupBy as $item => $fleet_lists)
                    {
                        type: "spline",
                        name: "{{ $item }}",
                        showInLegend: true,
                        dataPoints: [
                            @foreach ($fleet_lists as $fleet_list)
                                {
                                    label: "{{ $fleet_list->persian_date }}",
                                    y: {{ $fleet_list->calls }}

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
@endsection
