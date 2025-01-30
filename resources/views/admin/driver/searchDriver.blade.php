@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            لیست تماس
        </h5>
        <div class="card-body">

            <div class="col-md-12 mb-3">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDriverCall">افزودن راننده +</button>
                <div id="addDriverCall" class="modal fade" role="dialog">
                    <div class="modal-dialog modal-dialog-centered">

                        <!-- Modal content-->
                        <form action="{{ route('load.driverCall.store') }}" method="post" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h4 class="modal-title">پاسخ</h4>
                            </div>
                            <div class="modal-body text-right">
                                <div class="row">
                                    <div class="form-group col-12">
                                        <label>شماره موبایل راننده</label>
                                        <input class="form-control" name="mobileNumber" type="text">
                                    </div>
                                    {{-- <input type="hidden" value="{{ $load_id }}" name="load_id"> --}}
                                    {{-- <div class="form-group col-6">
                                        <label>شماره موبایل راننده</label>
                                        <input class="form-control" name="mobileNumber" type="text">
                                    </div> --}}
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
                </div>
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


@stop
