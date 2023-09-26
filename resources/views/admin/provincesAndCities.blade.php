@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            استان ها و شهرها
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام استان</th>
                    <th>عملیات</th>
                </tr>
                </thead>

                <tbody>
                @foreach($provinces as $key => $province)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $province->name }}</td>
                        <td>
                            <a class="btn btn-primary" href="{{ url('admin/provinceCitiesList') }}/{{ $province->id }}">لیست شهرها</a>

                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

@stop
