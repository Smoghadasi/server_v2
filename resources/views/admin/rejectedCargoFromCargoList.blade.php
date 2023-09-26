@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">بارهای رد شده</h5>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>بار</th>
                    <th>اپراتور</th>
                </tr>
                @foreach($cargoList as $cargo)
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

