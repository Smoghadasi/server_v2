@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            فعالیت رانندگان بر اساس زمان (امروز)
        </h5>
        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>راننده</th>
                            <th>ناوگان</th>
                            <th>شماره تماس گرفته شده</th>
                            <th>ساعت</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($driversActivitiesCallDates as $driversActivitiesCallDate)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $driversActivitiesCallDate->driver->name . ' ' . $driversActivitiesCallDate->driver->lastName . ' ( ' . $driversActivitiesCallDate->driver->mobileNumber . ' ) ' }}</td>
                                <td>{{ $driversActivitiesCallDate->driver->fleetTitle}}</td>
                                <td>{{ $driversActivitiesCallDate->phoneNumber }}</td>
                                @php
                                    $pieces = explode(' ', $driversActivitiesCallDate->created_at);
                                @endphp
                                <td>{{$pieces[1]}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $driversActivitiesCallDates }}
            </div>
        </div>
    </div>
@endsection
