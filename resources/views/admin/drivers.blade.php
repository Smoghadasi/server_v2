@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            رانندگان
        </h5>
        <div class="card-body">

            @if (auth()->user()->role == 'admin')
                <div class="alert alert-info text-right">
                    @if (isDriverAutoActive())
                        تایید رانندگان بصورت خودکار
                        <a class="btn btn-danger" href="{{ url('admin/changeSiteOption/driverAutoActive') }}">
                            تغییر به غیر خودکار
                        </a>
                    @else
                        تایید رانندگان بصورت غیر خودکار
                        <a class="btn btn-primary" href="{{ url('admin/changeSiteOption/driverAutoActive') }}">
                            تغییر به خودکار
                        </a>
                    @endif
                </div>
            @endif

            <div class="col-md-12 mb-3">
                <a class="btn btn-primary" href="{{ url('admin/addNewDriverForm') }}"> + افزودن راننده</a>
            </div>

            <form action="{{ url('admin/searchDrivers') }}" method="post">
                @csrf
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    <h6>جستجوی رانندگان : </h6>
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>نام :</label>
                                    <input type="text" name="name" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>نام خانوادگی :</label>
                                    <input type="text" name="lastName" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>شماره تلفن :</label>
                                    <input type="text" name="mobileNumber" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>کد نسخه :</label>
                                    <input type="text" name="version" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>
                </div>
            </form>

            @if (isset($showSearchResult) && $showSearchResult)
                <div class="col-lg-12 alert alert-info">
                    تعداد یافته ها :
                    {{ count($drivers) }}
                    راننده
                </div>
            @endif

        </div>
    </div>


@stop
