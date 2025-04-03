@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شهرهای استان
            {{ $provinceCity->name }}
        </h5>
        <div class="card-body">

            <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addNewCity">
                + افزودن شهر جدید
            </button>

            <div id="addNewCity" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form action="{{ route('provinceCity.store') }}" method="post" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">افزودن شهر جدید</h4>
                        </div>
                        <div class="modal-body text-right">
                            <div class="form-group">
                                <label>نام شهر :</label>
                                <input name="name" type="text" class="form-control" required placeholder="نام شهر">
                                <input type="hidden" value="{{ $provinceCity->id }}" name="parent_id">
                            </div>
                            <div class="form-group">
                                <label>طول جغرافیایی (longitude) :</label>
                                <input name="longitude" type="text" class="form-control" required
                                    placeholder="طول جغرافیایی (longitude)">
                            </div>
                            <div class="form-group">
                                <label>عرض جغرافیایی (latitude) :</label>
                                <input name="latitude" type="text" class="form-control" required
                                    placeholder="عرض جغرافیایی (latitude)">
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary mr-1">ثبت شهر جدید</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام شهر</th>
                        <th>طول و عرض جغرافیایی</th>
                        <th>عملیات</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($provinces as $key => $city)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                {{ $city->name }}
                                @if ($city->centerOfProvince == 1)
                                    <span class="alert alert-primary small d-inline-block p-1">مرکز استان</span>
                                @endif
                            </td>
                            <td>{{ $city->latitude }} , {{ $city->longitude }}</td>
                            <td>
                                @if (auth()->user()->role == ROLE_ADMIN)
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#removeCity_{{ $city->id }}">حذف
                                    </button>

                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#editCity_{{ $city->id }}">ویرایش
                                    </button>
                                    <div id="editCity_{{ $city->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">
                                            <form method="post" action="{{ route('provinceCity.update', $city->id) }}"
                                                class="modal-content">
                                                @method('PUT')
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">ویرایش شهر</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label>عنوان خدمت :</label>
                                                        <input type="text" class="form-control" name="name"
                                                            id="title" value="{{ $city->name }}"
                                                            placeholder="عنوان خدمت">
                                                    </div>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <button type="submit" class="btn btn-primary">
                                                        ویرایش
                                                    </button>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>

                                    <div id="removeCity_{{ $city->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف شهر</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>آیا مایل به حذف شهر
                                                        <span class="text-primary"> {{ $city->name }}</span>
                                                        هستید؟
                                                    </p>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <form action="{{ route('provinceCity.destroy', $city->id) }}" method="post">
                                                        @method('DELETE')
                                                        @csrf
                                                        <button class="btn btn-primary" type="submit">حذف</button>
                                                    </form>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    @if ($city->centerOfProvince == 0)
                                        <a class="btn btn-sm btn-info"
                                            href="{{ route('centerOfProvinceCities', ['provinceCity' => $city]) }}">انتخاب
                                            به عنوان مرکز استان</a>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

@stop
