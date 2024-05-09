@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    فعالیت رانندگان بر اساس زمان (امروز)
                </div>
                <div class="col" style="text-align: left;">
                    <a href="#" class="alert p-1 alert-primary">مجموع تعداد تماس امروز:</a>
                </div>
            </div>
        </h5>
        <div class="card-body">
            <form method="post" action="{{ route('search.driver.activitiesCallDate') }}">
                @csrf
                <div class="form-group row">
                    <div class="col-md-4 my-3">
                        <input class="form-control" name="phoneNumber" id="phoneNumber" placeholder="جستجو بر اساس: شماره تماس گرفته شده">
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
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
                        @foreach ($driversActivitiesCallDates as $key => $driversActivitiesCallDate)
                            <tr>
                                <td>
                                    {{ $loop->iteration }}
                                </td>
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
        </div>
    </div>
@endsection
