@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            افزودن ناوگان
        </h5>
        <div class="card-body">

            <form method="POST" action="{{ url('admin/addNewFleet') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group row m-2">
                    <label for="parent_id" class="col-md-4 col-form-label text-md-right">{{ __('عنوان گروه اصلی') }}</label>

                    <div class="col-md-6">
                        <select name="parent_id" class="form-control">
                            <option value="0">به عنوان گروه اصلی</option>
                            @foreach($fleets as $fleet)
                                <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row m-2">
                    <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('عنوان نوع بار') }}</label>

                    <div class="col-md-6">
                        <input id="title" type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                               name="title" value="{{ old('name') }}" required autofocus>

                        @if ($errors->has('title'))
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('title') }}</strong>
                                    </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row text-right m-2">
                    <label for="pic" class="col-md-4 col-form-label text-md-right">{{ __('تصویر نوع ناوگان') }}</label>
                    <div class="col-md-6">
                        <input class="form-control" id="pic" type="file" name="pic">
                    </div>
                </div>

                <div class="form-group row text-right m-2">
                    <h4 class="col-lg-12">ظرفیت :</h4>
                    <div class="col-sm-3">
                        <label>طول (متر) : </label>
                        <input class="form-control" step="any" type="number" name="length" placeholder="طول (متر)">
                    </div>
                    <div class="col-sm-3">
                        <label>عرض (متر) : </label>
                        <input class="form-control" step="any" type="number" name="width" placeholder="عرض (متر)">
                    </div>
                    <div class="col-sm-3">
                        <label>ارتفاع (متر) : </label>
                        <input class="form-control" step="any" type="number" name="height" placeholder="ارتفاع (متر)">
                    </div>
                    <div class="col-sm-3">
                        <label>ظرفیت (تن) : </label>
                        <input class="form-control" step="any" type="number" name="capacity" placeholder="ظرفیت (تن)">
                    </div>
                </div>

                <div class="form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            {{ __('ثبت ناوگان جدید') }}
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

@stop
