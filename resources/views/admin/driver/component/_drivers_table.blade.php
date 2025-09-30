<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>نام و نام خانوادگی</th>
                <th>وضعیت احراز هویت</th>
                <th>کد ملی</th>
                <th>نوع ناوگان</th>
                <th>کد نسخه</th>
                <th>شماره تلفن همراه</th>
                <th>تاریخ ثبت نام</th>
                <th>تاریخ واریزی</th>
                <th class="text-center">عملیات</th>
            </tr>
        </thead>
        <tbody class="small">
            @foreach ($drivers as $driver)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <form style="display: contents" action="{{ route('bookmark.store') }}" method="post">
                            @csrf
                            <input type="hidden" value="{{ $driver->id }}" name="user_id">
                            <input type="hidden" value="driver" name="type">
                            <button class="btn btn-link" type="submit">
                                @if ($driver->bookmark)
                                    <i class='bx bxs-bookmark-star'></i>
                                @else
                                    <i class='bx bx-bookmark'></i>
                                @endif
                            </button>
                        </form>

                        {{ $driver->name }} {{ $driver->lastName }}

                        @if ($driver->status == 0)
                            <span class="alert alert-danger p-1">غیرفعال</span>
                        @else
                            <span class="alert alert-success p-1">فعال</span>
                        @endif
                    </td>

                    {{-- احراز هویت --}}
                    <td>
                        @switch($driver->authLevel)
                            @case(DRIVER_AUTH_UN_AUTH)
                                <span class="badge bg-label-danger">انجام نشده</span>
                                @break
                            @case(DRIVER_AUTH_SILVER_PENDING)
                                <span class="badge bg-label-secondary border border-danger">
                                    <span class="badge bg-label-secondary">سطح نقره ای :</span> در حال بررسی
                                </span>
                                @break
                            @case(DRIVER_AUTH_SILVER)
                                <span class="badge bg-label-secondary">سطح نقره ای</span>
                                @break
                            @case(DRIVER_AUTH_GOLD_PENDING)
                                <span class="badge bg-label-warning border border-danger">
                                    <span class="badge bg-label-warning">سطح طلایی :</span> در حال بررسی
                                </span>
                                @break
                            @case(DRIVER_AUTH_GOLD)
                                <span class="badge bg-label-warning">سطح طلایی</span>
                                @break
                        @endswitch
                    </td>

                    <td>{{ $driver->nationalCode }}</td>
                    <td>{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id) }}</td>
                    <td>{{ $driver->version ?? '-' }}</td>
                    <td>{{ $driver->mobileNumber }}</td>

                    <td>
                        {{ gregorianDateToPersian($driver->created_at, '-', true) }}
                        @if (isset(explode(' ', $driver->created_at)[1]))
                            {{ explode(' ', $driver->created_at)[1] }}
                        @endif
                    </td>
                    <td>
                        @if ($driver->transactions->last())
                            {{ gregorianDateToPersian($driver->transactions->last()->created_at, '-', true) }}
                            @if (isset(explode(' ', $driver->transactions->last()->created_at)[1]))
                                {{ explode(' ', $driver->transactions->last()->created_at)[1] }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        <a class="btn btn-primary" href="{{ url('admin/driverInfo', $driver->id) }}">جزئیات</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
