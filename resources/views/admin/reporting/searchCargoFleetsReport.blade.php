@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش بار ها به تفکیک ناوگان |
            مجموعه کل ({{ $total_sum }})
        </h5>
        <div class="card-body">
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>ناوگان</th>
                            <th>تعداد</th>
                            <th>تعداد صاحبین بار</th>
                            <th>مجموع</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cargoReports as $cargoReport)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cargoReport->fleet->title ?? '-' }}</td>
                                <td>{{ $cargoReport->count }}</td>
                                <td>{{ $cargoReport->count_owner }}</td>
                                <td>{{ $cargoReport->count + $cargoReport->count_owner }}</td>
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
       $("#fromDate, #span1").persianDatepicker();
       $("#toDate, #span2").persianDatepicker();
    </script>
@endsection
