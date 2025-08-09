@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
           <div class="row">
            <div class="col-6">
                رانندگان ورژن: {{ $version }}
            </div>
            <div class="col-6 text-end">
                تعداد کل: {{ $driverActivities->total() }}
            </div>
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
                            <th>تاریخ ثبت نام</th>
                            <th>کد نسخه</th>
                            <th>شماره تلفن همراه</th>
                            <th class="text-center">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>

                        @foreach ($driverActivities as $driverActivity)
                            <tr>
                                <td>{{ ($driverActivities->currentPage() - 1) * $driverActivities->perPage() + ++$i }}</td>
                                <td>
                                    @if ($driverActivity->driver->bookmark)
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $driverActivity->driver->id }}" name="user_id">
                                            <input type="hidden" value="driver" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bxs-bookmark-star'></i>
                                            </button>
                                        </form>
                                    @else
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $driverActivity->driver->id }}" name="user_id">
                                            <input type="hidden" value="driver" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bx-bookmark'></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{ $driverActivity->driver->name }} {{ $driverActivity->driver->lastName }}

                                    @if ($driverActivity->driver->status == 0)
                                        <span class="alert alert-danger p-1">غیرفعال</span>
                                    @else
                                        <span class="alert alert-success p-1">فعال</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($driverActivity->driver->authLevel == DRIVER_AUTH_UN_AUTH)
                                        <span class="badge bg-label-danger"> انجام نشده</span>
                                    @elseif ($driverActivity->driver->authLevel == DRIVER_AUTH_SILVER_PENDING)
                                        <span class="badge bg-label-secondary border border-danger"><span
                                                class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                                    @elseif ($driverActivity->driver->authLevel == DRIVER_AUTH_SILVER)
                                        <span class="badge bg-label-secondary">سطح نقره ای</span>
                                    @elseif ($driverActivity->driver->authLevel == DRIVER_AUTH_GOLD_PENDING)
                                        <span class="badge bg-label-warning border border-danger"><span
                                                class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                                    @elseif ($driverActivity->driver->authLevel == DRIVER_AUTH_GOLD)
                                        <span class="badge bg-label-warning">سطح طلایی</span>
                                    @endif
                                </td>
                                <td>{{ $driverActivity->driver->nationalCode }}</td>
                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($driverActivity->driver->fleet_id) }}</td>
                                <td>
                                    {{ gregorianDateToPersian($driverActivity->driver->created_at, '-', true) }}
                                    @if (isset(explode(' ', $driverActivity->driver->created_at)[1]))
                                        {{ explode(' ', $driverActivity->driver->created_at)[1] }}
                                    @endif
                                </td>
                                <td>{{ $driverActivity->driver->version ?? '-' }}</td>
                                <td>{{ $driverActivity->driver->mobileNumber }}</td>
                                <td>
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/driverInfo') }}/{{ $driverActivity->driver->id }}">جزئیات</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{-- @if (!isset($showSearchResult) || !$showSearchResult) --}}
                {{ $driverActivities }}
            {{-- @endif --}}

        </div>
    </div>


@stop
