@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            افزودن باربری
        </h5>
        <div class="card-body">

            <form method="POST" action="{{ url('admin/addNewBearing') }}">
                @csrf
                <div class="row">
                    <div class="col-lg-6">

                        <div class="form-group row">
                            <label for="smartCode"
                                   class="col-md-4 col-form-label text-md-right">{{ __('عنوان باربری') }}</label>

                            <div class="col-md-6">
                                <input id="title" type="text"
                                       placeholder="عنوان باربری"
                                       class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                                       name="title" value="{{ old('title') }}" required>

                                @if ($errors->has('title'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('title') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="nationalCode"
                                   class="col-md-4 col-form-label text-md-right">{{ __('نام متصدی') }}</label>

                            <div class="col-md-6">
                                <input id="operatorName" type="text"
                                       placeholder="نام متصدی"
                                       class="form-control{{ $errors->has('operatorName') ? ' is-invalid' : '' }}"
                                       name="operatorName" value="{{ old('operatorName') }}" required>

                                @if ($errors->has('operatorName'))
                                    <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('operatorName') }}</strong>
                        </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="name" class="col-md-4 col-form-label text-md-right">{{ __('شماره ثبت') }}</label>
                            <div class="col-md-6">
                                <input id="registrationNumber" type="text"
                                       placeholder="شماره ثبت"
                                       class="form-control{{ $errors->has('registrationNumber') ? ' is-invalid' : '' }}"
                                       name="registrationNumber" value="{{ old('registrationNumber') }}" required autofocus>

                                @if ($errors->has('registrationNumber'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('registrationNumber') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="col-lg-6">
                        <div class="form-group row">
                            <label for="city_id"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شهر باربری') }}</label>

                            <div class="col-md-6">
                                <select id="city_id"
                                        class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}"
                                        name="city_id">
                                    <option value="0">شهر باربری</option>

                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}"><?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?></option>

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
                            <label for="phoneNumber"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن ثابت') }}</label>

                            <div class="col-md-6">
                                <input id="phoneNumber" type="text" placeholder="شماره تلفن ثابت"
                                       class="form-control{{ $errors->has('phoneNumber') ? ' is-invalid' : '' }}"
                                       name="phoneNumber" value="{{ old('phoneNumber') }}" required>

                                @if ($errors->has('phoneNumber'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('phoneNumber') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="mobileNumber"
                                   class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن همراه') }}</label>

                            <div class="col-md-6">
                                <input id="mobileNumber" type="text" placeholder="شماره تلفن همراه"
                                       class="form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                                       name="mobileNumber" value="{{ old('mobileNumber') }}" required>

                                @if ($errors->has('mobileNumber'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
                <div class="row form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('ثبت باربری جدید') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>


@stop
