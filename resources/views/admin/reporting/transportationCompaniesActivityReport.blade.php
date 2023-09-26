@extends('layouts.dashboard')

@section('content')

    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            گزارش فعالیت باربری ها
        </li>
    </ol>

    <div class="container text-right">
        <table class="table table-bordered">

            <tr>
                <td class="h5">
                    تعداد کل باربری ها تا امروز :
                    {{ number_format($totalTransportationCompanies) }}
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-center h6">روند افزایش باربری ها از 12 ماه قبل</div>
                    <canvas id="increaseOfTransportationCompaniesSince12MonthsAgo"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-center h6">تعداد بار ثبت شده 30 روز قبل</div>
                    <canvas id="countOfTransportationCompaniesLoadsInPrevious30Days"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="text-center h6">ثبت بار به تفکیک ناوگان از 30 روز قبل</div>
                    <canvas id="transportationCompaniesLoadsByFleetInPrevious30Days"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>

            <tr>
                <td>
                    <div class="text-center h6">هزینه وایزی توسط باربری ها از 60 روز قبل</div>
                    <canvas id="depositDee60DaysInAdvance"
                            style="width:100%;max-width:100%"></canvas>
                </td>
            </tr>



        </table>
    </div>


    <script>
        // روند افزایش باربری  ها از 12 ماه قبل
        var label = [
            @foreach($increaseOfTransportationCompaniesSince12MonthsAgo as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($increaseOfTransportationCompaniesSince12MonthsAgo as $item)
                "{{ $item['value'] }}",
            @endforeach
        ];
        new Chart("increaseOfTransportationCompaniesSince12MonthsAgo", {
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
        //تعداد بار ثبت شده  30 روز قبل شرکت های باربری
        var label = [
            @foreach($countOfTransportationCompaniesLoadsInPrevious30Days as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($countOfTransportationCompaniesLoadsInPrevious30Days as $item)
                "{{ $item['value'] }}",
            @endforeach

        ];
        new Chart("countOfTransportationCompaniesLoadsInPrevious30Days", {
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
        var labels = [
            @foreach($transportationCompaniesLoadsByFleetInPrevious30Days as $key=>$item)
                "{{ $key }}",
            @endforeach
        ];
        var data = [
            @foreach($transportationCompaniesLoadsByFleetInPrevious30Days as $item)
                "{{ $item }}",
            @endforeach
        ];

        new Chart("transportationCompaniesLoadsByFleetInPrevious30Days", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#ff00bb",
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
        //هزینه وایزی توسط باربری ها از 60 روز قبل
        var xValues = [
            @foreach($depositDee60DaysInAdvance as $value)
                "{{ $value['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($depositDee60DaysInAdvance as $value)
                {{ $value['value'] }},
            @endforeach

        ];
        new Chart("depositDee60DaysInAdvance", {
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



