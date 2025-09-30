@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">خلاصه گزارش روز</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">

                    <tr>
                        <th colspan="4" class="text-center">
                            خلاصه گزارش روز
                        </th>
                    </tr>
                    <tr>
                        <th class="text-center" rowspan="2">آمار کاربران</th>
                        <th class="text-center" colspan="2">تعداد و مجموع بار ثبت شده</th>
                        <th class="text-center" rowspan="2">درآمد</th>
                    </tr>

                    <tr>
                        <th class="text-center"> صاحبان بار</th>
                        <th class="text-center">اپراتور ها</th>
                    </tr>

                    <tr>
                        <td>
                            تعداد کل رانندگان : {{ number_format($drivers['total']) }}
                            <br />
                            افزایش امروز  : {{ number_format($drivers['toDay']) }}
                        </td>
                        <td>تعداد بار ثبت شده امروز : {{ number_format($owners['toDayLoads']) }}</td>

                        <td>تعداد بار ثبت شده امروز : {{ number_format($operators['toDayLoads']) }}</td>
                        <td>درآمد کل : {{ number_format($incomes['total']) }}</td>
                    </tr>
                    <tr>
                        <td>
                            تعداد کل صاحب بار : {{ number_format($owners['total']) }}
                            <br>
                            افزایش امروز : {{ number_format($owners['toDay']) }}
                            <br>
                            تعداد تیک سبز دار : {{ number_format($owners['fullAuth']) }}
                        </td>
                        <td>تعداد بار ثبت شده دیروز : {{ number_format($owners['yesterdayLoads']) }}</td>

                        <td></td>
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
                        <td></td>
                        <td>
                            بیشترین بار ثبت شده هفته : 0
                        </td>
                        <td></td>
                        <td>درآمد دیروز : {{ number_format($incomes['yesterday']) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            کمترین بار ثبت شده هفته : 0
                        </td>
                        <td></td>
                        <td>درآمد هفته : {{ number_format($incomes['week']) }}</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td colspan="2">
                            جمع کل بارهای امروز: {{ number_format($owners['toDayLoads'] + $operators['toDayLoads']) }}
                        </td>
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
                        <td colspan="2">
                            جمع کل بارهای دیروز :
                            {{ number_format($owners['yesterdayLoads']) }}
                        </td>
                        <td></td>
                    </tr>
                </table>
            </div>

        </div>
    </div>

@stop
