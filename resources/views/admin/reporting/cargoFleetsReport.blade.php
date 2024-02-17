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
                                    <option value="0">نوع ناوگان</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->id }}">
                                            {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                            -
                                            {{ $fleet->title }}
                                        </option>
                                    @endforeach
                                </select>
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
                            <th>تعداد</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cargoReports as $cargoReport)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cargoReport->fleet->title ?? '-' }}</td>
                                <td>{{ $cargoReport->count }}</td>
                                <td>{{ $cargoReport->date }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $cargoReports }}
            </div>
        </div>
    </div>
@endsection
