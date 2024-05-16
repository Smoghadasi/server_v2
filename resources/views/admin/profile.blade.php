@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <ul class="nav nav-pills flex-column flex-md-row mb-3">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ route('user.edit') }}"><i class="bx bx-user me-1"></i> حساب کاربری</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('login.history') }}"><i class="bx bx-history me-1"></i> تاریخچه ورود و خروج</a>
                </li>
            </ul>
            <div class="card mb-4">
                <div class="card-body">
                    <form action="{{ url('admin/restPassword') }}/{{ auth()->id() }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="mb-3 col-md-6">
                                <label for="firstName" class="form-label">نام</label>
                                <input class="form-control" type="text" value="{{ auth()->user()->name }}" disabled
                                    autofocus />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="lastName" class="form-label">نام خانوادگی</label>
                                <input class="form-control" type="text" value="{{ auth()->user()->lastName }}" disabled />
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">شماره تلفن</label>
                                <input class="form-control" type="text" value="{{ auth()->user()->mobileNumber }}" disabled/>
                            </div>

                            <div class="mb-3 col-md-6">
                                <label for="email" class="form-label">رمز عبور جدید</label>
                                <input class="form-control" type="text" id="password" name="password" />
                            </div>

                        </div>
                        <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">ذخیره</button>
                        </div>
                    </form>
                </div>
                <!-- /Account -->
            </div>
        </div>
    </div>
@endsection
