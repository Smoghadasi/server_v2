@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    رانندگان ({{ $drivers->total() }})
                </div>

            </div>
        </h5>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>نوع ناوگان</th>
                            <th>شماره تلفن همراه</th>
                            <th>زمان آنلاین</th>
                            <th>شهر</th>
                            <th>تعداد بازدید</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td> {{ $driver->name }} {{ $driver->lastName }}</td>
                                <td>{{ $driver->fleetTitle }}</td>
                                <td>{{ $driver->mobileNumber }}</td>

                                @php
                                    $time = explode(' ', $driver->location_at);
                                @endphp

                                <td>{{ gregorianDateToPersian($driver->location_at, '-', true) }} {{ $time[1] }}</td>
                                <td>{{ $driver->city_id ? \App\Http\Controllers\AddressController::geCityName($driver->city_id) : '-'  }}</td>
                                <td>{{ $driver->driverVisitLoad->count }}</td>
                                <td>
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">جزئیات</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">
                                    هیچ راننده ای یافت نشد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
