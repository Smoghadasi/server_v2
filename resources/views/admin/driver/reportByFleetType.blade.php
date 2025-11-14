@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            رانندگان
        </h5>
        <div class="card-body">

            <div class="col-lg-12 alert alert-info">
                تعداد یافته ها :
                {{ $drivers->total() }}
                راننده
            </div>

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
                                        href="{{ url('admin/driverInfo') }}/{{ $driver->driver_id }}">جزئیات</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- @if (!isset($showSearchResult) || !$showSearchResult) --}}
            {{ $drivers }}
            {{-- @endif --}}

        </div>
    </div>


@stop
