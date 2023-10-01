@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">تاریخ : {{ $persian_date }}</h5>
            <small class="text-muted float-end"><a class="btn btn-primary" href="{{ route('allRejectedCargoCount') }}">تمام روز ها</a></small>
        </div>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>اپراتور</th>
                    <th>تعداد</th>
                </tr>
                @foreach ($rejects as $reject)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $reject->user->name . " " . $reject->user->lastName }}</td>
                        <td>{{ $reject->count }}</td>

                    </tr>
                @endforeach
            </table>
        </div>
    </div>


@stop

