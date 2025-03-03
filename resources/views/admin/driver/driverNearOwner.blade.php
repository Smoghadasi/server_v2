@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    رانندگان
                </div>
                <div class="col-6 text-end">
                    <a href="{{ route('admin.sendNearLoadDrivers', ['load_id' => $load->id, 'type' => 'notification']) }}"
                        class="btn btn-primary btn-sm">
                        ارسال نوتیفیکشن ({{ $load->numOfNotif }})
                    </a>
                    <button data-bs-toggle="modal" data-bs-target="#driverNearOwnerCount" class="btn btn-primary btn-sm">
                        ارسال پیامک ({{ $load->numOfSms }})
                    </button>
                    <div id="driverNearOwnerCount" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-dialog-centered">

                            <!-- Modal content-->
                            <form action="{{ route('admin.sendNearLoadDrivers', ['load_id' => $load->id, 'type' => 'sms']) }}" method="get" class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">تعداد ارسال بار</h4>
                                </div>
                                <div class="modal-body text-right">
                                    <div class="row">
                                        <div class="form-group col-lg-12 col-sm-12">
                                            <input class="m-1 form-control" placeholder="تعداد" value="{{ $load->numOfSms }}" name="count" type="text">
                                        </div>
                                    </div>

                                </div>
                                <div class="modal-footer text-left">
                                    <button type="submit" class="btn btn-primary mr-1">ثبت</button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
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
                            <th>وضعیت احراز هویت</th>
                            <th>کد ملی</th>
                            <th>نوع ناوگان</th>
                            <th>تاریخ ثبت نام</th>
                            <th>کد نسخه</th>
                            <th>شماره تلفن همراه</th>
                            <th>فاصله</th>
                            <th>زمان آنلاین</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $driver->name }} {{ $driver->lastName }}

                                    @if ($driver->status == 0)
                                        <span class="alert alert-danger p-1">غیرفعال</span>
                                    @else
                                        <span class="alert alert-success p-1">فعال</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($driver->authLevel == DRIVER_AUTH_UN_AUTH)
                                        <span class="badge bg-label-danger"> انجام نشده</span>
                                    @elseif ($driver->authLevel == DRIVER_AUTH_SILVER_PENDING)
                                        <span class="badge bg-label-secondary border border-danger"><span
                                                class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                                    @elseif ($driver->authLevel == DRIVER_AUTH_SILVER)
                                        <span class="badge bg-label-secondary">سطح نقره ای</span>
                                    @elseif ($driver->authLevel == DRIVER_AUTH_GOLD_PENDING)
                                        <span class="badge bg-label-warning border border-danger"><span
                                                class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                                    @elseif ($driver->authLevel == DRIVER_AUTH_GOLD)
                                        <span class="badge bg-label-warning">سطح طلایی</span>
                                    @endif
                                </td>
                                <td>{{ $driver->nationalCode }}</td>
                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id) }}</td>
                                <td>
                                    {{ gregorianDateToPersian($driver->created_at, '-', true) }}
                                    @if (isset(explode(' ', $driver->created_at)[1]))
                                        {{ explode(' ', $driver->created_at)[1] }}
                                    @endif
                                </td>
                                <td>{{ $driver->version ?? '-' }}</td>
                                <td>{{ $driver->mobileNumber }}</td>
                                <td>{{ $driver->distance < 1 ? 'کمتر از یک کیلومتر' : number_format($driver->distance) . ' کیلومتر ' }}
                                </td>
                                @php
                                    $time = explode(' ', $driver->location_at);
                                @endphp

                                <td>{{ gregorianDateToPersian($driver->location_at, '-', true) }} {{ $time[1] }}</td>
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
