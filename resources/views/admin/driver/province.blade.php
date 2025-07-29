@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            {{ $provinceCity->name }} : {{ $drivers->total() }}
        </h5>
        <div class="card-body">
            <form action="{{ route('reporting.usersByCustomProvinces', $provinceCity) }}" method="get">
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    <h6>جستجوی رانندگان : </h6>
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>نوع ناوگان :</label>
                                    {{-- <input type="hidden" value="{{ $provinceCity->id }}" name="province_id"> --}}
                                    <select class="form-select" name="fleet_id">
                                        <option disabled selected>انتخاب ناوگان</option>
                                        @foreach ($fleets as $fleet)
                                            <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col">
                                <label>از تاریخ :</label>
                                <input class="form-control" type="text" id="fromDate" name="fromDate"
                                    placeholder="از تاریخ" autocomplete="off" />
                                <span id="span1"></span>
                            </div>
                            <div class="col">
                                <label>تا تاریخ :</label>
                                <input class="form-control" type="text" name="toDate" id="fromDate"
                                    placeholder="تا تاریخ"  autocomplete="off"/>
                                <span id="span2"></span>
                            </div>
                            {{-- <input type="hidden" name="province_id" value="{{ $provinceCity->id }}"> --}}
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
                <div class="mt-2">
                    {{ $drivers->appends($_GET)->links() }}
                </div>
            </div>

        </div>
    </div>


@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #toDate").persianDatepicker(({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        }));
        // $("#toDate, #span2").persianDatepicker();
    </script>
@endsection
