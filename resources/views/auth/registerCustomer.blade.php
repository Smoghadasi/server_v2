@extends('layouts.registerAndLogin')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                @if(isset($message))
                    <div class="alert {{ $alert }} text-right">{{ $message }}</div>
                @endif
                <div class="card">
                    <div class="card-header">{{ __('ثبت نام صاحب بار') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('user/registerCustomer') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="row">
                                <div class="form-group col-md-4">
                                    <label for="name"
                                           class="col-md-12 col-form-label text-md-right">نوع :</label>
                                    <div class="col-md-12">
                                        <select class="form-control" name="userType" id="userType"
                                                onchange="selectUserType(this.value);">
                                            <option value="realPersonality">شخصیت حقیقی</option>
                                            <option value="legalPersonality">شخصیت حقوقی</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="name"
                                           class="col-md-12 col-form-label text-md-right">{{ __('نام : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="name" type="text"
                                               class="col-md-12 form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                               name="name" value="{{ old('name') }}"
                                               placeholder="نام" required autofocus>
                                        @if ($errors->has('name'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="lastName"
                                           class="col-md-12 col-form-label text-md-right">{{ __('نام خانوادگی : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="lastName" type="text"
                                               class="col-md-12 form-control{{ $errors->has('lastName') ? ' is-invalid' : '' }}"
                                               name="lastName" value="{{ old('lastName') }}"
                                               placeholder="نام خانوادگی" required autofocus>
                                        @if ($errors->has('lastName'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('lastName') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group col-md-4">
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


                                <div class="form-group col-md-4" id="nationalCode">
                                    <label for="nationalCode"
                                           class="col-md-12 col-form-label text-md-right">{{ __('کد ملی (اختیاری) : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="nationalCode"
                                               class="col-md-12 form-control{{ $errors->has('nationalCode') ? ' is-invalid' : '' }}"
                                               name="nationalCode" value="{{ old('nationalCode') }}"
                                               placeholder="کد ملی" autofocus>
                                        @if ($errors->has('nationalCode'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('nationalCode') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="nationalCardPic">
                                    <div class=" border m-1 pb-2">
                                        <label for="nationalCode"
                                               class="col-md-12 col-form-label text-md-right">{{ __('تصویر کارت ملی (اختیاری) : ') }}</label>
                                        <div class="col-md-12">
                                            <input type="file" name="nationalCardPic">
                                        </div>
                                    </div>
                                </div>


                                <div class="form-group col-md-4">
                                    <label for="marketerCode"
                                           class="col-md-12 col-form-label text-md-right">{{ __('کد معرف (اختیاری) : ') }}</label>
                                    <div class="col-md-12">
                                        <input id="marketerCode" type="tel"
                                               class="col-md-12 form-control"
                                               name="marketerCode" value="{{ old('marketerCode') }}"
                                               placeholder="کد معرف">
                                    </div>
                                </div>


                                <div class="form-group col-md-4" id="companyName">
                                    <label for="companyName"
                                           class="col-md-12 col-form-label text-md-right">نام شرکت :</label>
                                    <div class="col-md-12">
                                        <input id="companyName" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('companyName') ? ' is-invalid' : '' }}"
                                               name="companyName" value="{{ old('companyName') }}"
                                               placeholder="نام شرکت" autofocus>
                                        @if ($errors->has('companyName'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('companyName') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="email">
                                    <label for="email"
                                           class="col-md-12 col-form-label text-md-right">ایمیل :</label>
                                    <div class="col-md-12">
                                        <input id="email" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                                               name="email" value="{{ old('email') }}"
                                               placeholder="ایمیل" autofocus>
                                        @if ($errors->has('email'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="nationalID">
                                    <label for="nationalID"
                                           class="col-md-12 col-form-label text-md-right">شناسه ملی (اختیاری) :</label>
                                    <div class="col-md-12">
                                        <input id="nationalID" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('nationalID') ? ' is-invalid' : '' }}"
                                               name="nationalID" value="{{ old('nationalID') }}"
                                               placeholder="شناسه ملی" autofocus>
                                        @if ($errors->has('nationalID'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('nationalID') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="registrationNumber">
                                    <label for="registrationNumber"
                                           class="col-md-12 col-form-label text-md-right">شماره ثبت :</label>
                                    <div class="col-md-12">
                                        <input id="registrationNumber" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('registrationNumber') ? ' is-invalid' : '' }}"
                                               name="registrationNumber" value="{{ old('registrationNumber') }}"
                                               placeholder="شماره ثبت" autofocus>
                                        @if ($errors->has('registrationNumber'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('registrationNumber') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="phoneNumber">
                                    <label for="phoneNumber"
                                           class="col-md-12 col-form-label text-md-right"> شماره تلفن ثابت (اختیاری)
                                        :</label>
                                    <div class="col-md-12">
                                        <input id="phoneNumber" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('phoneNumber') ? ' is-invalid' : '' }}"
                                               name="phoneNumber" value="{{ old('phoneNumber') }}"
                                               placeholder="شماره تلفن ثابت" autofocus>
                                        @if ($errors->has('phoneNumber'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phoneNumber') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="cityCode">
                                    <label for="cityCode"
                                           class="col-md-12 col-form-label text-md-right"> کد شهر (اختیاری) :</label>
                                    <div class="col-md-12">
                                        <input id="cityCode" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('cityCode') ? ' is-invalid' : '' }}"
                                               name="cityCode" value="{{ old('cityCode') }}"
                                               placeholder="کد شهر" autofocus>
                                        @if ($errors->has('cityCode'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('cityCode') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-4" id="companyType">
                                    <label for="companyType"
                                           class="col-md-12 col-form-label text-md-right">نوع شرکت (اختیاری) :</label>
                                    <div class="col-md-12">
                                        <input id="companyType" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('companyType') ? ' is-invalid' : '' }}"
                                               name="companyType" value="{{ old('companyType') }}"
                                               placeholder="نوع شرکت" autofocus>
                                        @if ($errors->has('companyType'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('companyType') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-md-8" id="address">
                                    <label for="address"
                                           class="col-md-12 col-form-label text-md-right">آدرس :</label>
                                    <div class="col-md-12">
                                        <input id="address" type="tel"
                                               class="col-md-12 form-control{{ $errors->has('address') ? ' is-invalid' : '' }}"
                                               name="address" value="{{ old('address') }}"
                                               placeholder="آدرس" autofocus>
                                        @if ($errors->has('address'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                            </div>
                            <div class="form-group mb-0">
                                <div class="col-md-12 offset-md-4">
                                    <button type="submit" class="btn btn-primary col-sm-12">
                                        {{ __('دریافت کد فعال سازی') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>

        document.getElementById("companyName").style.display = "none";
        document.getElementById("email").style.display = "none";
        document.getElementById("nationalID").style.display = "none";
        document.getElementById("registrationNumber").style.display = "none";
        document.getElementById("phoneNumber").style.display = "none";
        document.getElementById("cityCode").style.display = "none";
        document.getElementById("companyType").style.display = "none";
        document.getElementById("address").style.display = "none";

        function selectUserType(value) {
            if (value === "realPersonality") {
                document.getElementById("nationalCode").style.display = "block";
                document.getElementById("nationalCardPic").style.display = "block";

                document.getElementById("companyName").style.display = "none";
                document.getElementById("email").style.display = "none";
                document.getElementById("nationalID").style.display = "none";
                document.getElementById("registrationNumber").style.display = "none";
                document.getElementById("phoneNumber").style.display = "none";
                document.getElementById("cityCode").style.display = "none";
                document.getElementById("companyType").style.display = "none";
                document.getElementById("address").style.display = "none";

            } else if (value === "legalPersonality") {
                document.getElementById("nationalCode").style.display = "none";
                document.getElementById("nationalCardPic").style.display = "none";

                document.getElementById("companyName").style.display = "block";
                document.getElementById("email").style.display = "block";
                document.getElementById("nationalID").style.display = "block";
                document.getElementById("registrationNumber").style.display = "block";
                document.getElementById("phoneNumber").style.display = "block";
                document.getElementById("cityCode").style.display = "block";
                document.getElementById("companyType").style.display = "block";
                document.getElementById("address").style.display = "block";
            }
        }
    </script>
@endsection
