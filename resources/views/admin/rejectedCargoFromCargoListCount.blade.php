@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">بارهای رد شده تا به امروز</h5>
        <div class="table-responsive">
            <table class="table">
                <tr>
                    <th>#</th>
                    <th>اپراتور</th>
                    <th>تعداد</th>
                </tr>
                @foreach ($groupBys as $groupBy => $key)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>
                            سرکار خانم / آقا {{ $groupBy }}
                        </td>
                        <td>{{ $key->count() }}</td>

                    </tr>
                @endforeach
            </table>
{{--            <div class="mt-2">--}}
{{--            {{ $cargoList }}--}}
{{--            </div>--}}
        </div>
    </div>


@stop

