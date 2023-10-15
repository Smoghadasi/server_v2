@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            افزودن اپراتور
        </h5>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/addNewOperator') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3 row">
                    <label for="name" class="col-md-2 col-form-label text-md-right">نام</label>

                    <div class="col-md-10">
                        <input id="name" type="text"
                            class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name"
                            value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('name'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('name') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="name" class="col-md-2 col-form-label text-md-right">نام خانوادگی</label>

                    <div class="col-md-10">
                        <input id="lastName" type="text"
                            class="form-control{{ $errors->has('lastName') ? ' is-invalid' : '' }}" name="lastName"
                            value="{{ old('lastName') }}" required autofocus>

                        @if ($errors->has('lastName'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('lastName') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="email" class="col-md-2 col-form-label text-md-right">ایمیل</label>

                    <div class="col-md-10">
                        <input id="email" type="email"
                            class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                            value="{{ old('email') }}" required>

                        @if ($errors->has('email'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('email') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="nationalCode" class="col-md-2 col-form-label text-md-right">کد ملی</label>

                    <div class="col-md-10">
                        <input id="nationalCode" type="nationalCode"
                            class="form-control{{ $errors->has('nationalCode') ? ' is-invalid' : '' }}" name="nationalCode"
                            value="{{ old('nationalCode') }}" required>

                        @if ($errors->has('nationalCode'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('nationalCode') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="mobileNumber" class="col-md-2 col-form-label text-md-right">شماره موبایل</label>

                    <div class="col-md-10">
                        <input id="nationalCode" type=""
                            class="form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}" name="mobileNumber"
                            value="{{ old('mobileNumber') }}" required>

                        @if ($errors->has('mobileNumber'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('mobileNumber') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                {{-- <div class="mb-3 row">
                    <label for="sex" class="col-md-2 col-form-label text-md-right">جنسیت</label>

                    <div class="col-md-10 text-right">

                        <input type="radio" value="0" id="woman" name="sex"> خانم
                        <input type="radio" value="1" id="man" name="sex"> آقا

                        @if ($errors->has('sex'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('sex') }}</strong>
                            </span>
                        @endif
                    </div>
                </div> --}}

                <div class="mb-3 row">
                    <label for="sex" class="col-md-2 col-form-label text-md-right">نوع کاربر</label>

                    <div class="col-md-10 text-right">
                        <select class="form-select" name="role">
                            {{-- <option disabled selected>انتخاب کنید...</option> --}}
                            @foreach ($roles as $role)
                                <option @if($role->id == 2) selected @endif value="{{ $role->id }}">{{ $role->title }}</option>
                            @endforeach
                        </select>

                        @if ($errors->has('role'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('role') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="mb-3 row">
                    <label for="password" class="col-md-2 col-form-label text-md-right">رمز ورود</label>

                    <div class="col-md-10">
                        <input id="password" type="password"
                            class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                            required>

                        @if ($errors->has('password'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>


                <div class="mb-3 row">
                    <label for="password-confirm" class="col-md-2 col-form-label text-md-right">تکرار رمز ورود</label>

                    <div class="col-md-10">
                        <input id="password-confirm{{ $errors->has('password_confirmation') ? ' is-invalid' : '' }}"
                            type="password" class="form-control" name="password_confirmation" required>

                        @if ($errors->has('password_confirmation'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('password_confirmation') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>


                <div class="mb-3 row">
                    <label for="pic" class="col-md-2 col-form-label text-md-right">تصویر اپراتور</label>
                    <div class="col-md-10">
                        <input class="form-control" id="pic" type="file" name="pic">
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-10 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            ثبت اپراتور جدید
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop
