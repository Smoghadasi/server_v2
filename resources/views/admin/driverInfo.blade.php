@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            اطلاعات راننده
        </h5>
        <div class="card-body">

            <table class="table">
                <tr>
                    <th>نام و نام خانوادگی</th>
                    <td>{{ $driver->name }} {{ $driver->lastName }}</td>
                    <th>کد ملی</th>
                    <td>{{ $driver->nationalCode }}</td>
                </tr>
                <tr>
                    <th>تاریخ تولد</th>
                    <td>{{ $driver->birthDate }}</td>
                    <th>شماره همراه</th>
                    <td>{{ $driver->mobileNumber }}</td>
                </tr>
                <tr>
                    <th>شماره هوشمند</th>
                    <td>{{ $driver->smartCode }}</td>
                    <th>شماره کارت</th>
                    <td>{{ $driver->cardNumber }}</td>
                </tr>

                <tr>
                    <th>تاریخ صدور کارت</th>
                    <td>{{ $driver->cardPublishDate}}</td>
                    <th>شهر درخواست کننده</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->applicator_city_id)  }}</td>
                </tr>

                <tr>
                    <th>گواهینامه</th>
                    <td>{{ $driver->drivingLicence}}</td>
                    <th>محل دریافت گواهینامه</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->receipt_card_city_id) }}</td>
                </tr>

                <tr>
                    <th>مسافت (مجموع بار و مسافر)</th>
                    <td>{{ $driver->distances}}</td>
                    <th>کارکرد</th>
                    <td>{{ $driver->counter}}</td>
                </tr>

                <tr>
                    <th>شماره پرونده</th>
                    <td>{{ $driver->docNumber}}</td>
                    <th>تاریخ استعلام</th>
                    <td>{{ $driver->inquiryDate}}</td>
                </tr>

                <tr>
                    <th>تحصیلات</th>
                    <td>{{ $driver->degreeOfEdu}}</td>
                    <th>نوع راننده</th>
                    <td>{{ $driver->driverType}}</td>
                </tr>

                <tr>
                    <th>کد بیمه</th>
                    <td>{{ $driver->insuranceCode}}</td>
                    <th>شهرستان</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->city_id)  }}</td>
                </tr>

                <tr>
                    <th>نوع ناوگان</th>
                    <td>{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id)  }}</td>
                    <th>تاریخ اعتبار</th>
                    <td>{{ $driver->validityDate}}</td>
                </tr>
                <tr>
                    <th>وضعیت</th>
                    <td>
                        @if($driver->status == 0)
                            <div class="alert alert-secondary d-inline-block">غیر فعال</div>
                        @elseif($driver->status == 1)
                            <div class="alert alert-success d-inline-block">فعال</div>
                        @elseif($driver->status == 2)
                            <div class="alert alert-warning d-inline-block">خارج از سرویس</div>
                        @elseif($driver->status == 3)
                            <div class="alert alert-primary d-inline-block">درحال حمل بار</div>
                        @endif
                    </td>
                    <th>تصویر</th>
                    <td>
                        <img src="{{ url('pictures/drivers') }}/{{ $driver->pic}}"
                             class="img-thumbnail" width="100" height="100">
                    </td>
                </tr>
            </table>
        </div>
    </div>

@stop
