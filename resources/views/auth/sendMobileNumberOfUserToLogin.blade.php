@extends('layouts.registerAndLogin')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                @if(isset($message))
                    <div class="alert {{ $alert }} text-right">{{ $message }}</div>
                @endif
                <div class="card">
                    <div class="card-header">{{ __('ورود کاربران (باربری، صاحب بار)') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('user/sendActivationCode') }}">
                            @csrf
                            <div class="form-group row">
                                <label for="mobileNumber"
                                       class="col-md-12 col-form-label text-md-right">{{ __('شماره تلفن همراه') }}</label>
                                <div class="col-md-12">
                                    <input id="mobileNumber" type="tel"
                                           class="col-md-12 form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                                           name="mobileNumber" value="{{ old('mobileNumber') }}"
                                           placeholder="شماره تلفن همراه" required autofocus>
                                    @if ($errors->has('mobileNumber'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="userType"
                                       class="col-md-12 col-form-label text-md-right">{{ __('نوع کاربر') }}</label>
                                <div class="col-md-12">
                                    <select id="userType"
                                            class="col-md-12 form-control{{ $errors->has('userType') ? ' is-invalid' : '' }}"
                                            name="userType">
                                        <option value="0">انتخاب نوع کاربری</option>
                                        <option value="1">باربری</option>
                                        <option value="2">صاحب بار</option>
                                        {{--<option value="3">راننده</option>--}}
                                        {{--<option value="4">بازاریاب</option>--}}
                                    </select>
                                    @if ($errors->has('userType'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('userType') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-12 offset-md-4">
                                    <button type="submit" class="btn btn-primary col-sm-12">
                                        {{ __('دریافت کد فعال سازی') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                        <hr>
                        <div class="mt-3">
                            اگر هنوز ثبت نام نکرده اید از طریق لینک های زیر اقدام به ثبت نام کنید
                            <div class="mt-2">
                                <a href="{{ url('user/registerBearing') }}">ثبت نام باربری</a>
                                <br>
                                <a href="{{ url('user/registerCustomer') }}">ثبت نام صاحب بار</a>
                                <br>
                                {{--<a href="{{ url('user/registerDriver') }}">ثبت نام راننده</a>--}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection