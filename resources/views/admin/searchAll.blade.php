@extends('layouts.dashboard')

@section('content')
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
                                <td>
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">جزئیات</a>
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
                            <th class="text-center">تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($owners as $owner)
                            <tr>
                                <td>{{ ($owners->currentPage() - 1) * $owners->perPage() + ++$i }}</td>
                                <td>
                                    {{ $owner->name }} {{ $owner->lastName }}
                                    @if ($owner->status == 1)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیر فعال</span>
                                    @endif
                                    @if ($owner->moreDayLoad >= 3)
                                        <span class="badge bg-primary">3+</span>
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
                                <td>{{ $owner->mobileNumber }}</td>
                                <td>
                                    <a href="{{ route('owner.loads', $owner->id) }}">{{ $owner->numOfLoads }}</a>
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
                    <div class="mt-2">
                        {{ $owners }}
                    </div>
                </div>
            </div>
        </div>
@endsection
