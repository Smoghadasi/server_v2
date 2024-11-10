@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('services.index') }}">خدمات</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('bank.index') }}">درگاه پرداخت</a>
                </li>
            </ul>
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">درگاه پرداخت</h5>
                    <small class="text-muted float-end"><button class="btn btn-primary" data-bs-toggle="modal"
                            data-bs-target="#addBank"> + افزودن درگاه پرداخت
                        </button></small>
                </div>
                <div class="card-body">

                    <div id="addBank" class="modal fade" role="dialog">
                        <div class="modal-dialog">
                            <form method="post" enctype="multipart/form-data" action="{{ route('bank.store') }}"
                                class="modal-content">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-name">درگاه پرداخت جدید</h4>
                                </div>
                                <div class="modal-body">
                                    <div class="form-group">
                                        <lable class="form-label">عنوان درگاه پرداخت :</label>
                                            <input type="text" class="form-control" name="name" id="name"
                                                placeholder="عنوان درگاه پرداخت">
                                    </div>
                                    <div class="form-group">
                                        <lable class="form-label">عنوان به انگلیسی</label>
                                            <input type="text" class="form-control" name="en_name" id="en_name"
                                                placeholder="عنوان به انگلیسی">
                                    </div>
                                    <div class="form-group">
                                        <lable class="form-label">وضعیت :</label>
                                            <select class="form-control" name="status">
                                                <option value="1">فعال</option>
                                                <option value="0">غیر فعال</option>
                                            </select>
                                    </div>
                                    <div class="form-group">
                                        <lable class="form-label">آیکن :</label>
                                            <input type="file" class="form-control" name="icon" id="icon">
                                    </div>
                                </div>
                                <div class="modal-footer text-left">
                                    <button type="submit" class="btn btn-primary">
                                        ثبت درگاه پرداخت جدید
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
                                <th>عنوان درگاه پرداخت</th>
                                <th>وضعیت</th>
                                <th>عملیات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($banks as $bank)
                                <tr>
                                    {{-- <td>{{ $loop->iteration }}</td> --}}
                                    <td><img src="{{ url($bank->icon) }}" width="64"></td>
                                    <td>{{ $bank->name }}</td>
                                    <td>{{ $bank->status ? 'فعال' : 'غیر فعال' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                            data-bs-target="#removeService_{{ $bank->id }}">حذف
                                        </button>
                                        <div id="removebank_{{ $bank->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <form action="{{ url('admin/banks') }}/{{ $bank->id }}" method="POST"
                                                    class="modal-content">
                                                    @method('DELETE')
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h4 class="modal-name">حذف درگاه پرداخت</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>آیا مایل به حذف
                                                            <span class="text-primary"> {{ $bank->name }}
                                                            </span>
                                                            هستید؟
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <button class="btn btn-primary" type="submit">حذف
                                                            درگاه پرداخت
                                                        </button>
                                                        <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
                                                            انصراف
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <button class="btn btn-sm btn-success" data-bs-toggle="modal"
                                            data-bs-target="#editbank_{{ $bank->id }}"> ویرایش
                                        </button>
                                        <div id="editbank_{{ $bank->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">
                                                <form method="post" enctype="multipart/form-data"
                                                    action="{{ route('bank.update', $bank) }}" class="modal-content">
                                                    @method('PATCH')
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h4 class="modal-name">ویرایش اطلاعات درگاه پرداخت</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <lable class="form-label">عنوان درگاه پرداخت :</label>
                                                                <input type="text" class="form-control" name="name"
                                                                    id="name" value="{{ $bank->name }}"
                                                                    placeholder="عنوان درگاه پرداخت">
                                                        </div>
                                                        <div class="form-group">
                                                            <lable class="form-label">عنوان به انگلیسی</label>
                                                                <input type="text" class="form-control"
                                                                    value="{{ $bank->en_name }}" name="en_name"
                                                                    id="en_name" placeholder="عنوان به انگلیسی">
                                                        </div>
                                                        <div class="form-group">
                                                            <lable class="form-label">وضعیت :</label>
                                                                <select class="form-control" name="status">
                                                                    <option
                                                                        @if ($bank->status == 1) selected @endif
                                                                        value="1">فعال</option>
                                                                    <option
                                                                        @if ($bank->status == 0) selected @endif
                                                                        value="0">غیر فعال</option>
                                                                </select>
                                                        </div>
                                                        <div class="form-group">
                                                            <label>آیکن :</label>
                                                            <img src="{{ url($bank->icon) }}" width="64"
                                                                class="img-rounded m-2" />
                                                            <input type="file" class="form-control" name="icon"
                                                                id="icon">
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <button type="submit" class="btn btn-primary">
                                                            ویرایش
                                                        </button>
                                                        <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
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
                </div>
            </div>
        </div>
    </div>
@endsection
