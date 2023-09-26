@extends('layouts.dashboard')

@section('content')
    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            ویرایش نوع بسته بندی
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    @if(strlen($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif
    <div class="card-body">
        <form method="POST" action="{{ url('admin/editPackingType') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $packingType->id }}">
            <div class="form-group row">
                <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('عنوان نوع بسته بندی') }}</label>
                <div class="col-md-6">
                    <input id="title" type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                           name="title" value="{{ $packingType->title }}" required autofocus>
                    @if ($errors->has('title'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row text-right">
                <label for="pic" class="col-md-4 col-form-label text-md-right">{{ __('تصویر بسته بندی') }}</label>
                <div class="col-md-6">
                    <input id="pic" type="file" name="pic">
                </div>
            </div>
            
            <div class="form-group row text-right">
                <label for="pic" class="col-md-4 col-form-label text-md-right">{{ __('تصویر فعلی') }}</label>
                <div class="col-md-6">
                    <img src="{{ url($packingType->pic) }}" class="img-thumbnail" width="100" height="100">
                </div>
            </div>
            
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('ثبت نوع بسته بندی جدید') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop