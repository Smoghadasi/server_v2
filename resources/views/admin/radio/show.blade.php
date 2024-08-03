@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    {{ $radio->name }} ({{ $radio->status == 1 ? 'فعال' : 'غیر فعال' }})
                </div>
                <div class="card-body">
                    @csrf
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="name" class="form-label">نام رادیو</label>
                            <input class="form-control" type="text" disabled id="name" value="{{ $radio->name }}"
                                name="name" autofocus required />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="artist" class="form-label">پخش کننده رادیو</label>
                            <input class="form-control" type="text" disabled name="artist" id="artist"
                                value="رادیو ایران ترابر" required />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="email" class="form-label">کاور آهنگ (عکس)</label>
                            <img src="{{ asset($radio->cover) }}" alt="user-avatar" class="d-block rounded" width="300"
                                width="100" id="uploadedAvatar" />
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="source" class="form-label">فایل آهنگ</label>
                            <audio controls class="d-block">
                                <source src="{{ asset($radio->source) }}" type="audio/mpeg">
                            </audio>
                        </div>



                        <div class="mt-2">
                            <a class="btn btn-primary" href="{{ route('radio.edit', $radio) }}">ویرایش</a>
                            {{-- <button type="submit" class="btn btn-primary me-2">ثبت</button> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
