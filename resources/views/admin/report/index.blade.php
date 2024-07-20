@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات صاحب بار
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
                    <th>پاسخ ادمین</th>
                    <th>تاریخ</th>
                    <th>عملیات</th>
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
                        <td>{{ $report->adminMessage ?? '-' }}</td>
                        @php
                            $pieces = explode(' ', $report->created_at);
                        @endphp
                        <td>{{ gregorianDateToPersian($report->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}

                        <td>
                            <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3" data-bs-toggle="modal"
                                data-bs-target="#adminMessageForm_{{ $report->id }}">
                                پاسخ به صاحب بار
                            </button>

                            <div id="adminMessageForm_{{ $report->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <form
                                        action="{{ route('report.update', $report) }}"
                                        method="post" class="modal-content">
                                        @csrf
                                        @method('put')
                                        <div class="modal-header">
                                            <h4 class="modal-title">پاسخ</h4>
                                        </div>
                                        <div class="modal-body text-right">

                                            <div class="form-group">
                                                <label>متن پاسخ ادمین :</label>
                                                <textarea class="form-control" name="adminMessage" id="adminMessage" placeholder="پاسخ ادمین"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer text-left">
                                            <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                انصراف
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
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

