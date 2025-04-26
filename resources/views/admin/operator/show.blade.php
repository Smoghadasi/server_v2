@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            نمایش اطلاعات
        </h5>
        <div class="card-body">
            <form id="formAccountSettings" method="POST" onsubmit="return false">
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="firstName" class="form-label">نام</label>
                        <input class="form-control" type="text" id="firstName" name="firstName"
                            value="{{ $user->name }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="lastName" class="form-label">نام خانوادگی</label>
                        <input class="form-control" type="text" name="lastName" id="lastName"
                            value="{{ $user->lastName }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="fatherName" class="form-label">نام پدر</label>
                        <input class="form-control" type="text" id="fatherName" name="fatherName"
                            value="{{ $user->fatherName ?? '-' }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="role" class="form-label">نقش</label>
                        <input class="form-control" type="text" id="role" name="role"
                            value="{{ $user->role ?? '-' }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="nationalCode" class="form-label">کد ملی</label>
                        <input class="form-control" type="text" id="nationalCode" name="nationalCode"
                            value="{{ $user->nationalCode ?? '-' }}" maxlength="10" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="birthdate" class="form-label">تاریخ تولد</label>
                        <input class="form-control" type="text"
                            value="{{ $user->birthdate ? gregorianDateToPersian($user->birthdate, '-', true) : '-' }}"
                            id="birthdate" name="birthdate" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="education" class="form-label">تحصیلات</label>
                        <input class="form-control" type="text" id="education" name="education"
                            value="{{ $user->education ?? '-' }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="mobileNumber" class="form-label">شماره تلفن</label>
                        <input type="text" id="mobileNumber" name="mobileNumber" class="form-control"
                            value="{{ $user->mobileNumber }}" disabled />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="degree" class="form-label">مدرک</label>
                        @if ($user->degree)
                            <img class="form-control" src="{{ $user->degree }}" alt="">
                        @else
                            <input class="form-control" value="ندارد" disabled type="text">
                        @endif
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="address" class="form-label">آدرس</label>
                        <textarea class="form-control" rows="5" name="" id="" disabled>{{ $user->address }}</textarea>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer">
            <div class="mt-2">
                <a href="{{ route('contract.index', ['user_id' => $user->id]) }}" class="btn btn-primary me-2">قرارداد ها</a>
                <a href="{{ route('salary.index', ['user_id' => $user->id]) }}" class="btn btn-primary me-2">حقوق دریافتی</a>
                <a href="{{ route('vacation.day', $user->id) }}" class="btn btn-primary me-2">مرخصی روزانه</a>
                <a href="{{ route('vacation.hour', $user->id) }}" class="btn btn-primary me-2">مرخصی ساعتی</a>
                <a href="{{ route('operators.edit', $user) }}" class="btn btn-danger me-2">ویرایش اطلاعات</a>
                <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal"
                    data-bs-target="#changePassOperator_{{ $user->id }}">تغییر رمز عبور
                </button>
                 <!-- Modal -->
                 <div id="changePassOperator_{{ $user->id }}" class="modal fade" role="dialog">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form method="POST" action="{{ route('user.resetPass', $user->id) }}">
                                @csrf
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalTopTitle">تغییر رمز عبور :
                                        {{ $user->name }} {{ $user->lastName }}</h5>

                                </div>
                                <div class="modal-body">
                                    <div class="row">
                                        <div class="col mb-3">
                                            <label for="password" class="form-label">رمز عبور</label>
                                            <input type="text" id="password" name="password"
                                                class="form-control" placeholder="" />
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                    <button type="submit" class="btn btn-primary">ذخیره</button>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
                @if (auth()->user()->role == 'admin')
                    @if ($user->status == 0)
                        <a class="btn btn-primary me-2"
                            href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                            تغییر به فعال
                        </a>
                    @else
                        <a class="btn btn-primary me-2"
                            href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                            تغییر به غیرفعال
                        </a>
                    @endif
                    <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal"
                        data-bs-target="#removeOperator_{{ $user->id }}">حذف
                    </button>



                    <!-- Modal -->
                    <div id="removeOperator_{{ $user->id }}" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h4 class="modal-title">حذف اپراتور</h4>
                                </div>
                                <div class="modal-body">
                                    <p>آیا مایل به حذف اپراتور
                                        <span class="text-primary"> {{ $user->name }}
                                            {{ $user->lastName }}</span>
                                        هستید؟
                                    </p>
                                </div>
                                <div class="modal-footer text-left">
                                    <a class="btn btn-primary"
                                        href="{{ url('admin/removeOperator') }}/{{ $user->id }}">حذف
                                        اپراتور</a>
                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>

                @endif
            </div>
        </div>
        <!-- /Account -->
    </div>

@stop
