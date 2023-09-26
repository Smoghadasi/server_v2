@extends('layouts.dashboard')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            گزارش فعالیت صاحب بار ها
        </li>
    </ol>

    <div class="container text-right">
        <table class="table table-bordered">

            <tr>
                <td class="h5">
                    کل بار ثبت شده :
                    {{ number_format($totalLoads) }}
                    مورد
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-center h6">بار ثبت شده به تفکیک فعالین و ناوگان</div>

                    @php
                        $i=0;
                    @endphp
                    @foreach($countOfLoadByOperators as $fleetTitle => $data)
                        <div class="h6 mt-2 alert alert-primary text-center">{{ $fleetTitle }}</div>
                        <div id="chartContainer_{{ $i++ }}" style="height: 300px; width: 100%;"></div>
                        <hr>
                    @endforeach

                </td>
            </tr>
        </table>
    </div>
    <script type="text/javascript">
        window.onload = function () {
            @php
                $i=0;
            @endphp
            @foreach($countOfLoadByOperators as $fleetTitle => $data)
            var chart{{$i}} = new CanvasJS.Chart("chartContainer_{{ $i }}", {
                animationEnabled: true,
                title: {
                    text: ""
                },
                axisY: {
                    title: "",
                    includeZero: false
                },
                legend: {
                    cursor: "pointer",
                    itemclick: toggleDataSeries
                },
                toolTip: {
                    shared: true,
                    content: toolTipFormatter
                },
                data: [
                        @foreach($data as $name => $dataPoints)
                    {
                        type: "bar",
                        showInLegend: true,
                        @if($name == 'total')
                        name: "جمع کل",
                        color: "#ff620d",
                        @elseif($name == 'cargo_owner')
                        name: "صاحبان بار",
                        color: "#2568e5",
                        @elseif($name == 'transportation_company')
                        name: "باربری ها",
                        color: "#890dfd",
                        @elseif($name == 'operator')
                        name: "اپراتور ها",
                        color: "#66d924",
                        @endif
                        dataPoints: [
                                @foreach($dataPoints as $label => $y)
                            {
                                y: {{ $y }}, label: "{{ $label }}"
                            },
                            @endforeach
                        ]
                    },
                    @endforeach
                ]
            });
            chart{{$i}}.render();

            function toolTipFormatter(e) {
                var str = "";
                var total = 0;
                var str3;
                var str2;
                for (var i = 0; i < e.entries.length; i++) {
                    var str1 = "<span style= \"color:" + e.entries[i].dataSeries.color + "\">" + e.entries[i].dataSeries.name + "</span>: <strong>" + e.entries[i].dataPoint.y + "</strong> <br/>";
                    total = e.entries[i].dataPoint.y + total;
                    str = str.concat(str1);
                }
                str2 = "<strong>" + e.entries[0].dataPoint.label + "</strong> <br/>";
                // str3 = "<span style = \"color:Tomato\">جمع کل : </span><strong>" + total + "</strong><br/>";
                str3 = "";
                return (str2.concat(str)).concat(str3);
            }

            function toggleDataSeries(e) {
                if (typeof (e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                    e.dataSeries.visible = false;
                } else {
                    e.dataSeries.visible = true;
                }
                chart{{$i++}}.render();
            }
            @endforeach

        }
    </script>
@stop
