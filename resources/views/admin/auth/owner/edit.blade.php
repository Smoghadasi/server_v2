@extends('layouts.dashboard')

@section('content')
    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    بررسی صاحبان بار {{ $ownerAuth->isOwner == 1 ? '(صاحب بار)' : '(باربری)' }}
                </div>
                <div class="card-body">
                    <form action="{{ route('ownerAuth.update', $ownerAuth) }}" method="post">
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
                        <div class="mb-3 row">
                            <label for="isOwner" class="col-md-2 col-form-label">نوع</label>
                            <div class="col-md-10">
                                <select class="form-select" name="isOwner" id="isOwner">
                                    <option @if($ownerAuth->isOwner == 1) selected @endif value="1">صاحب بار</option>
                                    <option @if($ownerAuth->isOwner == 2) selected @endif value="2">باربری</option>
                                </select>
                            </div>
                        </div>
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
                                        <img width="250"  class="img-fluid" src="{{ asset($ownerAuth->activityLicense) }}" alt="">
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mb-3 row">
                                <label for="sanaImage" class="col-md-2 col-form-label">تصویر ثنا</label>
                                <div class="col-md-10">
                                    <a href="{{ asset($ownerAuth->sanaImage) }}">
                                        <img width="250" class="img-fluid" src="{{ asset($ownerAuth->sanaImage) }}" alt="">
                                    </a>
                                </div>
                            </div>
                        @endif
                        <div class="mb-3 row">
                            <label for="nationalCardImage" class="col-md-2 col-form-label">تصویر کارت ملی</label>
                            <div class="col-md-10">
                                <a href="{{ asset($ownerAuth->nationalCardImage) }}">
                                    <img width="250" class="img-fluid" src="{{ asset($ownerAuth->nationalCardImage) }}" alt="">
                                </a>
                            </div>
                        </div>
                        <div class="mb-3 row">
                            <label for="nationalFaceImage" class="col-md-2 col-form-label">تصویر کارت ملی کنار چهره</label>
                            <div class="col-md-10">
                                <a href="{{ asset($ownerAuth->nationalFaceImage) }}">
                                    <img width="250" class="img-fluid" src="{{ asset($ownerAuth->nationalFaceImage) }}" alt="">
                                </a>
                            </div>
                        </div>
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
