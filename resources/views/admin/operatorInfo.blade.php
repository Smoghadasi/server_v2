@extends('layouts.dashboard')

@section('content')


    <div class="container">

        <div class="text-center">
            <div class="col-sm-12">
                <img class="img-thumbnail" width="128" height="128" src="{{ url('pictures/users') }}/{{ $user->pic }}">
            </div>
            <div class="col-sm-12">
                <h3>{{ $user->name }} {{ $user->lastName }}</h3>
            </div>
        </div>
        <div class="col-md-12">
            <div class="text-right">
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>کد ملی</th>
                            <th>موبایل</th>
                            <th>ایمیل</th>
                            <th>جنسیت</th>
                            <th>وضعیت</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ $user->nationalCode }}</td>
                            <td>{{ $user->mobileNumber }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->sex == 0)
                                    خانم
                                @else
                                    آقا
                                @endif
                            </td>
                            <td>
                                @if($user->status == 0)
                                    مسدود
                                @else
                                    فعال
                                @endif
                            </td>

                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <a class="btn btn-dark" href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">تغییر وضعیت اپراتور</a>
            </div>
        </div>
    </div>
@stop