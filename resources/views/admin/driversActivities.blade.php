@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            فعالیت رانندگان
        </h5>
        <div class="card-body">

            <h4 class="alert alert-info">
                تعداد رانندگان فعال امروز تا این لحظه :
                {{ count($todayActivities) }}
                راننده
            </h4>

            <h4 class="alert alert-success">
                تعداد رانندگان فعال از سی روز گذشته تا این لحظه :
                {{ $currentMonthActivities }}
                راننده
            </h4>

            <form action="{{ url('admin/driversActivities') }}" method="post" class="m-1 p-1 border">
                <div class="row container">
                    @csrf
                    <label class="m-1">جستجو براساس تاریخ : </label>
                    <input id="persianDate" type="text" placeholder="تاریخ فعالیت"
                           class="form-control datepicker col-lg-2 m-1"
                           name="date" required>
                    <button type="submit" class="btn btn-primary col-lg-2 m-1">جستجو</button>
                </div>
            </form>

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام راننده</th>
                    <th>شماره تلفن راننده</th>
                    <th>ناوگان</th>
                    <th>تعداد بار دیده شده</th>
                    <th>تعداد تماس امروز</th>
                    <th>شماره تماس ها</th>
                </tr>
                </thead>
                <tbody>
                @foreach($todayActivities as $key=>$driver)
                    <tr>
                        <td>{{ (($todayActivities->currentPage()-1) * $todayActivities->perPage()) + ($key + 1) }}</td>
                        <td>{{ $driver->driverInfo->name }} {{ $driver->driverInfo->lastName }}</td>
                        <td>{{ $driver->driverInfo->mobileNumber }}</td>
                        <td>{{ $driver->driverInfo->fleetTitle }}</td>
                        <td>{{ $driver->total }}</td>
                        <td>{{ count($driver->driverCalls) }}</td>
                        <td>
                            @foreach($driver->driverCalls as $phoneNumber)
                                <span class="alert alert-info p-1 m-1 small">{{ $phoneNumber->phoneNumber }}</span>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $todayActivities }}
            </div>

        </div>
    </div>

@stop

