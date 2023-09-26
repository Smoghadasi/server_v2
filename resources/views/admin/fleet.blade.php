@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            ناوگان
        </h5>
        <div class="card-body row">

            <p class="text-right"><a class="btn btn-primary" href="{{ url('admin/addNewFleetForm') }}"> + افزودن ناوگان</a>
            </p>
            <div class="col-md-5">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>تصویر</th>
                        <th>دسته بندی ناوگان</th>
                        <th>ویرایش</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach($fleetsParents as $fleet)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>
                                <img src="{{ url($fleet->pic) }}" width="64" height="64" class="img-thumbnail">
                            </td>
                            <td>{{ $fleet->title }}</td>
                            <td>
                                <a class="btn btn-primary" href="{{ url('admin/editFleetForm') }}/{{ $fleet->id }}">ویرایش</a>
                                @if(auth()->user()->role == 'admin')
                                    <a class="btn btn-danger"
                                       href="{{ url('admin/deleteFleet') }}/{{ $fleet->id }}">حذف</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="col-md-7">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>تصویر</th>
                        <th>عنوان</th>
                        <th>ناوگان</th>
                        <th>اندازه</th>
                        <th>ظرفیت</th>
                        <th>عملیات</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; ?>
                    @foreach($fleets as $fleet)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td><img src="{{ url($fleet->pic) }}" width="64" height="64" class="img-thumbnail"></td>
                            <td>{{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}</td>
                            <td>{{ $fleet->title }}</td>
                            <td>{{ $fleet->length * $fleet->width * $fleet->height }}</td>
                            <td>{{ $fleet->capacity }}</td>
                            <td>
                                <a class="btn btn-primary" href="{{ url('admin/editFleetForm') }}/{{ $fleet->id }}">ویرایش</a>
                                @if(auth()->user()->role==ROLE_ADMIN)
                                    <a class="btn btn-danger"
                                       href="{{ url('admin/deleteFleet') }}/{{ $fleet->id }}">حذف</a>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>

@stop
