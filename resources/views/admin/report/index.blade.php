@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات رانندگان
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>راننده</th>
                    <th>صاحب بار</th>
                    <th>بار</th>
                    <th>نوع</th>
                    <th>متن پیام</th>
                </tr>
                </thead>
                <tbody>
                @foreach($reports as $key => $report)
                    <tr>
                        <td>{{ (($reports->currentPage()-1) * $reports->perPage()) + ($key + 1) }}</td>
                        <td><a href="{{ route('driver.detail', $report->driver_id) }}">{{ $report->driver->name }} {{ $report->driver->lastName }}</a></td>
                        <td><a href="{{ route('owner.show', $report->owner_id) }}">{{ $report->owner->name }} {{ $report->owner->lastName }}</a></td>
                        <td><a href="{{ route('loadInfo', $report->load_id) }}">{{ $report->cargo->title }}</a></td>
                        <td>
                            @switch($report->type)
                                @case('owner')
                                    صاحب بار
                                    @break
                                @case('driver')
                                راننده
                                @break
                            @endswitch
                        </td>
                        <td>{{ $report->description }}</td>
                    </tr>
                @endforeach
                </tbody>

            </table>

            <div class="mt-3">
                {{ $reports }}
            </div>

        </div>
    </div>

@stop

