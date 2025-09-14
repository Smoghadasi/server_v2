@extends('layouts.dashboard')
@section('css')
    <script type="text/javascript" src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>

    <script src="{{ asset('js/chart.js') }}"></script>
@endsection
@section('content')


    <div class="card">
        <h5 class="card-header">
            جدول نسبت ناوگان (کل و فعال) به بار
        </h5>
        <div class="card-body">

            <form action="fleetRatioToDriverActivityReport" method="post" class="mb-2">
                @csrf
                <div class="form-group col-lg-3">
                    <label>از تاریخ : </label>
                    <input class="form-control" type="text" name="fromDate" value="{{ $fromDate }}">
                </div>
                <div class="form-group col-lg-3">
                    <label>تا تاریخ : </label>
                    <input class="form-control" type="text" name="toDate" value="{{ $toDate }}">
                </div>
                <div class="form-group">
                    <button class="btn btn-sm btn-primary mt-4" type="submit">جستجو</button>
                </div>
            </form>

            <table class="table mb-4 table-bordered">
                <thead>
                <tr class="text-right">
                    <th>تعداد کل</th>
                    <th>تعداد فعال</th>
                    <th>تعداد اکانت فعال</th>
                    <th>تعداد بار اپراتورها</th>
                    <th>تعداد بار باربریها</th>
                    <th>تعداد بار صاحب بارها</th>
                    <th>تعداد کل بارها</th>
                    <th>نسبت بار به راننده فعال</th>
                </tr>
                </thead>

                <tbody>
                <tr
                    @if($fleetRatioToDriverActivityReport->sum('countOfActiveDrivers') > 0)
                    @if($fleetRatioToDriverActivityReport->sum('countOfAllLoads') / $fleetRatioToDriverActivityReport->sum('countOfActiveDrivers') < 1)
                    class="bg-danger"
                    @elseif($fleetRatioToDriverActivityReport->sum('countOfAllLoads') / $fleetRatioToDriverActivityReport->sum('countOfActiveDrivers') < 3)
                    class="bg-warning"
                    @endif
                    @endif
                >
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfAllDrivers')) }}}</td>
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfActiveDrivers')) }}}</td>
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfActiveDriverAccounts')) }}}</td>
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfOperatorsLoads')) }}}</td>
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfCargoOwnersLoads')) }}}</td>
                    <td>{{{ number_format($fleetRatioToDriverActivityReport->sum('countOfTransportationsLoads')) }}}</td>
                    <td>{{ number_format($fleetRatioToDriverActivityReport->sum('countOfAllLoads')) }}</td>

                    @if($fleetRatioToDriverActivityReport->sum('countOfActiveDrivers') > 0)
                        <td>
                            {{ number_format(($fleetRatioToDriverActivityReport->sum('countOfAllLoads') / $fleetRatioToDriverActivityReport->sum('countOfActiveDrivers')) , 1) }}
                        </td>
                    @else
                        <td>0</td>
                    @endif

                </tr>


                </tbody>
            </table>

            <table class="mt-4 table table-bordered" id="myTable">
                <thead>
                <tr class="text-right">
                    <th>ردیف</th>
                    <th>ناوگان</th>
                    <th>تعداد کل</th>
                    <th>تعداد فعال</th>
                    <th>تعداد اکانت فعال</th>
                    <th>تعداد بار اپراتورها</th>
                    <th>تعداد بار باربریها</th>
                    <th>تعداد بار صاحب بارها</th>
                    <th>تعداد کل بارها</th>
                    <th>نسبت بار به راننده فعال</th>
                </tr>
                </thead>
                <tbody>
                @foreach($fleetRatioToDriverActivityReport as $key => $item)
                    <tr
                        @if($item->countOfActiveDrivers > 0)
                        @if($item->countOfAllLoads / $item->countOfActiveDrivers < 1)
                        class="bg-danger"
                        @elseif($item->countOfAllLoads / $item->countOfActiveDrivers < 3)
                        class="bg-warning"
                        @endif
                        @endif
                    >
                        <td>{{ $key + 1 }}</td>
                        <td>{{{ $item->fleetName }}}</td>
                        <td>{{{ number_format($item->countOfAllDrivers) }}}</td>
                        <td>{{{ number_format($item->countOfActiveDrivers) }}}</td>
                        <td>{{{ number_format($item->countOfActiveDriverAccounts) }}}</td>
                        <td>{{{ number_format($item->countOfOperatorsLoads) }}}</td>
                        <td>{{{ number_format($item->countOfCargoOwnersLoads) }}}</td>
                        <td>{{{ number_format($item->countOfTransportationsLoads) }}}</td>
                        <td>{{ number_format($item->countOfAllLoads) }}</td>

                        @if($item->countOfActiveDrivers > 0)
                            <td>
                                {{ number_format(($item->countOfAllLoads / $item->countOfActiveDrivers) , 1) }}
                            </td>
                        @else
                            <td>0</td>
                        @endif

                    </tr>
                @endforeach

                </tbody>
            </table>

            <div class="mt-2">
                <div class="text-center h6">نمودار نسبت بار به راننده فعال</div>
                <div id="fleetRatioToDriverActivityDiagram" style="height: 370px; width: 100%;"></div>
            </div>
            <div class="mt-2">
                <div class="text-center h6">نمودار بارها و رانندگان</div>
                <div id="LoadsAndDriversDiagram" style="height: 370px; width: 100%;"></div>
            </div>
            <div class="mt-2">
                <div class="text-center h6">نمودار نسبت رانندگان فعال به کل رانندگان</div>
                <div id="activeDriverRatioToAllDriverDiagram" style="height: 370px; width: 100%;"></div>
            </div>
            <div class="mt-2">
                <div class="text-center h6">نمودار رانندگان فعال و کل رانندگان</div>
                <div id="activeDriverAndAllDriverDiagram" style="height: 370px; width: 100%;"></div>
            </div>

        </div>
    </div>

    <script>

        let chart = new CanvasJS.Chart("fleetRatioToDriverActivityDiagram", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [

                {
                    type: "spline",
                    name: "بار به راننده",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                        {
                            label: "{{ $item->date }}", y: {{ $item->value }}
                        },
                        @endforeach
                    ]
                }
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

        let chart = new CanvasJS.Chart("activeDriverRatioToAllDriverDiagram", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [
                {
                    type: "spline",
                    name: "رااننده فعال به کل راننده",
                    showInLegend: true,
                    dataPoints: [
                            @php $countOfDrivers = $allDrivers; @endphp
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                            @php $countOfDrivers += $item->countOfDrivers; @endphp
                        {
                            label: "{{ $item->date }}",
                            y: {{ number_format($item->countOfActiveDrivers / $countOfDrivers , 2) }}
                        },
                        @endforeach
                    ]
                }
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

        let chart = new CanvasJS.Chart("activeDriverAndAllDriverDiagram", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [
                {
                    type: "spline",
                    name: " کل رانندگان",
                    showInLegend: true,
                    dataPoints: [
                            @php $countOfDrivers = $allDrivers; @endphp
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                            @php $countOfDrivers += $item->countOfDrivers; @endphp
                        {
                            label: "{{ $item->date }}",
                            y: {{ $countOfDrivers }}
                        },
                        @endforeach
                    ]
                },
                {
                    type: "spline",
                    name: "راانندگان فعال",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                        {
                            label: "{{ $item->date }}",
                            y: {{ $item->countOfActiveDrivers }}
                        },
                        @endforeach
                    ]
                },
                {
                    type: "spline",
                    name: "کل بارها",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                        {
                            label: "{{ $item->date }}", y: {{ $item->countOfAllLoads }}
                        },
                        @endforeach
                    ]
                }
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

        let chart = new CanvasJS.Chart("LoadsAndDriversDiagram", {
            animationEnabled: true,
            // exportEnabled: true,

            axisY: {
                title: "تعداد"
            },
            toolTip: {
                shared: true
            },
            legend: {
                cursor: "pointer",
                itemclick: toggleDataSeries
            },
            data: [

                {
                    type: "spline",
                    name: "راننده های فعال",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                        {
                            label: "{{ $item->date }}", y: {{ $item->countOfActiveDrivers }}
                        },
                        @endforeach
                    ]
                },
                {
                    type: "spline",
                    name: "کل بارها",
                    showInLegend: true,
                    dataPoints: [
                            @foreach($fleetRatioToDriverActivityDiagram as $item)
                        {
                            label: "{{ $item->date }}", y: {{ $item->countOfAllLoads }}
                        },
                        @endforeach
                    ]
                }
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

@stop



