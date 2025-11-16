@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">گزارش میزان فعالیت اپراتورها به ساعت در روز جاری</h5>
        <div class="card-body">

            <form action="operatorsWorkingHoursActivityReport" method="post" class="mb-2">
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

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام اپراتور</th>
                        <th>ساعت شروع</th>
                        <th>ساعت پایان</th>
                        <th>میزان فعالیت به دقیقه / ساعت</th>
                        <th>تعداد بار های ثبت شده</th>
                        <th>میانگین ثبت بار امروز</th>
                        <th>
                            وضعیت فعلی ( <span class="text-success">آنلاین</span> / <span class="text-danger">آفلاین</span>
                            )
                        </th>
                        <th>گزارش</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $i = 1;
                        $totalLoads = 0; // متغیر برای جمع کل بارها

                    @endphp

                    @foreach ($users as $user)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>{{ $user->name }} {{ $user->lastName }}</td>
                            <td>{{ $user->firstLoad()?->created_at->format('H:i:s') ?? '-' }}</td>

                            <td>{{ $user->lastLoad()?->created_at->format('H:i:s') ?? '-' }}</td>
                            <td>{{ $user->userActivityReport * 5 }} دقیقه | {{ ($user->userActivityReport * 5) % 60 }}
                                : {{ intdiv($user->userActivityReport * 5, 60) }} ساعت
                            </td>
                            <td>{{ $user->numOfLoads() }}
                                @php
                                    $totalLoads += $user->numOfLoads(); // جمع کردن تعداد بارها
                                @endphp

                            </td>
                            <td>
                                {{ $user->user->avgLoadSubmit() ?? '-' }}
                            </td>
                            <td>
                                @if (Cache::has('user-is-online-' . $user->user_id))
                                    <span class="text-success">آنلاین</span>
                                @else
                                    <span class="text-danger">آفلاین</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{ route('operatorsActivityReport', ['operator_id' => $user->user_id]) }}">
                                    گزارش گیری
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $totalLoads }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop
