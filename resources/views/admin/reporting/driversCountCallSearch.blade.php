@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    گزارش تماس راننده
                </div>
            </div>
        </h5>
        <div class="card-body">
            <form class="my-4" method="get" action="{{ route('report.driversCountCallSearch') }}">
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <label>شماره موبایل</label>
                        <input class="form-control" name="mobileNumber" id="mobileNumber">
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <label>ناوگان</label>
                        <select class="form-select" name="fleet_id">
                            <option disabled selected>انتخاب ناوگان</option>
                            @foreach ($fleets as $fleet)
                                <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <label>از تاریخ : </label>
                        <input class="form-control" type="text" id="fromDate" name="fromDate">
                    </div>

                    <div class="col-md-3 col-sm-12">
                        <label>تا تاریخ : </label>
                        <input class="form-control" type="text" id="toDate" name="toDate">
                    </div>

                    <div class="col-md-4 col-sm-12">
                        <button type="submit" class="btn btn-primary my-2">جستجو</button>
                    </div>
                </div>

            </form>
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاریخ</th>
                            <th>تعداد تماس</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($driverCalls as $key => $driversActivitiesCallDate)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    {{ str_replace('-', '/', gregorianDateToPersian($driversActivitiesCallDate->created_at, '-', true)) }}
                                </td>
                                <td>
                                    <a href="{{ route('report.loadDriversCountCall', [
                                        'callingDate' => $driversActivitiesCallDate->callingDate,
                                        'driverId' => $driversActivitiesCallDate->driver_id,
                                    ]) }}">{{ $driversActivitiesCallDate->totalCalls }}</a>


                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#toDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
        $("#fromDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
    </script>
@endsection
