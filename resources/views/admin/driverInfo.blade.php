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
                    <td>{{ $driver->nationalCode ?? '-' }}</td>
                </tr>
                <tr>
                    <th>تاریخ تولد</th>
                    <td>{{ $driver->birthDate ?? '-' }}</td>
                    <th>شماره همراه</th>
                    <td>{{ $driver->mobileNumber ?? '-' }}</td>
                </tr>
                <tr>
                    <th>شماره هوشمند</th>
                    <td>{{ $driver->smartCode ?? '-' }}</td>
                    <th>شماره کارت</th>
                    <td>{{ $driver->cardNumber ?? '-' }}</td>
                </tr>

                <tr>
                    <th>تاریخ صدور کارت</th>
                    <td>{{ $driver->cardPublishDate ?? '-' }}</td>
                    <th>شهر درخواست کننده</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->applicator_city_id) }}</td>
                </tr>

                <tr>
                    <th>گواهینامه</th>
                    <td>{{ $driver->drivingLicence ?? '-' }}</td>
                    <th>محل دریافت گواهینامه</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->receipt_card_city_id) }}</td>
                </tr>

                <tr>
                    <th>مسافت (مجموع بار و مسافر)</th>
                    <td>{{ $driver->distances ?? '-' }}</td>
                    <th>کارکرد</th>
                    <td>{{ $driver->counter ?? '-' }}</td>
                </tr>

                <tr>
                    <th>شماره پرونده</th>
                    <td>{{ $driver->docNumber ?? '-' }}</td>
                    <th>تاریخ استعلام</th>
                    <td>{{ $driver->inquiryDate ?? '-' }}</td>
                </tr>

                <tr>
                    <th>تحصیلات</th>
                    <td>{{ $driver->degreeOfEdu ?? '-' }}</td>
                    <th>نوع راننده</th>
                    <td>{{ $driver->driverType ?? '-' }}</td>
                </tr>

                <tr>
                    <th>کد بیمه</th>
                    <td>{{ $driver->insuranceCode ?? '-' }}</td>
                    <th>شهرستان</th>
                    <td>{{ \App\Http\Controllers\AddressController::geCityName($driver->city_id) }}</td>
                </tr>

                <tr>
                    <th>نوع ناوگان</th>
                    <td>{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id) }}</td>
                    <th>تاریخ اعتبار</th>
                    <td>{{ $driver->validityDate ?? '-' }}</td>
                </tr>
                <tr>
                    <th>وضعیت</th>
                    <td>
                        @if ($driver->status == 0)
                            <div class="alert alert-secondary d-inline-block">غیر فعال</div>
                        @elseif($driver->status == 1)
                            <div class="alert alert-success d-inline-block">فعال</div>
                        @elseif($driver->status == 2)
                            <div class="alert alert-warning d-inline-block">خارج از سرویس</div>
                        @elseif($driver->status == 3)
                            <div class="alert alert-primary d-inline-block">درحال حمل بار</div>
                        @endif
                    </td>
                    <th>تماس رایگان</th>
                    <td>
                        {{ $driver->freeCalls }}
                    </td>
                </tr>
            </table>
        </div>
        <div class="card-footer">
            @if (in_array('driversContactCall', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ route('report.driversActivitiesCallDate.show', $driver) }}">
                    لیست تماس ها
                </a>
            @endif

            @if (auth()->user()->role == ROLE_ADMIN)
                @if ($driver->status == 0)
                    <a class="btn btn-primary" href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">فعال
                        شود</a>
                @else
                    <a class="btn btn-primary" href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">غیر
                        فعال شود</a>
                @endif
            @endif
            @if (auth()->user()->role == ROLE_ADMIN || Auth::id() == 29)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#removeDriver_{{ $driver->id }}">حذف
                </button>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#removeActiveDate_{{ $driver->id }}">حذف اشتراک
                </button>
            @endif
            @if (in_array('driversPaymentReport', auth()->user()->userAccess))
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#creditDriverExtending_{{ $driver->id }}">
                    تمدید اعتبار
                </button>


                <form action="{{ route('drivers-payment-report') }}" method="post" style="display: inline;">
                    @csrf
                    <input type="hidden" class="form-control" name="mobileNumber" placeholder="شماره تلفن"
                        @if (isset($driver->mobileNumber)) value="{{ $driver->mobileNumber }}" @endif>
                    <button class="btn btn-primary" type="submit">لیست پرداختی
                        ها
                    </button>
                </form>
            @endif
            @if (auth()->user()->role == ROLE_ADMIN && (strlen($driver->ip) > 0 || $driver->ip != null))
                @if ($driver->blockedIp == false)
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#blockUserIp_{{ $driver->id }}">مسدود کردن
                        IP
                    </button>
                @else
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                        data-bs-target="#unBlockUserIp_{{ $driver->id }}">
                        حذف از لیست Ipهای مسدود
                    </button>
                @endif
            @endif
            @if (in_array('contactReportWithCargoOwners', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ route('contactingWithDriverResult', $driver->id) }}">پیام
                    ها</a>
            @endif
            @if (in_array('detailDriver', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ url('admin/editDriver') }}/{{ $driver->id }}">ویرایش</a>
            @endif
            <div id="creditDriverExtending_{{ $driver->id }}" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <form action="{{ url('admin/creditDriverExtending') }}/{{ $driver->id }}" method="post"
                        class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">تمدید اعتبار راننده</h4>
                        </div>
                        <div class="modal-body text-right">

                            <div class="h6"> مدت اعتبار، تعداد تماس و تعداد بار را وارد
                                نمایید:
                            </div>
                            <p class="text-danger">تعداد بار رایگان
                                : {{ $driver->freeAcceptLoads }}</p>
                            <p class="text-warning">کل تعداد تماس رایگان داده شده
                                : {{ $driver->freeCallTotal }}</p>

                            <p class="text-danger">مدت اعتبار</p>
                            {{ gregorianDateToPersian($driver->activeDate, '-', true) }}
                            <div class="form-group">
                                <lable> مدت اعتبار به ماه :</lable>
                                <input type="number" class="form-control" name="month" value="0"
                                    placeholder="مدت اعتبار">
                            </div>

                            <div class="form-group">
                                <lable> تعداد تماس رایگان :</lable>
                                <input type="number" class="form-control" name="freeCalls" value="0"
                                    placeholder="تعداد تماس رایگان">
                            </div>

                            <div class="form-group">
                                <lable> تعداد بار رایگان :</lable>
                                <input type="number" class="form-control" name="freeAcceptLoads" value="0"
                                    placeholder="تعداد بار رایگان">
                            </div>


                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary">
                                تمدید شود
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>

                </div>
            </div>

            @if (auth()->user()->role == ROLE_ADMIN)
                <div id="removeDriver_{{ $driver->id }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">حذف راننده</h4>
                            </div>
                            <div class="modal-body">
                                <p>آیا مایل به حذف راننده
                                    <span class="text-primary">
                                        {{ $driver->name }}{{ $driver->lastName }}</span>
                                    هستید؟
                                </p>
                            </div>
                            <div class="modal-footer text-left">
                                <a class="btn btn-primary"
                                    href="{{ url('admin/removeDriver') }}/{{ $driver->id }}">حذف
                                    راننده</a>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                    انصراف
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
                <div id="removeActiveDate_{{ $driver->id }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title">حذف اشتراک راننده</h4>
                            </div>
                            <div class="modal-body">
                                <p>آیا مایل به حذف اشتراک راننده
                                    <span class="text-primary">
                                        {{ $driver->name }} {{ $driver->lastName }}</span>
                                    هستید؟
                                </p>
                            </div>
                            <div class="modal-footer text-left">
                                <form action="{{ route('removeActiveDate', $driver) }}" method="post">
                                    @method('put')
                                    @csrf
                                    <button class="btn btn-primary">حذف</button>
                                </form>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                    انصراف
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            @endif


            @if (auth()->user()->role == ROLE_ADMIN && (strlen($driver->ip) > 0 || $driver->ip != null))
                @if ($driver->blockedIp == false)
                    <div id="blockUserIp_{{ $driver->id }}" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">مسدود کردن IP</h4>
                                </div>
                                <div class="modal-body">
                                    <p>آیا مایل به مسدود کردن IP
                                        <span class="text-primary"> {{ $driver->name }}
                                            {{ $driver->lastName }}</span>
                                        هستید؟
                                    </p>
                                </div>
                                <div class="modal-footer text-left">
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/blockUserIp') }}/{{ $driver->id }}/{{ ROLE_DRIVER }}/{{ $driver->ip }}">
                                        بله مسدود شود
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div id="unBlockUserIp_{{ $driver->id }}" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">حذف از لیست Ipهای مسدود</h4>
                                </div>
                                <div class="modal-body">
                                    <p>آیا مایل به حذف کردن IP
                                        <span class="text-primary"> {{ $driver->name }}
                                            {{ $driver->lastName }}</span>
                                        از لیست مسدودها هستید؟
                                    </p>
                                </div>
                                <div class="modal-footer text-left">
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/unBlockUserIp') }}/{{ $driver->id }}/{{ ROLE_DRIVER }}">
                                        بله از لیست حذف شود
                                    </a>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

@stop
