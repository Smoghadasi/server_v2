@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            ویرایش نوع بار
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    @if(isset($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif
    <div class="card-body">
        <form method="POST" action="{{ url('admin/editLoadType') }}" enctype="application/x-www-form-urlencoded">
            @csrf

            <div class="form-group row">
                <label for="parent_id" class="col-md-4 col-form-label text-md-right">{{ __('عنوان گروه اصلی') }}</label>
                <input type="hidden" name="id" value="{{ $loadType->id }}">
                <div class="col-md-6">
                    <select name="parent_id" class="form-control">
                        <option value="0">به عنوان گروه اصلی</option>
                        @foreach($loadTypeParents as $loadTypeParent)
                            <option value="{{ $loadTypeParent->id }}"
                                    @if($loadTypeParent->id == $loadType->parent_id)
                                    selected
                                    @endif
                            >{{ $loadTypeParent->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group row">
                <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('عنوان نوع بار') }}</label>

                <div class="col-md-6">
                    <input id="title" type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                           name="title" value="{{ $loadType->title }}" required autofocus>

                    @if ($errors->has('title'))
                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-primary">
                        {{ __('ثبت نوع بار جدید') }}
                    </button>
                </div>
            </div>
        </form>
    </div>

@stop
