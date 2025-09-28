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
                    <th>
                        وضعیت فعلی ( <span class="text-success">آنلاین</span> / <span class="text-danger">آفلاین</span> )
                    </th>
                    <th>گزارش</th>
                </tr>
                </thead>
                <tbody>
                @php
                    $i=1;
                @endphp

                @foreach($users as $user)

                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>{{ $user->name }} {{ $user->lastName }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $user->userActivityReport * 5 }} / {{ (($user->userActivityReport * 5) % 60) }}
                            : {{ intdiv($user->userActivityReport * 5, 60) }}
                        </td>
                        <td></td>
                        <td>
                            @if(Cache::has('user-is-online-' . $user->user_id))
                                <span class="text-success">آنلاین</span>
                            @else
                                <span class="text-danger">آفلاین</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop



