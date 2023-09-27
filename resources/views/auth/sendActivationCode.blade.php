@extends('layouts.registerAndLogin')
@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                @if(isset($message))
                    <div class="alert alert-danger text-center">{{ $message }}</div>
                @endif
                <div class="alert alert-primary">
                    کد ارسال شده برای شماره تلفن
                    {{ $mobileNumber }}
                    را وارد نمایید
                </div>
                <div class="card">
                    <div class="card-header">{{ __('ارسال کد فعال سازی') }}</div>
                    <div class="card-body">
                        <form method="POST" action="{{ url('user/validateActivationCode') }}">

                            @csrf

                            <input type="hidden" value="{{ $userType }}" name="userType">
                            <input type="hidden" value="{{ $mobileNumber }}" name="mobileNumber">

                            <div class="form-group row">
                                <label for="code"
                                       class="col-md-12 col-form-label text-md-right">{{ __('کد فعال سازی') }}</label>
                                <div class="col-md-12">
                                    <input id="code" type="tel"
                                           class="col-md-12 form-control{{ $errors->has('code') ? ' is-invalid' : '' }}"
                                           name="code" value="{{ old('code') }}"
                                           placeholder="کد فعال سازی" required autofocus>
                                    @if ($errors->has('code'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('code') }}</strong>
                                            @if($errors->first('mobileNumber'))
                                                <br><strong>{{ $errors->first('mobileNumber') }}</strong>
                                            @endif
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row mb-0">
                                <div class="col-md-12 offset-md-4">
                                    <button type="submit" class="btn btn-primary col-sm-12">
                                        {{ __('ارسال کد فعال سازی') }}
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