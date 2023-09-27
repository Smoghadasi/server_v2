@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-7">

                <div class="card">
                    <div class="card-header">{{ __('ثبت نام باربری') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('user/addNewBearing') }}">
                            @csrf

                            <input type="hidden" value="{{ $mobileNumber }}" name="mobileNumber">

                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="form-group row">
                                        <label for="smartCode"
                                               class="col-md-4 col-form-label text-md-right">{{ __('عنوان باربری') }}</label>

                                        <div class="col-md-8">
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

                                        <div class="col-md-8">
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
                                        <label for="name"
                                               class="col-md-4 col-form-label text-md-right">{{ __('شماره ثبت') }}</label>
                                        <div class="col-md-8">
                                            <input id="registrationNumber" type="tel"
                                                   placeholder="شماره ثبت"
                                                   class="form-control{{ $errors->has('registrationNumber') ? ' is-invalid' : '' }}"
                                                   name="registrationNumber" value="{{ old('registrationNumber') }}"
                                                   required autofocus>

                                            @if ($errors->has('registrationNumber'))
                                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('registrationNumber') }}</strong>
                                    </span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="form-group row">
                                        <label for="city_id"
                                               class="col-md-4 col-form-label text-md-right">{{ __('شهر باربری') }}</label>

                                        <div class="col-md-8">
                                            <select id="city_id"
                                                    class="form-control{{ $errors->has('city_id') ? ' is-invalid' : '' }}"
                                                    name="city_id">
                                                <option value="0">شهر باربری</option>

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
                                        <label for="phoneNumber"
                                               class="col-md-4 col-form-label text-md-right">{{ __('شماره تلفن ثابت') }}</label>

                                        <div class="col-md-8">
                                            <input id="phoneNumber" type="tel" placeholder="شماره تلفن ثابت"
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

                                        <div class="col-md-8">
                                            <input id="mobileNumber" type="tel" placeholder="شماره تلفن همراه"
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
                                        {{ __('ثبت نام باربری جدید') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection