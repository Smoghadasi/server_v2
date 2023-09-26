@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            نوع بسته بندی
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    @if(strlen($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif
    <div class="container">
        <p class="text-right"><a class="btn btn-primary" href="{{ url('admin/addNewPackingTypeForm') }}"> + افزودن نوع
                بسته بندی</a>
        </p>
        <div class="col-md-7">
            <div class="text-right">
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>تصویر</th>
                            <th>عنوان نوع بسته بندی</th>
                            <th>ویرایش - حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach($packingTypes as $packingType)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td><img class="img-thumbnail" width="68" height="68"
                                         src="{{ url($packingType->pic) }}"></td>
                                <td>{{ $packingType->title }}</td>
                                <td>
                                    <a class="btn btn-primary"
                                       href="{{ url('admin/editPackingTypeForm') }}/{{ $packingType->id }}">ویرایش</a>
                                    <a class="btn btn-danger"
                                       href="{{ url('admin/deletePackingType') }}/{{ $packingType->id }}">حذف</a>
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