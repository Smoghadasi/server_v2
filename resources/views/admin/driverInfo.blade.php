@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row">
                <div class="col-6">
                    اطلاعات راننده
                </div>
                <div class="col-6" style="justify-items: left">
                    <!-- Icon Dropdown -->
                    <div class="">
                        <div class="">
                            <div class="btn-group m-0">
                                <button type="button" class="btn btn-link  dropdown-toggle hide-arrow"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <form action="{{ route('bookmark.store') }}" method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $driver->id }}" name="user_id">
                                            <input type="hidden" value="driver" name="type">
                                            <button class="dropdown-item"
                                                type="submit">{{ $driver->bookmark ? 'حذف علامت گذاری' : 'علامت گذاری' }}</button>
                                        </form>
                                        {{-- <a class="dropdown-item" href="javascript:void(0);">علامت گذاری</a> --}}
                                    </li>
                                    @if (auth()->user()->role == ROLE_ADMIN)
                                        <li>
                                            <button class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#changeStatusDriver_{{ $driver->id }}">تغییر
                                                وضعیت</button>
                                        </li>
                                        <li>
                                            <button class="dropdown-item" data-bs-toggle="modal"
                                                data-bs-target="#removeActiveDate_{{ $driver->id }}">حذف اشتراک</button>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!--/ Icon Dropdown -->
                </div>
            </div>
        </h5>
        <!-- Account -->
        <div class="card-body">
            <div class="row">
                <div class="col-6">
                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                        <img src="{{ asset('assets/img/truck-driver.jpg') }}" alt="user-avatar" class="d-block rounded"
                            height="120" width="120" id="uploadedAvatar" />
                        <div class="button-wrapper">
                            <p class="text-muted mb-0">تصویر راننده</p>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="row">
                        <div class="col">
                            <div id="status">
                                <label for="state" class="form-label">وضعیت</label>
                                @if ($driver->status == 0)
                                    <a href="#" data-bs-toggle="offcanvas" data-bs-target="#offcanvasEnd"
                                        aria-controls="offcanvasEnd">
                                        @if ($histories->count() > 0)
                                            <div class="badge rounded-pill bg-danger d-inline-block">غیر فعال
                                                <span class="badge bg-white text-primary">{{ $histories->count() }}
                                                </span>
                                            </div>
                                        @endif
                                    </a>
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
            <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasEnd" aria-labelledby="offcanvasEndLabel">
                <div class="offcanvas-header">
                    <h5 id="offcanvasEndLabel" class="offcanvas-title">تاریخچه غیر فعال کردن کاربر</h5>
                    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                        aria-label="Close"></button>
                </div>
                <div class="offcanvas-body my-auto mx-0 flex-grow-0">
                    <div class="demo-inline-spacing mt-3">
                        <ol class="list-group list-group-numbered">
                            @foreach ($histories as $history)
                                <li class="list-group-item">{{ $history->description }}</li>
                            @endforeach
                        </ol>
                    </div>
                </div>
            </div>

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
                    @php
                        $createdAt = \Carbon\Carbon::parse($driver->created_at);
                    @endphp

                    @if ($createdAt->diffInDays(now()) <= 5)
                        <div class="mb-3 col-md-6">
                            <label class="form-label" for="city_id">شهرستان</label>
                            <div class="input-group input-group-merge">
                                <input type="text" id="city_id" name="city_id" class="form-control" disabled
                                    value="{{ \App\Http\Controllers\AddressController::geCityName($driver->city_id) }}" />
                            </div>
                        </div>
                    @endif

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
                        <label for="freeCalls" class="form-label">امتیاز
                            @if ($driver->ratingDriver != null)
                                <a href="{{ route('score.index', ['driver_id' => $driver->id]) }}">(نظرات کاربران)</a>
                            @endif
                        </label>
                        <input type="text" class="form-control" disabled value="{{ $driver->ratingDriver }}" />
                    </div>
                    {{-- @if ($driver->status == 0)
                        <div class="mb-3 col-md-12">
                                <textarea name="" id="" cols="30" rows="10" disabled></textarea>
                        </div>
                    @endif --}}

                </div>
            </form>
            <div class="card">
                {{-- <div class="card-header">اشتراک یا تماس رایگان</div> --}}
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-sm-12">
                            <div class="card-title d-flex align-items-start justify-content-between">
                                <div class="">
                                    اشتراک یا تماس رایگان ({{ $freeCallTotal }})
                                </div>
                                <div class="dropdown">
                                    <button class="btn p-0" type="button" id="cardOpt3" data-bs-toggle="dropdown"
                                        aria-haspopup="true" aria-expanded="false">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="cardOpt3">
                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                            data-bs-target="#searchFreeSubs">
                                            جستجو
                                        </button>
                                    </div>
                                    <!-- Modal -->
                                    <div class="modal fade" id="searchFreeSubs" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('driver.detail', ['driver' => $driver]) }}"
                                                    method="get">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="searchFreeSubsTitle">جستجو اشتراک
                                                            رایگان
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="row">
                                                            <div class="col mb-3">
                                                                <label for="type" class="form-label">نوع</label>
                                                                <select class="form-control form-select" name="type"
                                                                    id="type">
                                                                    <option value="AuthCalls">تماس رایگان</option>
                                                                    <option value="AUTH_VALIDITY">اعتبار رایگان</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="row g-2">
                                                            <div class="col mb-0">
                                                                <label for="fromDate" class="form-label">از تاریخ</label>
                                                                <input class="form-control" type="text" id="fromDate"
                                                                    name="fromDate" placeholder="از تاریخ"
                                                                    autocomplete="off" />
                                                            </div>
                                                            <div class="col mb-0">
                                                                <label for="toDate" class="form-label">تا تاریخ</label>
                                                                <input class="form-control" type="text" name="toDate"
                                                                    id="fromDate" placeholder="تا تاریخ"
                                                                    autocomplete="off" />
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">
                                                            منصرف
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">جستجو</button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="display: block; max-height: 300px; overflow-y: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>نوع</th>
                                            <th>اپراتور</th>
                                            <th>تعداد</th>
                                            <th>تاریخ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($freeSubscriptions as $freeSubscription)
                                            <tr>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>
                                                    @switch($freeSubscription->type)
                                                        @case(AUTH_CALLS)
                                                            <span class="badge bg-label-success"> تماس رایگان</span>
                                                        @break

                                                        @case(AUTH_VALIDITY)
                                                            <span class="badge bg-label-warning"> اعتبار رایگان</span>
                                                        @break

                                                        @case(AUTH_CARGO)
                                                            <span class="badge bg-label-primary"> بار رایگان</span>
                                                        @break

                                                        @case(AUTH_VALIDITY_DELETED)
                                                            <span class="badge bg-label-danger"> حذف اشتراک</span>
                                                        @break
                                                    @endswitch
                                                </td>
                                                <td>
                                                    @if ($freeSubscription->operator_id != null)
                                                        {{ $freeSubscription->operator->name }}
                                                        {{ $freeSubscription->operator->lastName }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $freeSubscription->value }}</td>
                                                @php
                                                    $pieces = explode(' ', $freeSubscription->created_at);
                                                @endphp
                                                <td>{{ gregorianDateToPersian($freeSubscription->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                                                </td>
                                            </tr>
                                        @endforeach


                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <p>تماس ورودی</p>

                            <div class="table-responsive" style="display: block; max-height: 300px; overflow-y: auto;">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            {{-- <th>اپراتور</th> --}}
                                            <th>موضوع</th>
                                            {{-- <th>نتیجه</th> --}}
                                            <th>تاریخ ثبت</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small text-right">
                                        <?php $i = 1; ?>
                                        @forelse ($supports as $key => $support)
                                            <tr class="text-center">
                                                <td>{{ ($supports->currentPage() - 1) * $supports->perPage() + ($key + 1) }}
                                                </td>
                                                <td>
                                                    {{ $support->subject ?? '-' }}
                                                </td>
                                                {{-- <td>
                                                    {{ $support->result ?? '-' }}
                                                </td> --}}
                                                @php
                                                    $pieces = explode(' ', $support->created_at);
                                                @endphp
                                                <td>
                                                    {{ gregorianDateToPersian($support->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-primary mb-3 btn-sm text-nowrap"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#adminMessageForm_{{ $support->id }}">
                                                        {{ $support->result != null || $support->subject != null ? 'مشاهده نتیجه' : 'ثبت نتیجه' }}
                                                    </button>
                                                    <div id="adminMessageForm_{{ $support->id }}" class="modal fade"
                                                        role="dialog">
                                                        <div class="modal-dialog">

                                                            <!-- Modal content-->
                                                            <form
                                                                action="{{ route('admin.indexDriver.update', $support) }}"
                                                                method="post" class="modal-content">
                                                                @csrf
                                                                @method('put')
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">
                                                                        {{ $support->driver->name . ' ' . $support->driver->lastName }}
                                                                    </h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="form-group mb-2">
                                                                        <select class="form-control form-select"
                                                                            name="subject" id="">
                                                                            <option
                                                                                @if ($support->subject == 'خرید اشتراک') selected @endif
                                                                                value="خرید اشتراک">خرید اشتراک</option>
                                                                            <option
                                                                                @if ($support->subject == 'تماس رایگان') selected @endif
                                                                                value="تماس رایگان">تماس رایگان</option>
                                                                            <option
                                                                                @if ($support->subject == 'احراز هویت') selected @endif
                                                                                value="احراز هویت">احراز هویت</option>
                                                                            <option
                                                                                @if ($support->subject == 'استعلام') selected @endif
                                                                                value="استعلام">استعلام</option>
                                                                            <option
                                                                                @if ($support->subject == 'شکایت') selected @endif
                                                                                value="شکایت">شکایت</option>
                                                                            <option
                                                                                @if ($support->subject == 'راهنمایی استفاده') selected @endif
                                                                                value="راهنمایی استفاده">راهنمایی استفاده
                                                                            </option>
                                                                            <option
                                                                                @if ($support->subject == 'ویرایش اطلاعات') selected @endif
                                                                                value="ویرایش اطلاعات">ویرایش اطلاعات
                                                                            </option>
                                                                            <option
                                                                                @if ($support->subject == 'سایر موارد') selected @endif
                                                                                value="سایر موارد">سایر موارد</option>
                                                                        </select>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <textarea class="form-control" name="result" id="result" rows="5" placeholder="پاسخ">{{ $support->result }}</textarea>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer text-left">
                                                                    <button type="submit"
                                                                        class="btn btn-primary mr-1">ثبت پاسخ</button>
                                                                    <button type="button" class="btn btn-danger"
                                                                        data-bs-dismiss="modal">
                                                                        انصراف
                                                                    </button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr class="text-center">
                                                <td colspan="10">
                                                    دیتا مورد نظر یافت نشد
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                @if (Auth::user()->role_id == 'admin')
                                    <div class="mt-3">
                                        {{ $supports }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
        <div class="card-footer">
            @if (in_array('driversContactCall', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ route('report.driversActivitiesCallDate.show', $driver) }}">
                    لیست تماس ها
                </a>
            @endif

            {{-- @if (auth()->user()->role == ROLE_ADMIN)
                <button type="submit" class="btn btn-primary" data-bs-toggle="modal"
                    data-bs-target="#changeStatusDriver_{{ $driver->id }}">
                    {{ $driver->status == 0 ? 'فعال شود' : 'غیر فعال شود' }}
                </button>
            @endif --}}
            {{-- @if (auth()->user()->role == ROLE_ADMIN)
                <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                    data-bs-target="#removeDriver_{{ $driver->id }}">حذف
                </button>
            @endif --}}
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
            {{-- @if (in_array('contactReportWithCargoOwners', auth()->user()->userAccess)) --}}
            <a class="btn btn-primary" href="{{ route('contactingWithDriverResult', $driver->id) }}">پیام
                ها</a>
            {{-- @endif --}}
            @if (in_array('detailDriver', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ url('admin/editDriver') }}/{{ $driver->id }}">ویرایش</a>
            @endif
            @if (in_array('detailDriver', auth()->user()->userAccess))
                <a class="btn btn-primary" href="{{ route('admin.supportDriver.show', $driver->id) }}">ورودی تماس ها</a>
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
                            {{-- <p class="text-danger">تعداد بار رایگان
                                : {{ $driver->freeAcceptLoads }}</p> --}}
                            <p class="text-warning">کل تعداد تماس رایگان داده شده
                                : {{ $driver->freeCallTotal }}</p>

                            <p class="text-danger">مدت اعتبار: </p>
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

                            {{-- <div class="form-group">
                                <lable> تعداد بار رایگان :</lable>
                                <input type="number" class="form-control" name="freeAcceptLoads" value="0"
                                    placeholder="تعداد بار رایگان">
                            </div> --}}


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

            @if (auth()->user()->role == ROLE_ADMIN || Auth::id() == 29)
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
                <div id="changeStatusDriver_{{ $driver->id }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">

                        <!-- Modal content-->
                        <form method="post" action="{{ route('admin.changeDriverStatus', $driver) }}"
                            class="modal-content">
                            @csrf
                            @method('patch')
                            <div class="modal-header">
                                <h4 class="modal-title">تغییر وضعیت</h4>
                            </div>
                            <div class="modal-body">
                                <p>آیا مایل به تغییر وضعیت راننده به
                                    <span class="text-primary">
                                        {{ !$driver->status ? 'فعال' : 'غیر فعال' }}</span>
                                    هستید؟
                                </p>
                                <textarea class="form-control" name="description" id="" cols="30" rows="10"
                                    placeholder="دلیل..."></textarea>
                            </div>
                            <div class="modal-footer text-left">
                                <button type="submit" class="btn btn-primary">ثبت</button>

                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                    انصراف
                                </button>
                            </div>
                        </form>

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

@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #toDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
        // $("#toDate, #span2").persianDatepicker({
        //     formatDate: "YYYY/MM/DD",
        //     selectedBefore: !0
        // });
    </script>
@endsection
