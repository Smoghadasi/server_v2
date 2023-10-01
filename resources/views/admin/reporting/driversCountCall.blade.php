@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش رانندگان بر اساس تماس
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
                        <th>تاریخ</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($basedCalls as $basedCall)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $basedCall->driver->name . " " . $basedCall->driver->lastName ?? '-' }}</td>
                            <td>{{ $basedCall->driver->mobileNumber ?? '-' }}</td>
                            <td>{{ $basedCall->countOfCalls }}</td>
                            <td>{{ $basedCall->persian_date }}</td>
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
