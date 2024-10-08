@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش فعالیت صاحب بار ها
        </h5>
        <div class="card-body">
            <table class="table">

                <tr>
                    <td class="h5">
                        تعداد کل صاحب بار ها تا امروز :
                        {{ number_format($totalCargoOwners) }}
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6">روند افزایش صاحب بار ها از 12 ماه قبل</div>
                        <canvas id="increaseOfCargoOwnersSince12MonthsAgo"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6">تعداد بار ثبت شده 30 روز قبل</div>
                        <canvas id="countOfCargoOwnersLoadsInPrevious30Days"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
                <tr>
                    <td>
                        <div class="text-center h6">ثبت بار به تفکیک ناوگان از 30 روز قبل</div>
                        <canvas id="cargoOwnersLoadsByFleetInPrevious30Days"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="text-center h6">هزینه وایزی توسط صاحب بار ها از 60 روز قبل</div>
                        <canvas id="depositDee60DaysInAdvance"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>


            </table>
        </div>
    </div>


    <script>
        // روند افزایش صاحب بار  ها از 12 ماه قبل
        var label = [
            @foreach($increaseOfCargoOwnersSince12MonthsAgo as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($increaseOfCargoOwnersSince12MonthsAgo as $item)
                "{{ $item['value'] }}",
            @endforeach
        ];
        new Chart("increaseOfCargoOwnersSince12MonthsAgo", {
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
        //تعداد بار ثبت شده 30 روز قبل شرکت های صاحب بار
        var label = [
            @foreach($countOfCargoOwnersLoadsInPrevious30Days as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach($countOfCargoOwnersLoadsInPrevious30Days as $item)
                "{{ $item['value'] }}",
            @endforeach

        ];
        new Chart("countOfCargoOwnersLoadsInPrevious30Days", {
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
            @foreach($cargoOwnersLoadsByFleetInPrevious30Days as $key=>$item)
                "{{ $key }}",
            @endforeach
        ];
        var data = [
            @foreach($cargoOwnersLoadsByFleetInPrevious30Days as $item)
                "{{ $item }}",
            @endforeach
        ];

        new Chart("cargoOwnersLoadsByFleetInPrevious30Days", {
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
        //هزینه وایزی توسط صاحب بار ها از 60 روز قبل
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



