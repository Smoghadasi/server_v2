@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش بار ها به تفکیک ناوگان
        </h5>
        <div class="card-body">

            <div class="text-right">
                <form method="post" action="{{ route('search.report.cargo.fleets') }}" class="mt-3 mb-3 card card-body">
                    <h5>جستجو :</h5>
                    @csrf


                    <div class="form-group">
                        <div class="col-md-12 row">

                            <div class="col-md-3">
                                <select class="form-control col-md-4" name="fleet_id" id="fleet_id">
                                    <option disabled selected>نوع ناوگان</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->id }}">
                                            {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                            -
                                            {{ $fleet->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" id="fromDate" name="fromDate"
                                    placeholder="از تاریخ" autocomplete="off" />
                                <span id="span1"></span>
                            </div>
                            <div class="col-md-3">
                                <input class="form-control" type="text" name="toDate" id="fromDate"
                                    placeholder="تا تاریخ"  autocomplete="off"/>
                                <span id="span2"></span>
                            </div>
                        </div>
                        <button class="btn btn-primary m-2">جستجو</button>
                    </div>
                </form>
            </div>

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ناوگان</th>
                            <th>تعداد اپراتور</th>
                            <th>تعداد صاحب بار</th>
                            <th>مجموعه</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cargoReports as $cargoReport)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <a href="{{ route('report.cargo.fleets.search', $fleet) }}">
                                        {{ $cargoReport->fleet->title ?? '-' }}
                                    </a>
                                </td>
                                <td>{{ $cargoReport->count }}</td>
                                <td>{{ $cargoReport->count_owner }}</td>
                                <td>{{ $cargoReport->count + $cargoReport->count_owner }}</td>
                                <td>{{ $cargoReport->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (!isset($fleet_id))
                <div class="mt-2 mb-2">
                    {{ $cargoReports }}
                </div>
            @endif

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
       $("#fromDate, #span1").persianDatepicker();
       $("#toDate, #span2").persianDatepicker();
    </script>
@endsection
