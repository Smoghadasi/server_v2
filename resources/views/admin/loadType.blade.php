@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            نوع بار
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    @if(strlen($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif
    <div class="container">
        <p class="text-right"><a class="btn btn-primary" href="{{ url('admin/addNewLoadTypeForm') }}"> + افزودن نوع بار</a>
        </p>
        <div class="col-md-5">
            <div class="text-right">
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>دسته بندی نوع بار</th>
                            <th>ویرایش</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach($loadTypeParents as $loadType)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ $loadType->title }}</td>
                                <td>
                                    <a class="btn btn-primary" href="{{ url('admin/editLoadTypeForm') }}/{{ $loadType->id }}">ویرایش</a>
                                    {{--                                    <a class="btn btn-danger" href="{{ url('admin/deleteloadType') }}/{{ $loadType->id }}">حذف</a>--}}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="text-right">
                <div class="table-responsive">
                    <table class="table table-bordered" cellspacing="0">
                        <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان گروه اصلی</th>
                            <th>نوع بار</th>
                            <th>ویرایش - حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 1; ?>
                        @foreach($loadTypes as $loadType)
                            <tr>
                                <td>{{ $i++ }}</td>
                                <td>{{ \App\Http\Controllers\loadController::getLoadTypeTitle($loadType->parent_id) }}</td>
                                <td>{{ $loadType->title }}</td>
                                <td>
                                    <a class="btn btn-primary" href="{{ url('admin/editLoadTypeForm') }}/{{ $loadType->id }}">ویرایش</a>
                                    <a class="btn btn-danger"
                                       href="{{ url('admin/deleteLoadType') }}/{{ $loadType->id }}">حذف</a>
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