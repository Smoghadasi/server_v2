@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش فعالیت اپراتورها
        </h5>
        <div class="card-body">

            <table class="table">
                <tr>
                    <td>
                        <div class="text-center h6">
                            جمع مبالغ پرداختی براساس ناوگان
                        </div>
                        <canvas id="paymentByFleet"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="text-center h6">
                            تعداد پرداختی براساس ناوگان
                        </div>
                        <canvas id="countOfPaymentByFleet"
                                style="width:100%;max-width:100%"></canvas>
                    </td>
                </tr>
            </table>

        </div>
    </div>


    <script>
        // مبالغ پرداختی براساس ناوگان
        var labels = [
            @foreach($paymentByFleetReport as $item)
                "{{ $item->title }}",
            @endforeach
        ];
        var data = [
            @foreach($paymentByFleetReport as $item)
                "{{ $item->total }}",
            @endforeach
        ];

        new Chart("paymentByFleet", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#006fff",
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


        // بیشترین تعداد پرداختی براساس ناوگان
        var labels = [
            @foreach($paymentByFleetReport as $item)
                "{{ $item->title }}",
            @endforeach
        ];
        var data = [
            @foreach($paymentByFleetReport as $item)
                "{{ $item->totalAmount }}",
            @endforeach
        ];

        new Chart("countOfPaymentByFleet", {
            type: "bar",
            data: {
                labels: labels,
                datasets: [{
                    backgroundColor: "#006fff",
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
