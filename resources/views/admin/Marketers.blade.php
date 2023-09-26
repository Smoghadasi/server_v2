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
                <p><a class="btn btn-primary" href="{{ url('admin/addNewMarketersForm') }}"> + افزودن بازاریاب</a></p>
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>تصویر</th>
                            <th>نام و نام خانوادگی</th>
                            <th>کد ملی</th>
                            <th>شماره تلفن همراه</th>
                            <th>تلفن ثابت</th>
                            <th>تلفن ضروری</th>
                            <th>نام پدر</th>
                            <th>کد بازاریاب</th>
                            <th>آدرس</th>
                            <th>آمار</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($marketers as $marketer)
                            <tr>
                                <td>{{ ++$i }}</td>
                                <td><img src="{{ url($marketer->pic) }}" class="img-thumbnail" width="64" height="64">
                                </td>
                                <td>{{ $marketer->name }} {{ $marketer->lastName }}</td>
                                <td>{{ $marketer->nationalCode }}</td>
                                <td>{{ $marketer->mobileNumber }}</td>
                                <td>{{ $marketer->phoneNumber }}</td>
                                <td>{{ $marketer->emergencyPhoneNumber }}</td>
                                <td>{{ $marketer->fatherName }}</td>
                                <td>{{ $marketer->marketerCode }}</td>
                                <td>{{ $marketer->address }}</td>
                                <td><a href="#" class="btn btn-primary">نمایش آمار</a></td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop