@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش بار ها به تفکیک ناوگان
        </h5>
        <div class="card-body">

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
