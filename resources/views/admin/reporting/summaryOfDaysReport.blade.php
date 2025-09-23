@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">خلاصه گزارش روز</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">

                    <tr>
                        <th colspan="5" class="text-center">
                            خلاصه گزارش روز
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center" rowspan="2">رانندگان</th>
                        <th class="text-center" colspan="3">تعداد و مجموع بار ثبت شده</th>
                        <th class="text-center" rowspan="2">درآمد</th>
                    </tr>

                    <tr>
                        <th class="text-center"> صاحبان بار</th>
                        <th class="text-center">صاحب بارها</th>
                        <th class="text-center">اپراتور ها</th>
                    </tr>

                    <tr>
                        <td>تعداد کل : {{ number_format($drivers['total']) }}</td>
                        <td>تعداد کل : {{ number_format($owners['total']) }}</td>
                        <td>تعداد کل : {{ number_format($cargoOwners['total']) }}</td>
                        <td>تعداد بار ثبت شده امروز : {{ number_format($operators['toDayLoads']) }}</td>
                        <td>درآمد کل : {{ number_format($incomes['total']) }}</td>
                    </tr>
                    <tr>
                        <td>افزایش امروز : {{ number_format($drivers['toDay']) }}</td>
                        <td>افزایش امروز : {{ number_format($owners['toDay']) }}</td>
                        <td>افزایش امروز : {{ number_format($cargoOwners['toDay']) }}</td>
                        <td>تعداد بار ثبت شده دیروز : {{ number_format($operators['yesterdayLoads']) }}</td>
                        <td>
                            درآمد امروز : {{ number_format($incomes['toDay']) }}
                            <hr>
                            <a href="{{ route('driverSummery', ['type' => 'todayPayment']) }}">
                                تعداد کل : {{ number_format($drivers['todayPayment']) }}
                            </a>
                            <br>
                            <a href="{{ route('driverSummery', ['type' => 'todayOnline']) }}">
                                آنلاین : {{ number_format($drivers['todayOnline']) }}
                            </a>
                            <br>
                            <a href="{{ route('driverSummery', ['type' => 'todayCartToCart']) }}">
                                کارت به کارت : {{ number_format($drivers['todayCartToCart']) }}
                            </a>
                            <br />
                            <a href="{{ route('driverSummery', ['type' => 'todayGift']) }}">
                                هدیه : {{ number_format($drivers['todayGift']) }}
                            </a>
                        </td>
                    </tr>
                    <tr>
                        <td>افزایش دیروز : {{ number_format($drivers['yesterday']) }}</td>
                        <td>افزایش دیروز : {{ number_format($owners['yesterday']) }}</td>
                        <td>افزایش دیروز : {{ number_format($cargoOwners['yesterday']) }}</td>
                        <td> بار ثبت شده هفته : {{ number_format($operators['weekLoads']) }}</td>
                        <td>درآمد دیروز : {{ number_format($incomes['yesterday']) }}</td>
                    </tr>
                    <tr>
                        <td>افزایش طی هفته : {{ number_format($drivers['week']) }}</td>
                        <td>افزایش طی هفته : {{ number_format($owners['week']) }}</td>
                        <td>افزایش طی هفته : {{ number_format($cargoOwners['week']) }}</td>
                        <td> بار ثبت شده ماه : {{ number_format($operators['monthLoads']) }}</td>
                        <td>درآمد هفته : {{ number_format($incomes['week']) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>تعداد بار ثبت شده امروز : {{ number_format($owners['toDayLoads']) }}</td>
                        <td>تعداد بار ثبت شده امروز : {{ number_format($cargoOwners['toDayLoads']) }}</td>
                        <td></td>
                        <td>
                            درآمد ماه : {{ number_format($incomes['month']) }}
                            @if (Auth()->user()->role == 'admin')
                                <br />
                                درآمد ماه قبل : {{ number_format($incomes['lastMonth']) }}
                            @endif

                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>تعداد بار ثبت شده دیروز : {{ number_format($owners['yesterdayLoads']) }}</td>
                        <td>تعداد بار ثبت شده دیروز : {{ number_format($cargoOwners['yesterdayLoads']) }}</td>
                        <td></td>
                        <td>درآمد از راننده(هفته) : {{ number_format($incomes['drivers']) }}</td>

                    </tr>
                    <tr>
                        <td></td>
                        <td> بار ثبت شده هفته : {{ number_format($owners['weekLoads']) }}</td>
                        <td> بار ثبت شده هفته : {{ number_format($cargoOwners['weekLoads']) }}</td>
                        <td></td>
                        <td>درآمد از باربری (هفته) : {{ number_format($incomes['transportationCompany']) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="2" class="text-center">
                            بار ثبت شده دیروز :
                            {{ number_format($owners['yesterdayLoads'] + $cargoOwners['yesterdayLoads']) }}
                        </td>
                        <td></td>
                        <td>درآمد از صاحب بار (هفته) : {{ number_format($incomes['cargoOwner']) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="3" class="text-center h4">
                            جمع کل بارهای دیروز :
                            {{ number_format($owners['yesterdayLoads'] + $cargoOwners['yesterdayLoads'] + $operators['yesterdayLoads']) }}
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>



            {{-- <div class="mt-4">
                <div class="text-center h6">تعداد بار ثبت شده 30 روز قبل به تفکیک اپراتور</div>
                <canvas id="countOfLoadsInPrevious30Days" style="width:100%;max-width:100%"></canvas>
            </div> --}}

        </div>
    </div>


    <script>
        var label = [
            @foreach ($countOfLoadsInPrevious30Days as $item)
                "{{ $item['label'] }}",
            @endforeach
        ];
        var data = [
            @foreach ($countOfLoadsInPrevious30Days as $item)
                "{{ $item['value'] }}",
            @endforeach

        ];
        new Chart("countOfLoadsInPrevious30Days", {
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
                legend: {
                    display: false
                }
            }
        });
    </script>


@stop
