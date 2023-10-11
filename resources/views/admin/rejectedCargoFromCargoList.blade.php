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
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>بار</th>
                    <th>اپراتور</th>
                </tr>
                @foreach ($cargoList as $cargo)
                    <tr>
                        <td>{{ $cargo->cargo }}</td>
                        <td class="text-nowrap">
                            <span class="badge bg-label-primary">
                                {{ $cargo->operator->name }} {{ $cargo->operator->lastName }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </table>
            <div class="mt-2">
                {{ $cargoList }}
            </div>
        </div>
    </div>


@stop
