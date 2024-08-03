@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ $radio->name }}
                </div>
                <div class="card-body">
                    <form action="{{ route('radio.update', $radio) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="name" class="form-label">نام رادیو</label>
                                <input class="form-control" type="text" id="name" value="{{ $radio->name }}" name="name" autofocus
                                    required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="artist" class="form-label">پخش کننده رادیو</label>
                                <input class="form-control" type="text" name="artist" value="{{ $radio->artist }}" id="artist" required />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">کاور آهنگ (عکس)</label>
                                <input type="file" class="form-control" name="cover" id="cover">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="source" class="form-label">فایل آهنگ</label>
                                <input type="file" class="form-control" name="source" accept="audio/*">
                                <div class="form-text">
                                    کمتر 15 مگابایت
                                </div>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="state" class="form-label">وضعیت</label>
                                <div class="col-md">
                                    <div class="form-check">
                                        <input name="status" class="form-check-input" type="radio" value="0"
                                            id="status" @if($radio->status == 0) checked @endif />
                                        <label class="form-check-label" for="status"> غیر فعال </label>
                                    </div>
                                    <div class="form-check">
                                        <input name="status" class="form-check-input" type="radio" value="1"
                                            id="status" @if($radio->status == 1) checked @endif />
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
