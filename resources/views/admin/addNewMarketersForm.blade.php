@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            افزودن بازاریاب
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>

    @if(strlen($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif

    <div class="card-body">
        <form method="POST" action="{{ url('admin/addNewMarketer') }}" enctype="multipart/form-data">
            @csrf

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('نام :') }}</label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name') }}" required autofocus>

                    @if ($errors->has('name'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('نام خانوادگی :') }}</label>

                <div class="col-md-6">
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


            <div class="form-group row">
                <label for="nationalCode" class="col-md-4 col-form-label text-md-right">{{ __('کد ملی :') }}</label>

                <div class="col-md-6">
                    <input id="nationalCode" type="text"
                           class="form-control{{ $errors->has('nationalCode') ? ' is-invalid' : '' }}"
                           name="nationalCode" value="{{ old('nationalCode') }}" required>

                    @if ($errors->has('nationalCode'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('nationalCode') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="mobileNumber"
                       class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن همراه :') }}</label>

                <div class="col-md-6">
                    <input id="nationalCode" type="text"
                           class="form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                           name="mobileNumber" value="{{ old('mobileNumber') }}" required>

                    @if ($errors->has('mobileNumber'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="phoneNumber"
                       class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن ثابت :') }}</label>

                <div class="col-md-6">
                    <input id="nationalCode" type="text"
                           class="form-control{{ $errors->has('phoneNumber') ? ' is-invalid' : '' }}"
                           name="phoneNumber" value="{{ old('phoneNumber') }}" >

                    @if ($errors->has('phoneNumber'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phoneNumber') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="emergencyPhoneNumber"
                       class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن ضروری :') }}</label>

                <div class="col-md-6">
                    <input id="emergencyPhoneNumber" type="text"
                           class="form-control{{ $errors->has('emergencyPhoneNumber') ? ' is-invalid' : '' }}"
                           name="emergencyPhoneNumber" value="{{ old('emergencyPhoneNumber') }}" >

                    @if ($errors->has('emergencyPhoneNumber'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('emergencyPhoneNumber') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="fatherName" class="col-md-4 col-form-label text-md-right">{{ __('نام پدر :') }}</label>

                <div class="col-md-6">
                    <input id="fatherName" type="text"
                           class="form-control{{ $errors->has('fatherName') ? ' is-invalid' : '' }}"
                           name="fatherName" value="{{ old('fatherName') }}" required>

                    @if ($errors->has('fatherName'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fatherName') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="address" class="col-md-4 col-form-label text-md-right">{{ __('آدرس :') }}</label>

                <div class="col-md-6">
                    <textarea id="address"
                              class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}"
                              name="address" value="{{ old('address') }}"></textarea>

                    @if ($errors->has('address'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>


            <div class="form-group row text-right">
                <label for="pic" class="col-md-4 col-form-label text-md-right">{{ __('تصویر بازاریاب :') }}</label>
                <div class="col-md-6">
                    <input id="pic" type="file" name="pic">
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('ثبت بازاریاب جدید') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

@stop
