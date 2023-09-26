@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h5 class="mb-0">خدمات</h5>
            <small class="text-muted float-end"><button class="btn btn-primary" data-bs-toggle="modal"
                data-bs-target="#addService"> + افزودن خدمت
        </button></small>
          </div>
        <div class="card-body">

            <div id="addService" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <form method="post" enctype="multipart/form-data" action="{{ url('admin/services') }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">خدمت جدید</h4>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label>عنوان خدمت :</label>
                                <input type="text" class="form-control" name="title" id="title"
                                       placeholder="عنوان خدمت">
                            </div>
                            <div class="form-group">
                                <label>لینک :</label>
                                <input type="text" class="form-control" name="link" id="link"
                                       placeholder="لینک">
                            </div>
                            <div class="form-group">
                                <label>آیکن :</label>
                                <input type="file" class="form-control" name="icon" id="icon">
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary">
                                ثبت خدمت جدید
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>


            <table class="table">
                <thead>
                <tr>
                    {{-- <th>#</th> --}}
                    <th>تصویر</th>
                    <th>عنوان خدمت</th>
                    <th>لینک</th>
                    <th>عملیات</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($services as $service)
                    <tr>
                        {{-- <td>{{ $loop->iteration }}</td> --}}
                        <td><img src="{{ url($service->icon) }}" width="64"></td>
                        <td>{{ $service->title }}</td>
                        <td>{{ $service->link }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                    data-bs-target="#removeService_{{ $service->id }}">حذف
                            </button>
                            <div id="removeService_{{ $service->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <form action="{{ url('admin/services') }}/{{ $service->id }}" method="POST"
                                          class="modal-content">
                                        @method('DELETE')
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">حذف خدمت</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>آیا مایل به حذف
                                                <span class="text-primary"> {{ $service->title }}
                                                            </span>
                                                هستید؟
                                            </p>
                                        </div>
                                        <div class="modal-footer text-left">
                                            <button class="btn btn-primary" type="submit">حذف
                                                خدمت
                                            </button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                انصراف
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                    data-bs-target="#editService_{{ $service->id }}"> ویرایش
                            </button>
                            <div id="editService_{{ $service->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">
                                    <form method="post" enctype="multipart/form-data"
                                          action="{{ url('admin/services') }}/{{ $service->id }}"
                                          class="modal-content">
                                        @method('PATCH')
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">ویرایش اطلاعات خدمت</h4>
                                        </div>
                                        <div class="modal-body">
                                            <div class="form-group">
                                                <label>عنوان خدمت :</label>
                                                <input type="text" class="form-control" name="title" id="title"
                                                       value="{{ $service->title }}" placeholder="عنوان خدمت">
                                            </div>
                                            <div class="form-group">
                                                <label>لینک :</label>
                                                <input type="text" class="form-control" name="link" id="link"
                                                       value="{{ $service->link }}" placeholder="لینک">
                                            </div>
                                            <div class="form-group">
                                                <label>آیکن :</label>
                                                <img src="{{ url($service->icon) }}" width="64"
                                                     class="img-rounded m-2"/>
                                                <input type="file" class="form-control" name="icon" id="icon">
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

                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{ $services }}
        </div>
    </div>

@endsection
