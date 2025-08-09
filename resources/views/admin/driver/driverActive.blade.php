@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            <div class="row">
                <div class="col-6">
                    رانندگان ورژن: {{ $version }}
                </div>
                {{-- <div class="col-6 text-end">
                    تعداد کل: {{ $drivers }}
                </div> --}}
            </div>
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
                            <th>تاریخ اعتبار اشتراک</th>
                            <th>تماس رایگان</th>
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
                                <td>{{ $driver->fleetTitle }}</td>
                                <td>
                                    {{ gregorianDateToPersian($driver->activeDate, '-', true) }}
                                </td>
                                <td>
                                    {{ $driver->freeCalls }}
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
        </div>
    </div>


@stop
