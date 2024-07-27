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
                 <!-- Account -->
                 <div class="card-body">
                    <div class="d-flex align-items-start align-items-sm-center gap-4">
                      <img
                        src="{{ $owner->profileImage !== null ? asset($owner->profileImage) : asset('img/notFound.jpg') }}"
                        alt="user-avatar"
                        class="d-block rounded"
                        height="100"
                        width="100"
                        id="uploadedAvatar"
                      />
                      <div class="button-wrapper">
                        <form action="{{ route('owner.removeProfile', $owner->id) }}" method="POST">
                            @method('delete')
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary account-image-reset mb-4">
                                <i class="bx bx-reset d-block d-sm-none"></i>
                                <span class="d-none d-sm-block">حذف</span>
                              </button>
                        </form>
                      </div>
                    </div>
                  </div>
                  <hr class="my-0" />
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
                                <input class="form-control" type="text" name="address"
                                    value="{{ $owner->address ?? '-' }}" disabled id="address">
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
                        <div class="mb-3 col-md-6">
                            <label for="isAccepted" class="col-form-label">وضعیت تایید</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="isAccepted" disabled
                                    value="{{ $owner->isAccepted == 1 ? 'تایید شده' : 'تایید نشده' }}" id="isAccepted">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="ratingOwner" class="col-form-label">امتیاز</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="ratingOwner" disabled
                                    value="{{ $owner->ratingOwner == null ? 'بدون امتیاز' : $owner->ratingOwner }}" id="ratingOwner">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="sku" class="col-form-label">شناسه</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="sku" disabled
                                    value="{{ $owner->sku == null ? '-' : $owner->sku }}" id="sku">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="description" class="col-form-label">توضیحات </label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="description" disabled
                                    value="{{ $owner->description == null ? '-' : $owner->description }}" id="sku">
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="province_id" class="col-form-label">استان</label>
                            <div class="col-md-12">
                                <input class="form-control" type="text" name="province_id" disabled
                                    value="{{ $owner->province->name ?? '-' }}" id="sku">
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
                                    <a href="{{ $owner->activityLicense !== null ? asset($owner->activityLicense) : asset('img/notFound.jpg') }}" target="_blank">
                                        <img class="img-fluid" width="500"
                                            src="{{ $owner->activityLicense !== null ? asset($owner->activityLicense) : asset('img/notFound.jpg') }}">
                                    </a>
                                </div>
                            </div>
                        @else
                            <div class="mb-3 col-md-6">
                                <label for="sanaImage" class="col-form-label">تصویر ثنا</label>
                                <div class="col-md-10">
                                    <a href="{{ $owner->sanaImage !== null ? asset($owner->sanaImage) : asset('img/notFound.jpg') }}" target="_blank">
                                        <img class="img-fluid" width="500"
                                            src="{{ $owner->sanaImage !== null ? asset($owner->sanaImage) : asset('img/notFound.jpg') }}">
                                    </a>

                                </div>
                            </div>
                        @endif
                        <div class="mb-3 col-md-6">
                            <label for="nationalCardImage" class="col-form-label">تصویر کارت ملی</label>
                            <div class="col-md-10">
                                <a href="{{ $owner->nationalCardImage !== null ? asset($owner->nationalCardImage) : asset('img/notFound.jpg') }}" target="_blank">
                                    <img class="img-fluid" width="500"
                                    src="{{ $owner->nationalCardImage !== null ? asset($owner->nationalCardImage) : asset('img/notFound.jpg') }}">

                                </a>
                            </div>
                        </div>
                        <div class="mb-3 col-md-6">
                            <label for="nationalFaceImage" class="col-form-label">تصویر کارت ملی کنار
                                چهره</label>
                            <div class="col-md-10">
                                <a href="{{ $owner->nationalFaceImage !== null ? asset($owner->nationalFaceImage) : asset('img/notFound.jpg') }}" target="_blank">
                                    <img class="img-fluid" width="500"
                                        src="{{ $owner->nationalFaceImage !== null ? asset($owner->nationalFaceImage) : asset('img/notFound.jpg') }}">
                                </a>
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
                    @if (auth()->user()->role == ROLE_ADMIN)
                        <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                            data-bs-target="#removeOwner_{{ $owner->id }}">حذف
                        </button>
                        <div id="removeOwner_{{ $owner->id }}" class="modal fade" role="dialog">
                            <div class="modal-dialog">

                                <!-- Modal content-->
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">حذف صاحب بار</h4>
                                    </div>
                                    <div class="modal-body">
                                        <p>آیا مایل به حذف صاحب بار
                                            <span class="text-primary">
                                                {{ $owner->name }}{{ $owner->lastName }}</span>
                                            هستید؟
                                        </p>
                                    </div>
                                    <div class="modal-footer text-left">
                                        <form action="{{ route('owner.destroy', $owner) }}" method="post">
                                            @method('DELETE')
                                            @csrf
                                            <button type="submit" class="btn btn-primary">حذف صاحب بار</button>
                                        </form>

                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                            انصراف
                                        </button>
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endif

                </div>
                <!-- /Account -->
            </div>
        </div>
    </div>
@endsection
