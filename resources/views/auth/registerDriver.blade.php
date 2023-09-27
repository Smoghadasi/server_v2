@extends('layouts.registerAndLogin')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                @if(isset($message))
                    <div class="alert {{ $alert }} text-right">{{ $message }}</div>
                @endif
                <div class="card mb-2">
                    <div class="card-header">{{ __('ثبت نام راننده') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('user/registerBearing') }}">
                            @csrf
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="title"
                                           class="col-md-12 col-form-label text-md-right">{{ __('عنوان راننده : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="title" type="text"
                                               class="col-md-12 form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                                               name="title" value="{{ old('title') }}"
                                               placeholder="عنوان راننده" required autofocus>
                                        @if ($errors->has('title'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="operatorName"
                                           class="col-md-12 col-form-label text-md-right">{{ __('نام متصدی : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="operatorName" type="text"
                                               class="col-md-12 form-control{{ $errors->has('operatorName') ? ' is-invalid' : '' }}"
                                               name="operatorName" value="{{ old('operatorName') }}"
                                               placeholder="نام متصدی" required autofocus>
                                        @if ($errors->has('operatorName'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('operatorName') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="registrationNumber"
                                           class="col-md-12 col-form-label text-md-right">{{ __('شناسه ثبت راننده : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="registrationNumber" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('registrationNumber') ? ' is-invalid' : '' }}"
                                               name="registrationNumber" value="{{ old('registrationNumber') }}"
                                               placeholder="شناسه ثبت راننده" required autofocus>
                                        @if ($errors->has('registrationNumber'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('registrationNumber') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="mobileNumber"
                                           class="col-md-12 col-form-label text-md-right">{{ __('شماره تلفن همراه : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="mobileNumber" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                                               name="mobileNumber"
                                               value="@if(isset($mobileNumber)){{ $mobileNumber }}@else{{ old('mobileNumber') }}@endif"
                                               placeholder="شماره تلفن همراه" required autofocus>
                                        @if ($errors->has('mobileNumber'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="phoneNumber"
                                           class="col-md-12 col-form-label text-md-right">{{ __('شماره تلفن ثابت : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="phoneNumber" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('phoneNumber') ? ' is-invalid' : '' }}"
                                               name="phoneNumber"
                                               value="@if(isset($phoneNumber)){{ $phoneNumber }}@else{{ old('phoneNumber') }}@endif"
                                               placeholder="شماره تلفن ثابت" required autofocus>
                                        @if ($errors->has('phoneNumber'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phoneNumber') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-6">
                                    <label for="city"
                                           class="col-md-12 col-form-label text-md-right">{{ __('شهر راننده : ') }}</label>
                                    <div class="col-md-12">
                                        <select id="city"
                                                class="col-md-12 form-control{{ $errors->has('city') ? ' is-invalid' : '' }}"
                                                name="city">
                                            <option value="0">شهر خود را انتخاب نمایید</option>
                                            @foreach($cities as $city)
                                                <option value="{{ $city->id }}">
                                                    <?php
                                                    echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name));
                                                    ?></option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('city'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('city') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="form-group col-md-6">
                                    <label for="marketerCode"
                                           class="col-md-12 col-form-label text-md-right">{{ __('کد معرف : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="marketerCode" type="tel"
                                               class="col-md-12 form-control"
                                               name="marketerCode" value="{{ old('marketerCode') }}"
                                               placeholder="کد معرف">
                                    </div>
                                </div>


                                <div class="form-group col-md-6">
                                    <label for="marketerCode"
                                           class="col-md-12 col-form-label text-md-right">{{ __('ثبت اطلاعات') }}</label>
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary col-sm-12">
                                            {{ __('دریافت کد فعال سازی') }}
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
        $("#registrationNumber").keydown(function (e) {
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
        $("#marketerCode").keydown(function (e) {
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                (e.keyCode >= 35 && e.keyCode <= 39)) {
                return;
            }
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        });
    </script>

@endsection