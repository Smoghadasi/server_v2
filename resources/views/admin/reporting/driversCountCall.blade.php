@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش رانندگان بر اساس بیشترین تماس
        </h5>
        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>راننده</th>
                        <th>تلفن</th>
                        <th>تعداد</th>
{{--                        <th>تاریخ تماس</th>--}}
{{--                        <th>تاریخ ثبت نام</th>--}}
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($basedCalls as $basedCall)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $basedCall->driver->name . " " . $basedCall->driver->lastName ?? '-' }}</td>
                            <td>{{ $basedCall->driver->mobileNumber ?? '-' }}</td>
                            <td>{{ $basedCall->countOfCalls }}</td>
{{--                            <td>{{ $basedCall->persian_date }}</td>--}}
{{--                            <td>--}}
{{--                                {{ gregorianDateToPersian($basedCall->created_date, '-', true) }}--}}
{{--                                @if (isset(explode(' ', $basedCall->created_date)[1]))--}}
{{--                                    ({{ explode(' ', $basedCall->created_date)[1] }})--}}
{{--                                @else--}}
{{--                                    ---}}
{{--                                @endif--}}
{{--                            </td>--}}
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $basedCalls }}
            </div>
        </div>
    </div>
@endsection
