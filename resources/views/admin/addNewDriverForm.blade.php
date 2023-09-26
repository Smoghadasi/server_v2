@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            افزودن راننده
        </h5>
        <div class="card-body">

            <form method="POST" action="{{ url('admin/addNewDriver') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-6">

                        <div class="form-group row">
                            <label for="smartCode"
                                   class="col-md-4 col-form-label text-md-right">{{ __('کدهوشمند') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="smartCode" type="text"
                                       placeholder="کدهوشمند"
                                       class="form-control{{ $errors->has('smartCode') ? ' is-invalid' : '' }}"
                                       name="smartCode" value="{{ old('smartCode') }}" required>

                                @if ($errors->has('smartCode'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('smartCode') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nationalCode"
                                   class="col-md-4 col-form-label text-md-right">{{ __('کدملی') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="nationalCode" type="text"
                                       placeholder="کدملی"
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
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('نام') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="name" type="text"
                                       placeholder="نام"
                                       class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                       name="name" value="{{ old('name') }}" required autofocus>

                                @if ($errors->has('name'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('نام خانوادگی') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="lastName" type="text"
                                       placeholder="نام خانوادگی"
                                       class="form-control{{ $errors->has('lastName') ? ' is-invalid' : '' }}"
                                       name="lastName"
                                       value="{{ old('lastName') }}" required autofocus>

                                @if ($errors->has('lastName'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('lastName') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="fatherName"
                                   class="col-md-4 col-form-label text-md-right">{{ __('نام پدر') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="fatherName" type="text" placeholder="نام پدر"
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
                            <label for="birthDate"
                                   class="col-md-4 col-form-label text-md-right">{{ __('تاریخ تولد') }}</label>

                            <div class="col-md-6">
                                <input id="birthDate" type="text" placeholder="تاریخ تولد"
                                       class="form-control{{ $errors->has('birthDate') ? ' is-invalid' : '' }}"
                                       name="birthDate" value="{{ old('birthDate') }}">

                                @if ($errors->has('birthDate'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('birthDate') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cardNumber"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شماره کارت') }}</label>

                            <div class="col-md-6">
                                <input id="cardNumber" type="text" placeholder="شماره کارت"
                                       class="form-control{{ $errors->has('cardNumber') ? ' is-invalid' : '' }}"
                                       name="cardNumber" value="{{ old('cardNumber') }}" >

                                @if ($errors->has('cardNumber'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('cardNumber') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="cardPublishDate"
                                   class="col-md-4 col-form-label text-md-right">{{ __('تاریخ صدور کارت') }}</label>

                            <div class="col-md-6">
                                <input id="cardPublishDate" type="text" placeholder="تاریخ صدور کارت"
                                       class="form-control{{ $errors->has('cardPublishDate') ? ' is-invalid' : '' }}"
                                       name="cardPublishDate" value="{{ old('cardPublishDate') }}" >

                                @if ($errors->has('cardPublishDate'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('cardPublishDate') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="applicator_city_id"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شهر درخواست کننده') }}</label>

                            <div class="col-md-6">
                                <select id="applicator_city_id"
                                        class="form-control{{ $errors->has('applicator_city_id') ? ' is-invalid' : '' }}"
                                        name="applicator_city_id">
                                    <option value="0">شهر درخواست کننده</option>

                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach

                                </select>

                                @if ($errors->has('applicator_city_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('applicator_city_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="drivingLicence"
                                   class="col-md-4 col-form-label text-md-right">{{ __('گواهینامه رانندگی') }}</label>

                            <div class="col-md-6">
                                <input id="drivingLicence" type="text" placeholder="گواهینامه رانندگی"
                                       class="form-control{{ $errors->has('drivingLicence') ? ' is-invalid' : '' }}"
                                       name="drivingLicence" value="{{ old('drivingLicence') }}" >

                                @if ($errors->has('drivingLicence'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('drivingLicence') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="receipt_card_city_id"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شهر محل دریافت کارت') }}</label>

                            <div class="col-md-6 text-right">

                                <select class="form-control" name="receipt_card_city_id">

                                    <option value="0">شهر محل دریافت کارت</option>

                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach

                                </select>

                                @if ($errors->has('receipt_card_city_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('receipt_card_city_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="counter"
                                   class="col-md-4 col-form-label text-md-right">{{ __('تعداد کارکرد (کیلومتر)') }}</label>

                            <div class="col-md-6">
                                <input id="counter" type="text" placeholder="تعداد کارکرد (کیلومتر)"
                                       class="form-control{{ $errors->has('counter') ? ' is-invalid' : '' }}" name="counter"
                                >

                                @if ($errors->has('counter'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('counter') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">

                        <div class="form-group row">
                            <label for="docNumber"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شماره پرونده') }}</label>

                            <div class="col-md-6">
                                <input
                                    type="text" class="form-control" name="docNumber"
                                    placeholder="شماره پرونده"
                                    id="docNumber{{ $errors->has('docNumber')?' is-invalid' : '' }}"
                                >

                                @if ($errors->has('docNumber'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('docNumber') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="inquiryDate"
                                   class="col-md-4 col-form-label text-md-right">{{ __('تاریخ استعلام') }}</label>

                            <div class="col-md-6">
                                <input id="inquiryDate" type="text" placeholder="تاریخ استعلام"
                                       class="form-control{{ $errors->has('inquiryDate') ? ' is-invalid' : '' }}"
                                       name="inquiryDate" value="{{ old('inquiryDate') }}" >

                                @if ($errors->has('inquiryDate'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('inquiryDate') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="mobileNumber"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شماره موبایل') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6">
                                <input id="mobileNumber" type="text" placeholder="شماره موبایل"
                                       class="form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                                       name="mobileNumber"
                                       value="{{ old('mobileNumber') }}" required autofocus>

                                @if ($errors->has('mobileNumber'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="degreeOfEdu"
                                   class="col-md-4 col-form-label text-md-right">{{ __('مدرک تحصیلی') }}</label>

                            <div class="col-md-6">
                                <select id="degreeOfEdu"
                                        class="form-control{{ $errors->has('degreeOfEdu') ? ' is-invalid' : '' }}"
                                        name="degreeOfEdu">
                                    <option value="0">انتخاب مدرک تحصیلی</option>
                                    <option value="1">زیر دیپلم</option>
                                    <option value="2">دیپلم</option>
                                    <option value="3">فوق دیپلم</option>
                                    <option value="4">لیسانس</option>
                                    <option value="5">فوق لیسانس</option>
                                    <option value="6">دکتری</option>
                                    <option value="7">دیگر</option>
                                </select>

                                @if ($errors->has('degreeOfEdu'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('degreeOfEdu') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="driverType"
                                   class="col-md-4 col-form-label text-md-right">{{ __('نوع راننده') }}</label>

                            <div class="col-md-6">
                                <input id="driverType" type="text" placeholder="نوع راننده"
                                       class="form-control{{ $errors->has('driverType') ? ' is-invalid' : '' }}"
                                       name="driverType" value="{{ old('driverType') }}" >

                                @if ($errors->has('driverType'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('driverType') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="insuranceCode"
                                   class="col-md-4 col-form-label text-md-right">{{ __('کد بیمه') }}</label>

                            <div class="col-md-6">
                                <input id="insuranceCode" type="text" placeholder="کد بیمه"
                                       class="form-control{{ $errors->has('insuranceCode') ? ' is-invalid' : '' }}"
                                       name="insuranceCode" value="{{ old('insuranceCode') }}" >

                                @if ($errors->has('insuranceCode'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('insuranceCode') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="city_id" class="col-md-4 col-form-label text-md-right">{{ __('شهرستان') }}</label>

                            <div class="col-md-6">
                                <select id="city_id"
                                        class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}"
                                        name="city_id">
                                    <option value="0">شهرستان</option>

                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                                    @endforeach
                                </select>
                                @if ($errors->has('city_id'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('city_id') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="validityDate"
                                   class="col-md-4 col-form-label text-md-right">{{ __('تاریخ اعتبار') }}</label>

                            <div class="col-md-6">
                                <input id="validityDate" type="text" placeholder="تاریخ اعتبار"
                                       class="form-control{{ $errors->has('validityDate') ? ' is-invalid' : '' }}"
                                       name="validityDate" value="{{ old('validityDate') }}">

                                @if ($errors->has('validityDate'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('validityDate') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="distances"
                                   class="col-md-4 col-form-label text-md-right">{{ __('مسافت (مجموع بار و مسافر)') }}</label>

                            <div class="col-md-6">
                                <input id="distances" type="text" placeholder="مسافت (مجموع بار و مسافر)"
                                       class="form-control{{ $errors->has('distances') ? ' is-invalid' : '' }}"
                                       name="distances" value="{{ old('distances') }}">

                                @if ($errors->has('distances'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('distances') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group row">
                            <label for="fleet_id"
                                   class="col-md-4 col-form-label text-md-right">{{ __('نوع ناوگان') }}</label>
                            <span style="color: #ff0000">*</span>
                            <div class="col-md-6 text-right">

                                <select class="form-control" name="fleet_id">

                                    <option value="0">نوع ناوگان</option>

                                    @foreach($fleets as $fleet)
                                        <option value="{{ $fleet->id }}">
                                            {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                            -
                                            {{ $fleet->title }}
                                        </option>
                                    @endforeach

                                </select>

                                @if ($errors->has('fleet_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fleet_id') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>


                        <div class="form-group row text-right">
                            <label for="pic" class="col-md-4 col-form-label text-md-right">{{ __('تصویر راننده') }}</label>
                            <div class="col-md-6">
                                <input id="pic" type="file" name="pic">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('ثبت راننده جدید') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@stop
