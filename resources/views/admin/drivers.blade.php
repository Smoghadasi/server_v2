@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            رانندگان
        </h5>
        <div class="card-body">

            @if (auth()->user()->role == 'admin')
                <div class="alert alert-info text-right">
                    @if (isDriverAutoActive())
                        تایید رانندگان بصورت خودکار
                        <a class="btn btn-danger" href="{{ url('admin/changeSiteOption/driverAutoActive') }}">
                            تغییر به غیر خودکار
                        </a>
                    @else
                        تایید رانندگان بصورت غیر خودکار
                        <a class="btn btn-primary" href="{{ url('admin/changeSiteOption/driverAutoActive') }}">
                            تغییر به خودکار
                        </a>
                    @endif
                </div>
            @endif

            <div class="col-md-12 mb-3">
                <a class="btn btn-primary" href="{{ url('admin/addNewDriverForm') }}"> + افزودن راننده</a>
            </div>

            <form action="{{ url('admin/searchDrivers') }}" method="post">
                @csrf
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    <h6>جستجوی رانندگان : </h6>
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>نام :</label>
                                    <input type="text" name="name" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>نام خانوادگی :</label>
                                    <input type="text" name="lastName" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>شماره تلفن :</label>
                                    <input type="text" name="mobileNumber" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>نوع ناوگان :</label>
                                    <select class="form-select" name="fleet_id">
                                        <option value="0">انتخاب ناوگان</option>
                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>


                </div>
            </form>

            @if (isset($showSearchResult) && $showSearchResult)
                <div class="col-lg-12 alert alert-info">
                    تعداد یافته ها :
                    {{ count($drivers) }}
                    راننده
                </div>
            @endif

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
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>

                        @foreach ($drivers as $driver)
                            <tr>
                                <td>{{ ($drivers->currentPage() - 1) * $drivers->perPage() + ++$i }}</td>
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
                                <td style="line-height: 2rem;">
                                    <div class="dropdown">
                                        <div class="btn-group dropstart">
                                            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                عملیات
                                            </button>
                                            <ul class="dropdown-menu">
                                                @if(in_array('detailDriver',auth()->user()->userAccess))
                                                    <li><a class="dropdown-item" href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">جزئیات</a></li>
                                                    <li><a class="dropdown-item" href="{{ url('admin/editDriver') }}/{{ $driver->id }}">ویرایش</a></li>
                                                @endif
                                                <li>
                                                    @if (auth()->user()->role == ROLE_ADMIN)
                                                        @if ($driver->status == 0)
                                                            <a class="dropdown-item" href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">فعال شود</a>
                                                        @else
                                                            <a class="dropdown-item" href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">غیر فعال شود</a>
                                                        @endif
                                                    @endif
                                                </li>

                                                @if (auth()->user()->role == ROLE_ADMIN)
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                                data-bs-target="#removeDriver_{{ $driver->id }}">حذف
                                                        </button>
                                                    </li>
                                                @endif
                                                @if(in_array('driversPaymentReport',auth()->user()->userAccess))
                                                    <li>
                                                        <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                                data-bs-target="#creditDriverExtending_{{ $driver->id }}">تمدید اعتبار
                                                        </button>
                                                    </li>
                                                    <li>
                                                        <form action="{{ route('drivers-payment-report') }}" method="post" style="display: inline;">
                                                            @csrf
                                                            <input type="hidden" class="form-control" name="mobileNumber"
                                                                   placeholder="شماره تلفن"
                                                                   @if (isset($driver->mobileNumber)) value="{{ $driver->mobileNumber }}" @endif>
                                                            <button class="dropdown-item" type="submit">لیست پرداختی
                                                                ها</button>
                                                        </form>
                                                    </li>
                                                @endif
                                                @if (auth()->user()->role == ROLE_ADMIN && (strlen($driver->ip) > 0 || $driver->ip != null))
                                                    @if ($driver->blockedIp == false)
                                                        <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#blockUserIp_{{ $driver->id }}">مسدود کردن IP
                                                        </button>
                                                    @else
                                                        <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#unBlockUserIp_{{ $driver->id }}">
                                                            حذف از لیست Ipهای مسدود
                                                        </button>
                                                    @endif
                                                @endif
                                                @if(in_array('contactReportWithCargoOwners',auth()->user()->userAccess))
                                                    <li><a class="dropdown-item" href="{{ route('contactingWithDriverResult', $driver->id) }}">پیام ها</a></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>


                                    <div id="creditDriverExtending_{{ $driver->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <form action="{{ url('admin/creditDriverExtending') }}/{{ $driver->id }}"
                                                  method="post" class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">تمدید اعتبار راننده</h4>
                                                </div>
                                                <div class="modal-body text-right">

                                                    <div class="h6"> مدت اعتبار، تعداد تماس و تعداد بار را وارد
                                                        نمایید:
                                                    </div>

                                                    <div class="form-group">
                                                        <lable> مدت اعتبار به ماه :</lable>
                                                        <input type="number" class="form-control" name="month"
                                                               value="0" placeholder="مدت اعتبار">
                                                    </div>

                                                    <div class="form-group">
                                                        <lable> تعداد تماس رایگان :</lable>
                                                        <input type="number" class="form-control" name="freeCalls"
                                                               value="0" placeholder="تعداد تماس رایگان">
                                                    </div>

                                                    <div class="form-group">
                                                        <lable> تعداد بار رایگان :</lable>
                                                        <input type="number" class="form-control" name="freeAcceptLoads"
                                                               value="0" placeholder="تعداد بار رایگان">
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
                                                        <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
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
                                                            <button type="button" class="btn btn-danger"
                                                                data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @else
                                            <div id="unBlockUserIp_{{ $driver->id }}" class="modal fade"
                                                role="dialog">
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
                                                            <button type="button" class="btn btn-danger"
                                                                data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (!isset($showSearchResult) || !$showSearchResult)
                {{ $drivers }}
            @endif

        </div>
    </div>


@stop
