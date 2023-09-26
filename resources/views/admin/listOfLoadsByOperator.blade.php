@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارهای ثبت شده توسط اپراتورها
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>ردیف</th>
                    <th>نام اپراتور</th>
                    <th>تعداد بار ثبت شده در امروز</th>
                    <th>جزییات ناوگان امروز</th>
                    <th>تعداد بار ثبت شده در هفته جاری</th>
                    <th>جزییات ناوگان هفته جاری</th>
                    <th>تعداد کل بارهای ثت شده</th>
                    <th>جزییات ناوگان کل بارها</th>
                </tr>
                </thead>
                <tbode class="text-right">
                    @foreach($users as $key => $user)
                        @if($user->id == 28 && auth()->id() != $user->id && auth()->user()->role != ROLE_ADMIN)
                            @continue
                        @endif
                        <tr class="text-right">
                            <td>{{ $key+1 }}</td>
                            <td>{{ $user->name }} {{ $user->lastName }}</td>
                            <td>{{ $user->numOfTodayLoads }}</td>
                            <td>

                                @foreach($user->countOfFleetsInToday as $countOfFleetsInToday)
                                    <div>
                                        {{ $countOfFleetsInToday->title }} -
                                        تعداد : {{ $countOfFleetsInToday->numOfFleets }}
                                    </div>
                                @endforeach
                            </td>

                            <td>{{ $user->numOfThisWeekLoads }}</td>
                            <td>

                                @foreach($user->countOfFleetsInThisWeek as $countOfFleetsInThisWeek)
                                    <div>
                                        {{ $countOfFleetsInThisWeek->title }} -
                                        تعداد : {{ $countOfFleetsInThisWeek->numOfFleets }}
                                    </div>
                                @endforeach</td>


                            <td>{{ $user->numOfAllLoads }}</td>
                            <td>

                                @foreach($user->countOfFleetsInAll as $countOfFleetsInAll)
                                    <div>
                                        {{ $countOfFleetsInAll->title }} -
                                        تعداد : {{ $countOfFleetsInAll->numOfFleets }}
                                    </div>
                                @endforeach</td>
                        </tr>
                    @endforeach


                    <tr class="bg-info text-white">
                        <th>جمع بارها :</th>
                        <th>جمع امروز :</th>
                        <td>{{ $countOfToday }} بار</td>
                        <th>جمع این هفته :</th>
                        <td>{{ $countOfThisWeek }} بار</td>
                        <th>جمع کل :</th>
                        <td>{{ $countOfAll }} بار</td>
                        <td></td>
                    </tr>

                </tbode>
            </table>
        </div>
    </div>

@stop
