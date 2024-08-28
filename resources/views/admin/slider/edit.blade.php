@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ $slider->name }}
                </div>
                <div class="card-body">
                    <form action="{{ route('slider.update', $slider) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">نام</label>
                                <input class="form-control" type="text" id="name" value="{{ $slider->name }}"
                                    name="name" autofocus required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="file_url" class="form-label">فایل تصویر</label>
                                <input type="file" class="form-control" name="file_url">
                                <div class="form-text">
                                    320 * 115 اندازه تصویر
                                </div>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="state" class="form-label">وضعیت</label>
                                <div class="col-md">
                                    <div class="form-check">
                                        <input name="status" class="form-check-input" type="radio" value="0"
                                            id="status" @if ($slider->status == 0) checked @endif />
                                        <label class="form-check-label" for="status"> غیر فعال </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="status" class="form-check-input" type="radio" value="1"
                                            id="status" @if ($slider->status == 1) checked @endif />
                                        <label class="form-check-label" for="status"> فعال </label>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-2">
                                <button type="submit" class="btn btn-primary me-2">ویرایش</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
