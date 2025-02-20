@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش رانندگان بر اساس بیشترین تماس (امروز)
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
                            <th>راننده</th>
                            <th>ناوگان</th>
                            <th>تلفن</th>
                            <th>تعداد</th>
                            <th>تعداد کل</th>
                            {{--                        <th>تاریخ تماس</th> --}}
                            {{--                        <th>تاریخ ثبت نام</th> --}}
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($basedCalls as $basedCall)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $basedCall->driver->name . ' ' . $basedCall->driver->lastName ?? '-' }}</td>
                                <td>{{ $basedCall->driver->fleetTitle ?? '-' }}</td>
                                <td>{{ $basedCall->driver->mobileNumber ?? '-' }}</td>
                                <td>{{ $basedCall->countOfCalls }}</td>
                                <td>{{ $basedCall->totalCalls }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $basedCalls }}
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#toDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });
        $("#fromDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });
    </script>
@endsection
