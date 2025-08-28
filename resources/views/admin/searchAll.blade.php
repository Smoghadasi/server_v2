@extends('layouts.dashboard')

@section('content')
    @if (Auth::user()->role == 'admin' || Auth::id() == 29)
        <h4 class="fw-bold py-3 mb-4"><span class="text-muted fw-light">کد فعال سازی: </span> {{ $activationCode }}</h4>
    @endif
    <div class="card mb-4">
        <h5 class="card-header">
            راننده
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
                            <th>ورژن</th>
                            <th>شماره تلفن همراه</th>
                            <th>استان - شهر</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>

                        @forelse ($drivers as $driver)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($driver->bookmark)
                                        <form style="display: contents" action="{{ route('bookmark.store') }}" method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $driver->id }}" name="user_id">
                                            <input type="hidden" value="driver" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bxs-bookmark-star'></i>
                                            </button>
                                        </form>
                                    @else
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $driver->id }}" name="user_id">
                                            <input type="hidden" value="driver" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bx-bookmark'></i>
                                            </button>
                                        </form>
                                    @endif
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
                                <td>
                                    <span class="text-primary">{{ $driver->mobileNumber }}</span>
                                    @foreach ($driver->driverMobiles as $driverMobile)
                                        , {{ $driverMobile->mobileNumber }}
                                    @endforeach
                                </td>
                                <td>{{ $driver->provinceOwner?->name ?? '-' }} {{ $driver->cityOwner?->name ?? '-' }}</td>
                                <td>
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">جزئیات</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">فیلد مورد خالی است</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
    <div class="card">
        <h5 class="card-header">صاحب بار</h5>
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>احراز هویت</th>
                            <th>نوع</th>
                            <th>کد ملی</th>
                            <th>شماره موبایل</th>
                            <th>بار ها</th>
                            <th>ورژن</th>
                            <th class="text-center">تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($owners as $owner)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @if ($owner->bookmark)
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $owner->id }}" name="user_id">
                                            <input type="hidden" value="owner" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bxs-bookmark-star'></i>
                                            </button>
                                        </form>
                                    @else
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $owner->id }}" name="user_id">
                                            <input type="hidden" value="owner" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bx-bookmark'></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{ $owner->name }} {{ $owner->lastName }}
                                    @if ($owner->status == 1)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیر فعال</span>
                                    @endif
                                    @if ($owner->moreDayLoad >= 3)
                                        <span class="badge bg-primary">3+</span>
                                    @endif
                                    @if ($owner->isAccepted == 1)
                                        <i class="menu-icon tf-icons bx bx-check-shield text-success"></i>
                                    @endif
                                </td>
                                <td>
                                    @switch($owner->isAuth)
                                        @case(0)
                                            <span class="badge bg-danger">انجام نشده</span>
                                        @break

                                        @case(1)
                                            <span class="badge bg-success">انجام شده</span>
                                        @break

                                        @case(2)
                                            <span class="badge bg-secondary">در حال بررسی</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>
                                    @switch($owner->isOwner)
                                        @case(1)
                                            صاحب بار
                                        @break

                                        @case(2)
                                            باربری
                                        @break

                                        @default
                                            تعیین نشده
                                    @endswitch
                                </td>
                                <td>{{ $owner->nationalCode }}</td>
                                <td> <span class="text-primary">{{ $owner->mobileNumber }}</span>
                                    @foreach ($owner->ownerMobiles as $ownerMobile)
                                        , {{ $ownerMobile->mobileNumber }}
                                    @endforeach
                                </td>
                                <td>
                                    <a href="{{ route('owner.loads', $owner->id) }}">{{ $owner->numOfLoads }}</a>
                                </td>
                                <td>
                                    {{ $owner->version ?? 1 }}
                                </td>
                                @php
                                    $pieces = explode(' ', $owner->created_at);
                                @endphp
                                <td dir="ltr">
                                    {{ gregorianDateToPersian($owner->created_at, '-', true) . ' ' . $pieces[1] }}
                                </td>

                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('owner.show', $owner) }}">مشاهده</a>
                                </td>
                            </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="10">فیلد مورد خالی است</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- شکایات و انتقادات راننده --}}
        <div class="card my-4">
            <h5 class="card-header">
                گزارش تخلف راننده
            </h5>
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>راننده</th>
                                <th>صاحب بار</th>
                                <th>بار</th>
                                <th>نوع</th>
                                <th>متن پیام</th>
                                <th>پاسخ ادمین</th>
                                <th>تاریخ</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reportDrivers as $key => $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td><a href="{{ route('driver.detail', $report->driver_id) }}">{{ $report->driver->name }}
                                            {{ $report->driver->lastName }}</a></td>
                                    <td>
                                        @if ($report->owner_id == null)
                                            -
                                        @else
                                            <a href="{{ route('owner.show', $report->owner_id) }}">{{ $report->owner->name }}
                                                {{ $report->owner->lastName }}</a>
                                        @endif
                                    </td>
                                    <td><a href="{{ route('loadInfo', $report->load_id) }}">{{ $report->cargo->title }}</a>
                                    </td>
                                    <td>
                                        @switch($report->type)
                                            @case('owner')
                                                صاحب بار
                                            @break

                                            @case('driver')
                                                راننده
                                            @break
                                        @endswitch
                                    </td>
                                    <td>{{ $report->description }}</td>
                                    <td>{{ $report->adminMessage ?? '-' }}</td>
                                    @php
                                        $pieces = explode(' ', $report->created_at);
                                    @endphp
                                    <td>{{ gregorianDateToPersian($report->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}

                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3"
                                            data-bs-toggle="modal" data-bs-target="#adminMessageForm_{{ $report->id }}">
                                            پاسخ به صاحب بار
                                        </button>

                                        <div id="adminMessageForm_{{ $report->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <form action="{{ route('report.update', $report) }}" method="post"
                                                    class="modal-content">
                                                    @csrf
                                                    @method('put')
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">پاسخ</h4>
                                                    </div>
                                                    <div class="modal-body text-right">

                                                        <div class="form-group">
                                                            <label>متن پاسخ ادمین :</label>
                                                            <textarea class="form-control" name="adminMessage" id="adminMessage" placeholder="پاسخ ادمین"></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
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
                                        <td colspan="10">دیتایی یافت نشد</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>

            {{-- شکایات و انتقادات صاحب بار --}}
            <div class="card my-4">
                <h5 class="card-header">
                    شکایات و انتقادات صاحب بار
                </h5>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>راننده</th>
                                    <th>صاحب بار</th>
                                    <th>بار</th>
                                    <th>نوع</th>
                                    <th>متن پیام</th>
                                    <th>پاسخ ادمین</th>
                                    <th>تاریخ</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportOwners as $key => $report)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td><a href="{{ route('driver.detail', $report->driver_id) }}">{{ $report->driver->name }}
                                                {{ $report->driver->lastName }}</a></td>
                                        <td>
                                            @if ($report->owner_id == null)
                                                -
                                            @else
                                                <a href="{{ route('owner.show', $report->owner_id) }}">{{ $report->owner->name }}
                                                    {{ $report->owner->lastName }}</a>
                                            @endif
                                        </td>
                                        <td><a href="{{ route('loadInfo', $report->load_id) }}">{{ $report->cargo->title }}</a>
                                        </td>
                                        <td>
                                            @switch($report->type)
                                                @case('owner')
                                                    صاحب بار
                                                @break

                                                @case('driver')
                                                    راننده
                                                @break
                                            @endswitch
                                        </td>
                                        <td>{{ $report->description }}</td>
                                        <td>{{ $report->adminMessage ?? '-' }}</td>
                                        @php
                                            $pieces = explode(' ', $report->created_at);
                                        @endphp
                                        <td>{{ gregorianDateToPersian($report->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}

                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3"
                                                data-bs-toggle="modal" data-bs-target="#adminMessageForm_{{ $report->id }}">
                                                پاسخ به صاحب بار
                                            </button>

                                            <div id="adminMessageForm_{{ $report->id }}" class="modal fade" role="dialog">
                                                <div class="modal-dialog">

                                                    <!-- Modal content-->
                                                    <form action="{{ route('report.update', $report) }}" method="post"
                                                        class="modal-content">
                                                        @csrf
                                                        @method('put')
                                                        <div class="modal-header">
                                                            <h4 class="modal-title">پاسخ</h4>
                                                        </div>
                                                        <div class="modal-body text-right">

                                                            <div class="form-group">
                                                                <label>متن پاسخ ادمین :</label>
                                                                <textarea class="form-control" name="adminMessage" id="adminMessage" placeholder="پاسخ ادمین"></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer text-left">
                                                            <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
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
                                            <td colspan="10">دیتایی یافت نشد</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <div class="card my-4">
                    <h5 class="card-header">
                        شکایات و انتقادات راننده
                    </h5>
                    <div class="card-body">

                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>راننده</th>
                                        <th>صاحب بار</th>
                                        <th>بار</th>
                                        <th>نوع</th>
                                        <th>متن پیام</th>
                                        <th>پاسخ ادمین</th>
                                        <th>تاریخ</th>
                                        <th>عملیات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($reportDrivers as $key => $report)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><a href="{{ route('driver.detail', $report->driver_id) }}">{{ $report->driver->name }}
                                                    {{ $report->driver->lastName }}</a></td>
                                            <td>
                                                @if ($report->owner_id == null)
                                                    -
                                                @else
                                                    <a href="{{ route('owner.show', $report->owner_id) }}">{{ $report->owner->name }}
                                                        {{ $report->owner->lastName }}</a>
                                                @endif
                                            </td>
                                            <td><a href="{{ route('loadInfo', $report->load_id) }}">{{ $report->cargo->title }}</a>
                                            </td>
                                            <td>
                                                @switch($report->type)
                                                    @case('owner')
                                                        صاحب بار
                                                    @break

                                                    @case('driver')
                                                        راننده
                                                    @break
                                                @endswitch
                                            </td>
                                            <td>{{ $report->description }}</td>
                                            <td>{{ $report->adminMessage ?? '-' }}</td>
                                            @php
                                                $pieces = explode(' ', $report->created_at);
                                            @endphp
                                            <td>{{ gregorianDateToPersian($report->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}

                                            <td>
                                                <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3"
                                                    data-bs-toggle="modal" data-bs-target="#adminMessageForm_{{ $report->id }}">
                                                    پاسخ به صاحب بار
                                                </button>

                                                <div id="adminMessageForm_{{ $report->id }}" class="modal fade" role="dialog">
                                                    <div class="modal-dialog">

                                                        <!-- Modal content-->
                                                        <form action="{{ route('report.update', $report) }}" method="post"
                                                            class="modal-content">
                                                            @csrf
                                                            @method('put')
                                                            <div class="modal-header">
                                                                <h4 class="modal-title">پاسخ</h4>
                                                            </div>
                                                            <div class="modal-body text-right">

                                                                <div class="form-group">
                                                                    <label>متن پاسخ ادمین :</label>
                                                                    <textarea class="form-control" name="adminMessage" id="adminMessage" placeholder="پاسخ ادمین"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer text-left">
                                                                <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
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
                                                <td colspan="10">دیتایی یافت نشد</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>

                    {{-- موارد قابل پیگیری --}}
                    <div class="card my-4">
                        <h5 class="card-header">
                            موارد قابل پیگیری
                        </h5>
                        <div class="card-body">

                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>ردیف</th>
                                            <th>شماره موبایل</th>
                                            <th>کد رهگیری</th>
                                            <th>توضیحات</th>
                                            <th>وضعیت</th>
                                            <th>تاریخ</th>
                                            <th>عملیات</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php $i = 1; ?>
                                        @forelse ($tracks as $key => $track)
                                            <tr>
                                                <td>
                                                    @if (isset($track->childrenRecursive))
                                                        <a href="{{ route('trackableItems.index', ['parentId' => $track->id]) }}">{{ $loop->iteration }}</a>
                                                    @else
                                                        {{ $loop->iteration }}
                                                    @endif
                                                </td>
                                                <td>{{ $track->mobileNumber  }}({{$track->childrenRecursive->count()}})</td>
                                                <td>{{ $track->tracking_code }}</td>
                                                <td>{{ Str::limit($track->description, 30) }}</td>
                                                <td>{{ $track->status ? 'فعال' : 'بایگانی شد' }}</td>
                                                {{-- @php
                                                $pieces = explode(' ', $track->created_at);
                                            @endphp --}}
                                                <td>
                                                    {{ $track->date }}
                                                </td>

                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                                        data-bs-target="#followUp">پیگیری مجدد</button>
                                                    <div class="modal fade" id="followUp" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="followUpTitle">پیگیری مجدد :
                                                                        {{ $track->tracking_code }} </h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                        aria-label="Close"></button>
                                                                </div>

                                                                <form action="{{ route('trackableItems.store') }}" method="post">
                                                                    @csrf
                                                                    <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col-md-4 col-sm-12">
                                                                                <input class="form-control" type="text" id="new_again"
                                                                                    name="date" required placeholder="تاریخ"
                                                                                    autocomplete="off" />
                                                                            </div>
                                                                            <div class="col-md-4 col-sm-12">
                                                                                <input value="{{ now() }}" class="form-control"
                                                                                    type="time" id="time_2" name="time" required
                                                                                    placeholder="ساعت" autocomplete="off" />
                                                                            </div>
                                                                        </div>

                                                                        <input type="hidden" name="parent_id"
                                                                            value="{{ $track->id }}">
                                                                    </div>
                                                                    <div class="modal-footer">
                                                                        <button type="button" class="btn btn-outline-secondary"
                                                                            data-bs-dismiss="modal">
                                                                            بستن
                                                                        </button>
                                                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                                                    </div>
                                                                </form>

                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if ($track->status == 1)
                                                        <button data-bs-toggle="modal" data-bs-target="#submitClose"
                                                            class="btn btn-sm btn-outline-danger">بستن</button>
                                                        <div class="modal fade" id="submitClose" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="submitCloseTitle">بستن تیکت شماره
                                                                            :
                                                                            {{ $track->tracking_code }} </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>
                                                                    <form method="POST"
                                                                        action="{{ route('trackableItems.destroy', $track) }}">
                                                                        @csrf
                                                                        @method('delete')

                                                                        <div class="modal-body">
                                                                            <div class="row">
                                                                                <div class="col-12">
                                                                                    <textarea class="form-control" name="result" id="" cols="15" rows="5"
                                                                                        placeholder="متن توضیحات"></textarea>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-outline-secondary"
                                                                                data-bs-dismiss="modal">
                                                                                بستن
                                                                            </button>
                                                                            <button type="submit" class="btn btn-primary">ثبت</button>
                                                                        </div>
                                                                    </form>

                                                                </div>
                                                            </div>
                                                        </div>
                                                        @else
                                                        <button data-bs-toggle="modal" data-bs-target="#watchResult"
                                                            class="btn btn-sm btn-outline-success">مشاهده نتیجه</button>
                                                        <div class="modal fade" id="watchResult" tabindex="-1" aria-hidden="true">
                                                            <div class="modal-dialog modal-dialog-centered" role="document">
                                                                <div class="modal-content">
                                                                    <div class="modal-header">
                                                                        <h5 class="modal-title" id="watchResultTitle">نتیجه تیکت شماره
                                                                            :
                                                                            {{ $track->tracking_code }} </h5>
                                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                            aria-label="Close"></button>
                                                                    </div>


                                                                    <div class="modal-body">
                                                                        <div class="row">
                                                                            <div class="col-12">
                                                                                <textarea class="form-control" disabled name="description" id="" cols="15" rows="5"
                                                                                    placeholder="متن نتیجه">{{ $track->result }}</textarea>
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif


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
                            </div>
                        </div>

                        {{-- مسدودی ها --}}
                        <div class="card my-4">
                            <h5 class="card-header">
                                مسدودی ها
                            </h5>
                            <div class="card-body">

                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>شماره</th>
                                                <th>کد ملی</th>
                                                <th>نام و نام خانوادگی</th>
                                                <th>توضیحات</th>
                                                <th>تاریخ</th>
                                                <th>عملیات</th>
                                            </tr>
                                        </thead>
                                        <tbody class="small text-right">
                                            @forelse ($blockedPhoneNumbers as $blockedPhoneNumber)
                                                <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>
                                                        {{ $blockedPhoneNumber->phoneNumber ?? '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $blockedPhoneNumber->nationalCode ?? '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $blockedPhoneNumber->name ?? '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $blockedPhoneNumber->description ?? '-' }}
                                                    </td>

                                                    @php
                                                        $pieces = explode(' ', $blockedPhoneNumber->created_at);
                                                    @endphp
                                                    <td dir="ltr">
                                                        {{ gregorianDateToPersian($blockedPhoneNumber->created_at, '-', true) . ' ' . $pieces[1] }}
                                                    </td>
                                                    <td>
                                                        <form
                                                            action="{{ route('blockedPhoneNumber.destroy', $blockedPhoneNumber->phoneNumber) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('delete')
                                                            <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="10">فیلد مورد خالی است</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                        {{-- پیام ها --}}
                        <div class="card my-4">
                            <h5 class="card-header">
                                پیام ها
                            </h5>
                            <div class="card-body">

                                <div class="table-responsive mt-4">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>عنوان پیام</th>
                                                <th>متن پیام</th>
                                                <th>نام نام خانوادگی</th>
                                                <th>نوع ناوگان</th>
                                                <th>شماره تلفن همراه</th>
                                                <th>نوع کاربر</th>
                                                <th>تاریخ</th>
                                                <th>نتیجه</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $i = 0; ?>
                                            @forelse ($messages as $key => $message)
                                                <tr @if ($message->status == true) style="background: #f1f1f1" @endif>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td>{{ $message->title }}</td>
                                                    <td>{{ $message->message }}</td>
                                                    <td>
                                                        {{ $message->nameAndLastName }}
                                                        @if ($message->role == ROLE_DRIVER)
                                                            <a class="btn btn-primary btn-sm"
                                                                href="{{ url('admin/editDriver/') }}/{{ $message->userId }}">
                                                                پروفایل راننده
                                                            </a>
                                                        @endif
                                                    </td>
                                                    <td>{{ $message->fleetName }} </td>
                                                    <td>{{ $message->mobileNumber }}</td>
                                                    <td>
                                                        <?php
                                                        switch ($message->role) {
                                                            case 'bearing':
                                                                echo 'باربری';
                                                                break;
                                                            case 'customer':
                                                                echo 'مشتری';
                                                                break;
                                                            case 'org':
                                                                echo 'سازمان';
                                                                break;
                                                            case 'driver':
                                                                echo 'راننده';
                                                                break;
                                                        }
                                                        ?>
                                                    </td>
                                                    <td>{{ $message->messageDateAndTime }}</td>
                                                    <td>
                                                        @if ($message->status == false)
                                                            <button type="button" class="btn btn-primary btn-sm text-nowrap"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#changeMessageStatus_{{ $message->id }}">
                                                                ثبت نتیجه
                                                            </button>

                                                            <!-- Modal -->
                                                            <div id="changeMessageStatus_{{ $message->id }}" class="modal fade"
                                                                role="dialog">
                                                                <div class="modal-dialog">

                                                                    <!-- Modal content-->
                                                                    <form method="post"
                                                                        action="{{ url('admin/changeMessageStatus') }}/{{ $message->id }}"
                                                                        class="modal-content">
                                                                        @csrf
                                                                        <div class="modal-header">
                                                                            <h4 class="modal-title">ثبت نتیجه</h4>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="form-group">
                                                                                <label> نتیجه تماس با کاربر را ثبت کنید: </label>
                                                                                <textarea class="form-control" placeholder="نتیجه ..." name="result"></textarea>
                                                                            </div>
                                                                            <div class="form-check mt-2">
                                                                                <input class="form-check-input" name="notification"
                                                                                    type="checkbox" id="gridCheck">
                                                                                <label class="form-check-label" for="gridCheck">
                                                                                    ارسال اعلان
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer text-left">
                                                                            <button type="submit" class="btn btn-primary">
                                                                                ثبت نتیجه
                                                                            </button>
                                                                            <button type="button" class="btn btn-danger"
                                                                                data-bs-dismiss="modal">
                                                                                انصراف
                                                                            </button>
                                                                        </div>
                                                                    </form>

                                                                </div>
                                                            </div>
                                                        @else
                                                            {{ $message->result }}
                                                        @endif


                                                        <button type="button" class="btn btn-danger btn-sm text-nowrap"
                                                            data-bs-toggle="modal" data-bs-target="#remove_{{ $message->id }}">
                                                            حذف پیام
                                                        </button>

                                                        <!-- Modal -->
                                                        <div id="remove_{{ $message->id }}" class="modal fade" role="dialog">
                                                            <div class="modal-dialog">

                                                                <!-- Modal content-->
                                                                <div class="modal-content">
                                                                    @csrf
                                                                    <div class="modal-header">
                                                                        <h4 class="modal-title">حذف پیام</h4>
                                                                    </div>
                                                                    <div class="modal-body">
                                                                        <div class="form-group">
                                                                            آیا مایل به حذف این پیام هستید؟
                                                                        </div>
                                                                    </div>
                                                                    <div class="modal-footer text-left">
                                                                        <a href="{{ url('admin/removeMessage') }}/{{ $message->id }}"
                                                                            class="btn btn-primary">
                                                                            بله حذف شود
                                                                        </a>
                                                                        <button type="button" class="btn btn-danger"
                                                                            data-bs-dismiss="modal">
                                                                            انصراف
                                                                        </button>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="10">فیلد مورد خالی است</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>

                        {{-- تماس با صاحب بار و باربری --}}
                        <div class="card my-4">
                            <h5 class="card-header">
                                تماس با صاحب بار و باربری
                            </h5>
                            <div class="card-body">

                                <div class="table-responsive mt-4">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>شماره تماس</th>
                                                <th>نام و نام خانوادگی</th>
                                                <th>تاریخ اولین تماس</th>
                                                <th>تاریخ آخرین تماس</th>
                                                <th>آخرین نتیجه ثبت شده</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($contactReportWithCargoOwners as $key => $contactReportWithCargoOwner)
                                                <tr>
                                                    <td>
                                                        {{ $loop->iteration }}
                                                    </td>
                                                    <td>{{ $contactReportWithCargoOwner->mobileNumber }}</td>
                                                    <td>
                                                        {{ $contactReportWithCargoOwner->nameAndLastName !== '' ? $contactReportWithCargoOwner->nameAndLastName : '-' }}
                                                        <span
                                                            class="text-primary small mr-1">{{ $contactReportWithCargoOwner->registerStatus }}</span>
                                                    </td>
                                                    <td>
                                                        {{ $contactReportWithCargoOwner->firstCal !== '' ? $contactReportWithCargoOwner->firstCal : '-' }}
                                                    </td>
                                                    <td>
                                                        {{ $contactReportWithCargoOwner->lastCal !== '' ? $contactReportWithCargoOwner->lastCal : '-' }}
                                                    </td>
                                                    <td>
                                                        @if (isset($contactReportWithCargoOwner->results[0]))
                                                            <div class="alert alert-info">
                                                                {{ $contactReportWithCargoOwner->results[0]->result }}
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <a class="btn btn-primary btn-sm small m-1 p-2"
                                                            href="{{ url('admin/contactReportWithCargoOwners') }}/{{ $contactReportWithCargoOwner->mobileNumber }}">تاریخچه
                                                            تماس ها</a>

                                                        @if (auth()->user()->role == ROLE_ADMIN)
                                                            <a class="btn btn-danger btn-sm small m-1 p-2"
                                                                href="{{ url('admin/deleteContactReportWithCargoOwners') }}/{{ $contactReportWithCargoOwner->id }}">
                                                                حذف
                                                            </a>
                                                        @endif

                                                    </td>
                                                </tr>
                                            @empty
                                                <tr class="text-center">
                                                    <td colspan="10">فیلد مورد خالی است</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>
                    @endsection
