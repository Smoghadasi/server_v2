@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    رانندگان ({{ $drivers->total() }}) - {{ $load->driverVisitCounts }}
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
                            <th>شهر</th>
                            <th>تعداد بازدید</th>
                            <th>زمان بازدید</th>
                            <th>تاریخ آخرین اشتراک</th>
                            <th>تماس رایگان</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $driver->name }} {{ $driver->lastName }}
                                    @if (\App\Http\Controllers\LoadController::driverCallLoadExists($driver->id, $load->id))
                                    <i class="menu-icon tf-icons bx bx-support"></i>
                                    @endif

                                </td>
                                <td>{{ $driver->fleetTitle }}</td>
                                <td>{{ $driver->mobileNumber }}</td>

                                @php
                                    $time = explode(' ', $driver->driverVisitLoad->created_at);
                                @endphp

                                <td>{{ $driver->city_id ? \App\Http\Controllers\AddressController::geCityName($driver->city_id) : '-'  }}</td>
                                <td>{{ $driver->driverVisitLoad->count }}</td>
                                <td>{{ gregorianDateToPersian($driver->driverVisitLoad->created_at, '-', true) }} {{ $time[1] }}</td>
                                <td>{{ $driver->activeDate ? gregorianDateToPersian($driver->activeDate, '-', true) : 'ندارد' }}</td>
                                <td>{{ $driver->freeCalls }}</td>

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
