@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            رانندگان
        </h5>
        <div class="card-body">
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
                                    <label>کد نسخه :</label>
                                    <input type="text" name="version" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>نوع ناوگان :</label>
                                    <select class="form-select" name="fleet_id">
                                        <option value="0">انتخاب ناوگان</option>
                                        @foreach ($fleets as $fleet)
                                            <option @if($fleet_id == $fleet->id ) selected @endif value="{{ $fleet->id }}">{{ $fleet->title }}</option>
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

                        <tr>
                            <td>1</td>
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
                                <a class="btn btn-primary btn-sm"
                                    href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">جزئیات</a>
                                <a class="btn btn-danger btn-sm"
                                    href="{{ route('contactingWithDriverResult', $driver->id) }}">ثبت نتیجه</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>


@stop
