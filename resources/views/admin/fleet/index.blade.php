@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            ناوگان
        </h5>
        <div class="card-body row">

            <p class="text-right"><a class="btn btn-primary" href="{{ route('fleet.create') }}"> + افزودن ناوگان</a>
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
                        @foreach ($fleetsParents as $fleet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    <img src="{{ url($fleet->pic) }}" width="64" height="64" class="img-thumbnail">
                                </td>
                                <td>{{ $fleet->title }}</td>
                                <td>
                                    <a class="btn btn-primary" href="{{ route('fleet.edit', $fleet->id) }}">ویرایش</a>
                                    @if (auth()->user()->role == 'admin')
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#removeOperator_{{ $fleet->id }}">حذف
                                        </button>

                                        <!-- Modal -->
                                        <div id="removeOperator_{{ $fleet->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">حذف ناوگان</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>آیا مایل به حذف ناوگان
                                                            <span class="text-primary"> {{ $fleet->title }}
                                                            </span>
                                                            هستید؟
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <a class="btn btn-primary"
                                                            href="{{ route('fleet.destroy', $fleet->id) }}">حذف
                                                            ناوگان</a>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                                                            انصراف
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
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
                            <th>عنوان گروه اصلی</th>
                            <th>ناوگان</th>
                            <th>اندازه</th>
                            <th>ظرفیت (وزن)</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fleets as $fleet)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td><img src="{{ url($fleet->pic) }}" width="64" height="64" class="img-thumbnail">
                                </td>
                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}</td>
                                <td>{{ $fleet->title }}</td>
                                <td>{{ $fleet->length * $fleet->width * $fleet->height }}</td>
                                <td>{{ $fleet->capacity }}</td>
                                <td>
                                    <a class="btn btn-primary" href="{{ route('fleet.edit', $fleet->id) }}">ویرایش</a>
                                    @if (auth()->user()->role == 'admin')
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#removeOperator_{{ $fleet->id }}">حذف
                                        </button>

                                        <!-- Modal -->
                                        <div id="removeOperator_{{ $fleet->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">حذف ناوگان</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>آیا مایل به حذف ناوگان
                                                            <span class="text-primary"> {{ $fleet->title }}
                                                            </span>
                                                            هستید؟
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <a class="btn btn-primary"
                                                            href="{{ route('fleet.destroy', $fleet->id) }}">حذف
                                                            ناوگان</a>
                                                        <button type="button" class="btn btn-danger" data-dismiss="modal">
                                                            انصراف
                                                        </button>
                                                    </div>
                                                </div>

                                            </div>
                                        </div>
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
