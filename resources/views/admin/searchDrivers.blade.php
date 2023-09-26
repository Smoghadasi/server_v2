@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            رانندگان
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    @if(isset($message))
        <div class="alert alert-primary">{{ $message }}</div>
    @endif
    <div class="container">
        <div class="col-md-12">
            <div class="text-right">

                <form action="{{ url('admin/searchDrivers') }}" method="post">
                    @csrf
                    <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                        <h6>جستجوی رانندگان : </h6>
                        <div class="form-group col-lg-3">
                            <label>نام :</label>
                            <input type="text" placeholder="نام" name="name" class="form-control">
                        </div>
                        <div class="form-group col-lg-3">
                            <label>نام خانوادگی :</label>
                            <input type="text" placeholder="نام خانوادگی" name="lastName" class="form-control">
                        </div>
                        <div class="form-group col-lg-3">
                            <label>شماره تلفن :</label>
                            <input type="text" placeholder="شماره تلفن" name="mobileNumber" class="form-control">
                        </div>
                        <div class="form-group col-lg-3">
                            <button class="btn btn-primary mt-4" type="submit">جستجوی راننده</button>
                        </div>
                    </div>
                </form>

                <div class="col-lg-12 alert alert-info">
                    تعداد یافته ها :
                    {{ count($drivers) }}
                    راننده
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>کد ملی</th>
                            <th>تاریخ تولد</th>
                            <th>شماره تلفن همراه</th>
                            <th>نمایش جزئیات</th>
                            <th>وضعیت</th>
                            <th>تغییر وضعیت</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($drivers as $driver)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td>
                                    {{ $driver->name }} {{ $driver->lastName }}
                                    [<a href="{{ url('admin/editDriver') }}/{{ $driver->id }}">ویرایش</a>]
                                </td>
                                <td>{{ $driver->nationalCode }}</td>
                                <td>{{ $driver->birthDate }}</td>
                                <td>{{ $driver->mobileNumber }}</td>
                                <td><a href="{{ url('admin/driverInfo') }}/{{ $driver->id }}">نمایش جزئیات</a></td>
                                <td>
                                    @if($driver->status==0)
                                        <div class="alert alert-warning">غیرفعال</div>
                                    @else
                                        <div class="alert alert-success">فعال</div>
                                    @endif
                                </td>
                                <td>
                                    @if($driver->status==0)
                                        <a class="btn btn-primary"
                                           href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">تغییر به
                                            فعال</a>
                                    @else
                                        <a class="btn btn-danger"
                                           href="{{ url('admin/changeDriverStatus') }}/{{ $driver->id }}">تغییر به غیر
                                            فعال</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
