@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            محاسبه شهرستان ها
        </h5>
        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>مقدار</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cityDistances as $cityDistance)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $cityDistance->fromCity ? $cityDistance->fromCity->state : '-' }} - {{ $cityDistance->fromCity ? $cityDistance->fromCity->name : '-' }}</td>
                                <td>{{ $cityDistance->toCity ? $cityDistance->toCity->state : '' }} - {{ $cityDistance->toCity ? $cityDistance->toCity->name : '-' }}</td>
                                <td>{{ $cityDistance->value }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                        data-bs-target="#editCityDistance{{ $cityDistance->id }}">ویرایش
                                    </button>
                                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                        data-bs-target="#deleteCityDistance{{ $cityDistance->id }}">حذف
                                    </button>

                                    <div id="editCityDistance{{ $cityDistance->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">آیا مایل به ویرایش هستید؟</h4>
                                                </div>
                                                <div class="">
                                                    <form action="{{ route('cityDistanceCalculate.update', ['cityDistanceCalculate' => $cityDistance]) }}" method="POST">
                                                        @csrf
                                                        @method('put')

                                                        <div class="modal-body text-right">

                                                            <div class="form-group col-lg-12">
                                                                <label for="">مقدار</label>
                                                                <input class="m-1 form-control" name="value" value="{{ $cityDistance->value }}" type="text"
                                                                    placeholder="شماره مورد نظر را وارد نمایید">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer text-left">
                                                            <button type="submit" class="btn btn-primary mr-1">ثبت</button>
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <div id="deleteCityDistance{{ $cityDistance->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">آیا مایل به حذف هستید؟</h4>
                                                </div>
                                                <div class="">
                                                    <form action="{{ route('cityDistanceCalculate.destroy', ['cityDistanceCalculate' => $cityDistance]) }}" method="POST">
                                                        @csrf
                                                        @method('delete')

                                                        <div class="modal-footer text-left">
                                                            <button type="submit" class="btn btn-primary mr-1">حذف</button>
                                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                                انصراف
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $cityDistances }}
            </div>
        </div>
    </div>

@endsection
