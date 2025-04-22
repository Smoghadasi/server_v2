@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            ویرایش اطلاعات
        </h5>
        <div class="card-body">

            <form method="POST" action="{{ route('operators.update', $user) }}" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="name" class="form-label">نام</label>
                        <input class="form-control" type="text" id="name" name="name"
                            value="{{ $user->name }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="lastName" class="form-label">نام خانوادگی</label>
                        <input class="form-control" type="text" name="lastName" id="lastName"
                            value="{{ $user->lastName }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="fatherName" class="form-label">نام پدر</label>
                        <input class="form-control" type="text" id="fatherName" name="fatherName"
                            value="{{ $user->fatherName }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="email" class="form-label">ایمیل</label>
                        <input class="form-control" type="email" id="email" name="email"
                            value="{{ $user->email }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="role" class="form-label">نقش</label>
                        <select class="form-control form-select" name="role" id="role">
                            <option value="admin" @if ($user->role == 'admin') selected @endif>مدیر</option>
                            <option value="operator" @if ($user->role == 'operator') selected @endif>اپراتور</option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="nationalCode" class="form-label">کد ملی</label>
                        <input class="form-control" type="text" id="nationalCode" name="nationalCode"
                            value="{{ $user->nationalCode }}" maxlength="10" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="birthdate" class="form-label">تاریخ تولد</label>
                        <input class="form-control" type="text" id="birthdate" name="birthdate" value="{{ gregorianDateToPersian($user->birthdate, '-', true) }}" placeholder="تاریخ تولد"
                            autocomplete="off" />
                        <span id="span1"></span>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="education" class="form-label">تحصیلات</label>
                        <input class="form-control" type="text" id="education" name="education"
                            value="{{ $user->education }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="mobileNumber" class="form-label">شماره تلفن</label>
                        <input type="text" id="mobileNumber" name="mobileNumber" class="form-control"
                            value="{{ $user->mobileNumber }}" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="degree" class="form-label">مدرک</label>
                        @if ($user->degree)
                            <img class="form-control" src="{{ $user->degree }}" alt="">
                        @else
                            <input type="file" class="form-control" name="{{ $user->degree }}" id="">
                        @endif
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="address" class="form-label">آدرس</label>
                        <textarea class="form-control" rows="5" name="address" id="">{{ $user->address }}</textarea>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">ویرایش اطلاعات</button>
            </form>
        </div>
        <!-- /Account -->
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#birthdate, #span1").persianDatepicker();
    </script>
@endsection
