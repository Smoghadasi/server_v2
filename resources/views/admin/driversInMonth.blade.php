@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            فعالیت رانندگان غیر تکراری در یک ماه گذشته
        </h5>
        <div class="card-body">

            <form action="{{ route('report.driversInMonth') }}" method="get">
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    <h6>جستجوی رانندگان : </h6>
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>ورژن :</label>
                                    <select class="form-control form-select" style="width: 200px" name="version"
                                        id="">
                                        @foreach ($driverVersions as $driverVersion)
                                            <option @if (app('request')->input('version') == $driverVersion->version) selected @endif
                                                value="{{ $driverVersion->version }}">{{ $driverVersion->version }}</option>
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

                        @foreach ($driversInMonths as $driversInMonth)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ $driversInMonth->driver->name }} {{ $driversInMonth->driver->lastName }}

                                    @if ($driversInMonth->driver->status == 0)
                                        <span class="alert alert-danger p-1">غیرفعال</span>
                                    @else
                                        <span class="alert alert-success p-1">فعال</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($driversInMonth->driver->authLevel == DRIVER_AUTH_UN_AUTH)
                                        <span class="badge bg-label-danger"> انجام نشده</span>
                                    @elseif ($driversInMonth->driver->authLevel == DRIVER_AUTH_SILVER_PENDING)
                                        <span class="badge bg-label-secondary border border-danger"><span
                                                class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                                    @elseif ($driversInMonth->driver->authLevel == DRIVER_AUTH_SILVER)
                                        <span class="badge bg-label-secondary">سطح نقره ای</span>
                                    @elseif ($driversInMonth->driver->authLevel == DRIVER_AUTH_GOLD_PENDING)
                                        <span class="badge bg-label-warning border border-danger"><span
                                                class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                                    @elseif ($driversInMonth->driver->authLevel == DRIVER_AUTH_GOLD)
                                        <span class="badge bg-label-warning">سطح طلایی</span>
                                    @endif
                                </td>
                                <td>{{ $driversInMonth->driver->nationalCode }}</td>
                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($driversInMonth->driver->fleet_id) }}
                                </td>
                                <td>
                                    {{ gregorianDateToPersian($driversInMonth->driver->created_at, '-', true) }}
                                    @if (isset(explode(' ', $driversInMonth->driver->created_at)[1]))
                                        {{ explode(' ', $driversInMonth->driver->created_at)[1] }}
                                    @endif
                                </td>
                                <td>{{ $driversInMonth->driver->version ?? '-' }}</td>
                                <td>{{ $driversInMonth->driver->mobileNumber }}</td>
                                <td>
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/driverInfo') }}/{{ $driversInMonth->driver->id }}">جزئیات</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $driversInMonths }}
        </div>
    </div>


@stop
