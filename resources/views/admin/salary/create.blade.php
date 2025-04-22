@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            افزودن حقوق و دریافتی: {{ $user->name }} {{ $user->lastName }}
        </h5>
        <div class="card-body">
            @include('partials.error')
            <form method="POST" action="{{ route('salary.store') }}">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="price" class="form-label">مبلغ</label>
                        <input class="form-control" type="text" value="{{ old('price') }}" name="price" id="price" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="salary" class="form-label">حقوق پایه</label>
                        <input class="form-control" type="text" value="{{ old('salary') }}" id="salary" name="salary" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="salary_increase" class="form-label">اضافه کار / عیدی</label>
                        <input class="form-control" type="text" id="salary_increase" value="{{ old('salary_increase') }}" name="salary_increase" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="date" class="form-label">تاریخ</label>
                        <input class="form-control" type="text" id="date" name="date" placeholder="تاریخ"
                            autocomplete="off" />
                        <span id="span1"></span>
                    </div>

                    <div class="mb-3 col-md-12">
                        <label for="address" class="form-label">توضیحات</label>
                        <textarea class="form-control" rows="5" name="description">{{ old('description') }}</textarea>
                    </div>
                    <input type="hidden" value="{{ $user->id }}" name="user_id">
                </div>

                <button type="submit" class="btn btn-primary">ثبت</button>
            </form>
        </div>
    </div>

@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#date, #span1").persianDatepicker();
    </script>
@endsection
