@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">{{ $user->name }} {{ $user->lastName }}</h5>
            <h5 class="mb-0 text-primary">ثبت بار دستی امروز ({{ $cargoList->total() }})</h5>
        </div>
        <div class="card-body">

            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>بار</th>
                        <th>وضعیت</th>
                        <th>تاریخ</th>
                    </tr>
                    @foreach ($cargoList as $cargo)
                        <tr>
                            <td>{{ $cargo->cargo }}</td>
                            <td>
                                @if ($cargo->operator_id > 0)
                                    @if ($cargo->status == 1 && $cargo->rejected == 0)
                                        <span class="text-success">ثبت شد</span>
                                    @endif
                                    @if ($cargo->rejected == 1 && $cargo->status == 1)
                                        <span class="text-danger">رد شد</span>
                                    @endif
                                    @if ($cargo->status == 0 && $cargo->rejected == 0)
                                        <span class="text-primary">در حال بررسی</span>
                                    @endif
                                @else
                                    در حال بررسی
                                @endif
                            </td>

                            @php
                                $pieces = explode(' ', $cargo->created_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($cargo->created_at, '-', true) . ' ' . $pieces[1] }}</td>
                        </tr>
                    @endforeach
                </table>
                <div class="mt-2">
                    {{ $cargoList }}
                </div>
            </div>
        </div>

    </div>


@stop
