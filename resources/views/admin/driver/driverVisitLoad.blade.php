@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    رانندگان ({{ $driverVisitLoads->total() }}) - {{ $load->driverVisitCount }}
                </div>
                <div class="col-6 text-end">
                    <a href="{{ route('admin.sendNotifLoadVisit', ['load_id' => $load->id, 'type' => 'notification']) }}"
                        class="btn btn-primary btn-sm">
                        ارسال نوتیفیکشن
                    </a>
                    {{-- <button data-bs-toggle="modal" data-bs-target="#driverNearOwnerCount" class="btn btn-primary btn-sm">
                        ارسال پیامک ({{ $load->numOfSms }})
                    </button>
                    <div id="driverNearOwnerCount" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-dialog-centered">

                            <!-- Modal content-->
                            <form
                                action="{{ route('admin.sendNearLoadDrivers', ['load_id' => $load->id, 'type' => 'sms']) }}"
                                method="get" class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">تعداد ارسال بار</h4>
                                </div>
                                <div class="modal-body text-right">
                                    <div class="row">
                                        <div class="form-group col-lg-12 col-sm-12">
                                            <input class="m-1 form-control" placeholder="تعداد" value=""
                                                name="count" type="text">
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
                    </div> --}}
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
                        @forelse ($driverVisitLoads as $driverVisitLoad)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $driverVisitLoad->driver->name }} {{ $driverVisitLoad->driver->lastName }}
                                    @if (\App\Http\Controllers\LoadController::driverCallLoadExists($driverVisitLoad->driver->id, $load->id) == 1)
                                        <i class="menu-icon tf-icons bx bx-support"></i>
                                    @endif

                                </td>
                                <td>{{ $driverVisitLoad->driver->fleetTitle }}</td>
                                <td>{{ $driverVisitLoad->driver->mobileNumber }}</td>

                                @php
                                    $time = explode(' ', $driverVisitLoad->created_at);
                                @endphp

                                <td>{{ $driverVisitLoad->driver->city_id ? \App\Http\Controllers\AddressController::geCityName($driverVisitLoad->driver->city_id) : '-' }}
                                </td>
                                <td>{{ $driverVisitLoad->count }}</td>
                                <td>{{ gregorianDateToPersian($driverVisitLoad->created_at, '-', true) }}
                                    {{ $time[1] }}</td>
                                <td>{{ $driverVisitLoad->driver->activeDate ? gregorianDateToPersian($driverVisitLoad->driver->activeDate, '-', true) : 'ندارد' }}
                                </td>
                                <td>{{ $driverVisitLoad->driver->freeCalls }}</td>

                                <td>
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/driverInfo') }}/{{ $driverVisitLoad->driver->id }}">جزئیات</a>
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
                {{ $driverVisitLoads }}
            </div>
        </div>
    </div>
@endsection
