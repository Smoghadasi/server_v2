@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    بررسی صاحبان بار {{ $ownerAuth->isOwner == 1 ? '(صاحب بار)' : '(باربری)' }}
                </div>
                <div class="card-body">
                    <form action="{{ route('ownerAuth.update', $ownerAuth) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="mb-3 row">
                            <label for="name" class="col-md-2 col-form-label">نام</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" value="{{ $ownerAuth->name }}" name="name"
                                    id="name">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="lastName" class="col-md-2 col-form-label">نام خانوادگی</label>
                            <div class="col-md-10">
                                <input class="form-control" name="lastName" value="{{ $ownerAuth->lastName }}"
                                    type="text" id="lastName">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="nationalCode" class="col-md-2 col-form-label">کد ملی</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="nationalCode"
                                    value="{{ $ownerAuth->nationalCode }}" id="nationalCode">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="mobileNumber" class="col-md-2 col-form-label">شماره موبایل</label>
                            <div class="col-md-10">
                                <input class="form-control" name="mobileNumber" type="text"
                                    value="{{ $ownerAuth->mobileNumber }}" id="mobileNumber">
                            </div>
                        </div>
                        @if (auth()->user()->role == 'admin' || auth()->user()->id == 29 || auth()->user()->id == 69)
                            <div class="mb-3 row">
                                <label for="isOwner" class="col-md-2 col-form-label">نوع</label>
                                <div class="col-md-10">
                                    <select class="form-select" name="isOwner" id="isOwner">
                                        <option @if ($ownerAuth->isOwner == 1) selected @endif value="1">صاحب بار
                                        </option>
                                        <option @if ($ownerAuth->isOwner == 2) selected @endif value="2">باربری
                                        </option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="isAccepted" class="col-md-2 col-form-label">وضعیت تایید</label>
                                <div class="col-md-10">
                                    <select class="form-select" name="isAccepted" id="isAccepted">
                                        <option @if ($ownerAuth->isAccepted == 0) selected @endif value="0">تایید نشده
                                        </option>
                                        <option @if ($ownerAuth->isAccepted == 1) selected @endif value="1">تایید شده
                                        </option>
                                    </select>
                                </div>
                            </div>
                        @endif
                        <div class="mb-3 row">
                            <label for="postalCode" class="col-md-2 col-form-label">آدرس</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="address" value="{{ $ownerAuth->address }}"
                                    id="address">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="postalCode" class="col-md-2 col-form-label">کد پستی</label>
                            <div class="col-md-10">
                                <input class="form-control" type="text" name="postalCode"
                                    value="{{ $ownerAuth->postalCode }}" id="postalCode">
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="province_id" class="col-md-2 col-form-label">استان</label>
                            <div class="col-md-10">
                                <select class="form-control col-md-4" name="province_id" id="origin_city_id">
                                    <option disabled selected value="0">استان مورد نظر خود را انتخاب کنید</option>
                                    @foreach ($provinces as $province)
                                        <option @if ($province->id == $ownerAuth->province_id) selected @endif
                                            value="{{ $province->id }}">
                                            {{ $province->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="description" class="col-md-2 col-form-label">توضیحات مربوط به صاحب بار</label>
                            <div class="col-md-10">
                                <textarea class="form-control" name="description" rows="6">{{ $ownerAuth->description }}</textarea>
                            </div>
                        </div>
                        @if ($ownerAuth->isOwner == 2)
                            <div class="mb-3 row">
                                <label for="companyName" class="col-md-2 col-form-label">نام شرکت</label>
                                <div class="col-md-10">
                                    <input class="form-control" name="companyName" type="text"
                                        value="{{ $ownerAuth->companyName }}" id="companyName">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="companyID" class="col-md-2 col-form-label">شناسه شرکت</label>
                                <div class="col-md-10">
                                    <input class="form-control" name="companyID" type="text"
                                        value="{{ $ownerAuth->companyID }}" id="companyID">
                                </div>
                            </div>
                            <div class="mb-3 row">
                                <label for="activityLicense" class="col-md-2 col-form-label">عکس پروانه فعالیت</label>
                                <div class="col-md-10">
                                    <a href="{{ asset($ownerAuth->activityLicense) }}">
                                        <img width="250" class="img-fluid"
                                            src="{{ asset($ownerAuth->activityLicense) }}" alt="">
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mb-3 row">
                                <label for="sanaImage" class="col-md-2 col-form-label">تصویر ثنا</label>
                                <input type="file" class="form-control" name="sanaImage" />

                                <div class="col-md-10">
                                    <a href="{{ asset($ownerAuth->sanaImage) }}">
                                        <img width="250" class="img-fluid" src="{{ asset($ownerAuth->sanaImage) }}"
                                            alt="">
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="mb-3 row">
                            <label for="nationalCardImage" class="col-md-2 col-form-label">تصویر کارت ملی</label>
                            <input type="file" class="form-control" name="nationalCardImage" />

                            <div class="col-md-10">
                                <a href="{{ asset($ownerAuth->nationalCardImage) }}">
                                    <img width="250" class="img-fluid"
                                        src="{{ asset($ownerAuth->nationalCardImage) }}" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="nationalFaceImage" class="col-md-2 col-form-label">تصویر کارت ملی کنار
                                چهره</label>
                            <input type="file" class="form-control" name="nationalFaceImage" />

                            <div class="col-md-10">
                                <a href="{{ asset($ownerAuth->nationalFaceImage) }}">
                                    <img width="250" class="img-fluid"
                                        src="{{ asset($ownerAuth->nationalFaceImage) }}" alt="">
                                </a>
                            </div>
                        </div>
                        @if (Auth::user()->role == 'admin')
                            <div class="mb-3 col">
                                <div class="row">
                                    <div class="col">
                                        <label class="form-label" for="basic-default-company">نوتیفیکشن :</label>
                                        <select class="form-control form-select" name="notification" id="">
                                            <option @if ($ownerAuth->notification == 1) selected @endif value="1">فعال
                                            </option>
                                            <option @if ($ownerAuth->notification == 0) selected @endif value="0">غیر
                                                فعال</option>
                                        </select>
                                    </div>
                                    <div class="col">
                                        <label class="form-label" for="basic-default-company">پیامک :</label>
                                        <select class="form-control form-select" name="sms" id="">
                                            <option @if ($ownerAuth->sms == 1) selected @endif value="1">فعال
                                            </option>
                                            <option @if ($ownerAuth->sms == 0) selected @endif value="0">غیر
                                                فعال</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        @endif
                        <button type="submit" class="btn btn-primary">بروز رسانی اطلاعات</button>
                        <button type="button" class="btn btn-success" data-bs-toggle="modal"
                            data-bs-target="#acceptLevel">
                            تایید وضعیت
                        </button>
                    </form>
                    <div id="acceptLevel" class="modal fade" role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <form action="{{ route('owner.updateAuthOwner', $ownerAuth->id) }}" method="POST"
                                class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h4 class="modal-title">بررسی احراز هویت</h4>
                                </div>
                                <div class="modal-body">
                                    <p>آیا مایل به تایید کردن وضعیت
                                        <span class="text-primary"> {{ $ownerAuth->name }}
                                            {{ $ownerAuth->lastName }}</span>
                                        هستید؟
                                    </p>
                                    <div class="mb-3">
                                        {{-- <label class="form-label" for="basic-default-company">توضیحات اپراتور :</label> --}}
                                        <textarea rows="6" required name="operatorMessage" class="form-control" placeholder="توضیحات اپراتور"></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-light fw-semibold d-block">وضعیت</small>
                                        <div class="form-check form-check-inline mt-3">
                                            <input class="form-check-input" type="radio" name="status"
                                                id="inlineRadio1" value="{{ ACCEPT }}" checked />
                                            <label class="form-check-label" for="inlineRadio1">تایید</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status"
                                                id="inlineRadio2" value="{{ REJECT }}" />
                                            <label class="form-check-label" for="inlineRadio2">رد</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer text-left">
                                    <button type="submit" class="btn btn-primary">
                                        ثبت
                                    </button>
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                        انصراف
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
