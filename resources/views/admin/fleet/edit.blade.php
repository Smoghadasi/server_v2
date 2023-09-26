@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            ویرایش ناوگان
        </h5>
        <div class="card-body">

            <form method="POST" action="{{ route('fleet.update', $fleet) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group row m-2">
                    <label for="parent_id" class="col-md-4 col-form-label text-md-right">{{ __('عنوان گروه اصلی') }}</label>
                    <input type="hidden" name="id" value="{{ $fleet->id }}">
                    <div class="col-md-6">
                        <select name="parent_id" class="form-control">
                            <option value="0">به عنوان گروه اصلی</option>
                            @foreach ($fleetParents as $fleetParent)
                                <option value="{{ $fleetParent->id }}" @if ($fleetParent->id == $fleet->parent_id) selected @endif>
                                    {{ $fleetParent->title }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group row m-2">
                    <label for="title" class="col-md-4 col-form-label text-md-right">{{ __('عنوان ناوگان') }}</label>

                    <div class="col-md-6">
                        <input id="title" type="text"
                            class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}" name="title"
                            value="{{ $fleet->title }}" required autofocus>

                        @if ($errors->has('title'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('title') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <div class="form-group row text-right m-2">
                    <label for="pic"
                        class="col-md-4 col-form-label text-md-right">{{ __('تصویر جدید نوع ناوگان') }}</label>
                    <div class="col-md-6">
                        <input id="pic" class="form-control" type="file" name="pic">
                    </div>
                </div>

                <div class="form-group row text-right m-2">
                    <label class="col-md-4 col-form-label text-md-right">{{ __('تصویر فعلی نوع ناوگان') }}</label>
                    <img class="img-fluid" src="{{ url($fleet->pic) }}" style="width: 15rem">
                </div>

                <div class="form-group row text-right m-2">
                    <h4 class="col-lg-12">ظرفیت :</h4>
                    <div class="col-sm-3">
                        <label>طول (متر) : </label>
                        <input class="form-control" step="any" type="number" name="length" placeholder="طول (متر)"
                            value="{{ $fleet->length }}">
                        @if ($errors->has('length'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('length') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <label>عرض (متر) : </label>
                        <input class="form-control" step="any" type="number" name="width" placeholder="عرض (متر)"
                            value="{{ $fleet->width }}">
                        @if ($errors->has('width'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('width') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <label>ارتفاع (متر) : </label>
                        <input class="form-control" step="any" type="number" name="height" placeholder="ارتفاع (متر)"
                            value="{{ $fleet->height }}">
                        @if ($errors->has('pic'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('height') }}</strong>
                            </span>
                        @endif
                    </div>
                    <div class="col-sm-3">
                        <label>ظرفیت (تن) : </label>
                        <input class="form-control" step="any" type="number" name="capacity" placeholder="ظرفیت (تن)"
                            value="{{ $fleet->capacity }}">
                        @if ($errors->has('pic'))
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $errors->first('capacity') }}</strong>
                            </span>
                        @endif
                    </div>
                </div>

                <p></p>
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
