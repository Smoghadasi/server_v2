@extends('layouts.dashboard')

@section('content')
    <div class="row">

        <div class="col-md-12">

            <div class="card mb-4">
                <div class="card-header">
                    @switch($owner->isOwner)
                        @case(1)
                            صاحب بار
                        @break

                        @case(2)
                            باربری
                        @break

                        @default
                            تعیین نشده
                    @endswitch
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="name" class="col-md-2 col-form-label">نام</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" value="{{ $owner->name }}" name="name"
                                    disabled id="name">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="lastName" class="col-md-2 col-form-label">نام خانوادگی</label>
                            <div class="col-md-12">
                                <input class="form-control" name="lastName" value="{{ $owner->lastName }}" disabled
                                    type="text" id="lastName">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="nationalCode" class="col-md-2 col-form-label">کد ملی</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="nationalCode" disabled
                                    value="{{ $owner->nationalCode }}" id="nationalCode">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="mobileNumber" class="col-md-2 col-form-label">شماره موبایل</label>
                            <div class="col-md-12">
                                <input class="form-control" name="mobileNumber" type="text" disabled
                                    value="{{ $owner->mobileNumber }}" id="mobileNumber">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="postalCode" class="col-form-label">آدرس</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="address" value="{{ $owner->address ?? '-' }}"
                                    disabled id="address">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="postalCode" class="col-form-label">کد پستی</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="postalCode" disabled
                                    value="{{ $owner->postalCode ?? '-' }}" id="postalCode">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="status" class="col-form-label">وضعیت</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="status" disabled
                                    value="{{ $owner->status == 1 ? 'فعال' : 'غیر فعال' }}" id="status">
                            </div>
                        </div>
                        @if ($owner->isOwner == 2)
                            <div class="mb-3 col-md-6">
                                <label for="companyName" class="col-form-label">نام شرکت</label>
                                <div class="col-md-12">
                                    <input class="form-control" name="companyName" type="text" disabled
                                        value="{{ $owner->companyName }}" id="companyName">
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="companyID" class="col-form-label">شناسه شرکت</label>
                                <div class="col-md-12">
                                    <input class="form-control" name="companyID" type="text" disabled
                                        value="{{ $owner->companyID }}" id="companyID">
                                </div>
                            </div>
                            <div class="mb-3 col-md-6">
                                <label for="activityLicense" class="col-form-label">عکس پروانه فعالیت</label>
                                <div class="col-md-10">
                                    <img class="img-fluid"
                                        src="{{ $owner->activityLicense !== null ? asset($owner->activityLicense) : asset('img/notFound.jpg') }}"
                                        >
                                </div>
                            </div>
                        @else
                            <div class="mb-3 col-md-6">
                                <label for="sanaImage" class="col-form-label">تصویر ثنا</label>
                                <div class="col-md-10">
                                    <img class="img-fluid"
                                        src="{{ $owner->sanaImage !== null ? asset($owner->sanaImage) : asset('img/notFound.jpg') }}"
                                        >
                                </div>
                            </div>
                        @endif
                        <div class="mb-3 col-md-6">
                            <label for="nationalCardImage" class="col-form-label">تصویر کارت ملی</label>
                            <div class="col-md-10">
                                <img class="img-fluid"
                                    src="{{ $owner->nationalCardImage !== null ? asset($owner->nationalCardImage) : asset('img/notFound.jpg') }}"
                                    >
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="nationalFaceImage" class="col-form-label">تصویر کارت ملی کنار
                                چهره</label>
                            <div class="col-md-10">
                                <img class="img-fluid"
                                    src="{{ $owner->nationalFaceImage !== null ? asset($owner->nationalFaceImage) : asset('img/notFound.jpg') }}" >
                            </div>
                        </div>

                    </div>
                    {{-- <div class="mt-2">
                            <button type="submit" class="btn btn-primary me-2">Save changes</button>
                            <button type="reset" class="btn btn-outline-secondary">Cancel</button>
                        </div> --}}
                </div>
                <div class="card-footer">
                    @if (auth()->user()->role == ROLE_ADMIN)
                        @if ($owner->status == 0)
                            <a class="btn btn-primary" href="{{ route('owner.change.status', $owner) }}">فعال</a>
                        @else
                            <a class="btn btn-danger" href="{{ route('owner.change.status', $owner) }}">غیر فعال</a>
                        @endif
                    @endif
                    <a class="btn btn-primary" href="{{ route('ownerAuth.edit', $owner) }}">ویرایش اطلاعات</a>
                    <a class="btn btn-secondary" href="{{ route('owner.loads', $owner->id) }}">لیست بار ها</a>

                </div>
                <!-- /Account -->
            </div>
        </div>
    </div>
@endsection
