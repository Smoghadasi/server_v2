@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">بار های رد شده</h5>
            @if (auth()->user()->role == ROLE_ADMIN || auth()->id() == 29)
                <small class="text-muted float-end"><a class="btn btn-primary" href="{{ route('rejectCargoCount') }}">بار
                        رد شده توسط اپراتور ها</a></small>
            @endif
        </div>
        <div class="card-body">
            <form method="get" action="{{ route('searchRejectCargo') }}">
                <div class="form-group">
                    <div class="col-md-12 row">
                        <div class="col-md-3">
                            <input type="text" placeholder="بار مورد نظر..." class="form-control col-md-4"
                                name="cargo" id="cargo" />
                        </div>
                    </div>
                    <button class="btn btn-primary m-2">جستجو</button>
                </div>
            </form>
            <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>بار</th>
                    <th>اپراتور</th>
                    <th>تاریخ</th>
                </tr>
                @foreach ($cargoList as $cargo)
                    <tr>
                        <td>{{ $cargo->cargo }}</td>
                        <td class="text-nowrap">
                            <span class="badge bg-label-primary">
                                {{ $cargo->operator->name }} {{ $cargo->operator->lastName }}
                            </span>
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
