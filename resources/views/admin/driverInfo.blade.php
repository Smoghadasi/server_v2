@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            اطلاعات راننده
        </h5>
        <!-- Account -->
        <div class="card-body">
            <div class="d-flex align-items-start align-items-sm-center gap-4">
                <img src="{{ asset('assets/img/truck-driver.jpg') }}" alt="user-avatar" class="d-block rounded" height="120"
                    width="120" id="uploadedAvatar" />
                <div class="button-wrapper">
                    <p class="text-muted mb-0">تصویر راننده</p>
                </div>
            </div>
        </div>
        <div class="card-header">
            <form>
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="firstName" class="form-label">نام</label>
                        <input class="form-control" type="text" value="{{ $driver->name }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="lastName" class="form-label">نام خانوادگی</label>
                        <input class="form-control" type="text" name="lastName" id="lastName"
                            value="{{ $driver->lastName }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="mobileNumber" class="form-label">تلفن همراه</label>
                        <input class="form-control" type="text" id="email" name="email"
                            value="{{ $driver->mobileNumber }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label class="form-label" for="city_id">شهرستان</label>
                        <div class="input-group input-group-merge">
                            <input type="text" id="city_id" name="city_id" class="form-control" disabled
                                value="{{ \App\Http\Controllers\AddressController::geCityName($driver->city_id) }}" />
                        </div>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="fleet_id" class="form-label">نوع ناوگان</label>
                        <input type="text" class="form-control" id="fleet_id" name="fleet_id" disabled
                            value="{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id) }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="freeCalls" class="form-label">تماس رایگان</label>
                        <input type="text" class="form-control" disabled value="{{ $driver->freeCalls }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <div class="row">
                            <div class="col">
                                <div id="status">
                                    <label for="state" class="form-label">وضعیت</label>
                                    @if ($driver->status == 0)
                                        <div class="badge rounded-pill bg-secondary d-inline-block">غیر فعال</div>
                                    @elseif($driver->status == 1)
                                        <div class="badge rounded-pill bg-success d-inline-block">فعال</div>
                                    @elseif($driver->status == 2)
                                        <div class="badge rounded-pill bg-warning d-inline-block">خارج از سرویس</div>
                                    @elseif($driver->status == 3)
                                        <div class="badge rounded-pill bg-primary d-inline-block">درحال حمل بار</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col">
                                <div id="notification">
                                    <label for="state" class="form-label">اعلان</label>
                                    @if ($driver->notification == 'enable')
                                        <div class="badge rounded-pill bg-success d-inline-block">فعال</div>
                                    @else
                                        <div class="badge rounded-pill bg-danger d-inline-block">غیر فعال</div>
                                    @endif
                                </div>
                            </div>
                            <div class="col">
                                <div id="sms">
                                    <label for="state" class="form-label">پیامک</label>
                                    @if ($driver->sms == 'enable')
                                        <div class="badge rounded-pill bg-success d-inline-block">فعال</div>
                                    @else
                                        <div class="badge rounded-pill bg-danger d-inline-block">غیر فعال</div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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
