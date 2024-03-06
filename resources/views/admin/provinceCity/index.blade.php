@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    استان ها و شهرها
                </div>
                <div class="col" style="text-align: left;">
                    <a href="{{ route('equivalents') }}" class="btn btn-danger btn-sm">کلمات معادل شهر</a>
                </div>
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
                            <a class="btn btn-primary" href="{{ route('provinceCity.show', $province->id) }}">لیست شهرها</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>
        </div>
    </div>

@stop
