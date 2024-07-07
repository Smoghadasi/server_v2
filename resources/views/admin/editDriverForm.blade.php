@extends('layouts.dashboard')

@section('content')

    @php
        $persianAlphabet = ["الف", "ب", "پ", "ت", "ث", "ج", "چ", "‌ح", "خ", "د", "ذ", "ر", "ز", "ژ", "س", " ش", "ص", "ض", "ط", "ظ", "ع", "غ", "ف", "ق", "ک", "گ", "ل", "م", "ن", "و", "ه", "ی"];
    @endphp

    <div class="row">
        <div class="col-xl">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">ویرایش اطلاعات راننده
                        @if ($driver->authLevel == DRIVER_AUTH_UN_AUTH)
                            <span class="badge bg-label-danger"> انجام نشده</span>
                        @elseif ($driver->authLevel == DRIVER_AUTH_SILVER_PENDING)
                            <span class="badge bg-label-secondary border border-danger"><span class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                        @elseif ($driver->authLevel == DRIVER_AUTH_SILVER)
                            <span class="badge bg-label-secondary">سطح نقره ای</span>
                        @elseif ($driver->authLevel == DRIVER_AUTH_GOLD_PENDING)
                        <span class="badge bg-label-warning border border-danger"><span class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                        @elseif ($driver->authLevel == DRIVER_AUTH_GOLD)
                            <span class="badge bg-label-warning">سطح طلایی</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ url('admin/editDriver') }}/{{ $driver->id }}"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                کد هوشمند :
                                <span class="text-danger">*</span>
                            </label>
                            <input id="smartCode" type="text"
                                   placeholder="کدهوشمند"
                                   class="form-control{{ $errors->has('smartCode') ? ' is-invalid' : '' }}"
                                   name="smartCode"
                                   value="{{ $driver->smartCode }}">
                            @if ($errors->has('smartCode'))
                                <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('smartCode') }}</strong>
                                            </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                کدملی :
                                <span class="text-danger">*</span>
                            </label>
                            <input id="nationalCode" type="text"
                                   placeholder="کدملی"
                                   class="form-control{{ $errors->has('nationalCode') ? ' is-invalid' : '' }}"
                                   name="nationalCode"
                                   value="{{ $driver->nationalCode }}"
                                   required>

                            @if ($errors->has('nationalCode'))
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('nationalCode') }}</strong>
                                </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                نام :
                                <span class="text-danger">*</span>
                            </label>
                            <input id="name" type="text"
                                   placeholder="نام"
                                   class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                                   name="name"
                                   value="{{ $driver->name }}"
                                   required autofocus>

                            @if ($errors->has('name'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                نام خانوادگی :
                                <span class="text-danger">*</span>
                            </label>
                            <input id="lastName" type="text"
                                   placeholder="نام خانوادگی"
                                   class="form-control{{ $errors->has('lastName') ? ' is-invalid' : '' }}"
                                   name="lastName"
                                   value="{{ $driver->lastName }}"
                                   required autofocus>

                            @if ($errors->has('lastName'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('lastName') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                شماره موبایل :
                                <span class="text-danger">*</span>
                            </label>
                            <input id="mobileNumber" type="text" placeholder="شماره موبایل"
                                   class="form-control{{ $errors->has('mobileNumber') ? ' is-invalid' : '' }}"
                                   name="mobileNumber"
                                   value="{{ $driver->mobileNumber }}"
                                   required autofocus>

                            @if ($errors->has('mobileNumber'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('mobileNumber') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-fullname">
                                نوع ناوگان :
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="fleet_id">

                                <option value="0">نوع ناوگان</option>

                                @foreach($fleets as $fleet)
                                    <option
                                        @if ($driver->fleet_id == $fleet->id)
                                        selected
                                        @endif

                                        value="{{ $fleet->id }}">
                                        {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                        -
                                        {{ $fleet->title }}
                                    </option>
                                @endforeach

                            </select>

                            @if ($errors->has('fleet_id'))
                                <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fleet_id') }}</strong>
                                    </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">شماره پلاک :</label>

                            <div class="form-group">
                                <div class="row">
                                    <div class="col-md-2 border border-dark border-3 bg-warning">
                                        <div class="text-dark text-center">ایران</div>
                                        <input
                                            style="font-size: 30px"
                                            class="form-control text-center text-dark border-0 bg-warning"
                                            value="{{ $driver->vehicleLicensePlatePartD }}"
                                            placeholder="ایران"
                                            name="vehicleLicensePlatePartD" type="text">
                                    </div>
                                    <div class="col-md-2 border border-dark border-3 bg-warning">
                                        <input
                                            style="height: 60px; font-size: 30px"
                                            class="form-control border-0 text-center text-center text-dark bg-warning"
                                            value="{{ $driver->vehicleLicensePlatePartC }}"
                                            name="vehicleLicensePlatePartC" type="text">
                                    </div>
                                    <div class="col-md-2 border border-dark border-3 bg-warning">
                                        <select
                                            style="height: 60px;font-size: 30px"
                                            class="form-control border-0 text-center text-center text-dark bg-warning"
                                            name="vehicleLicensePlatePartB" type="text">
                                            @foreach($persianAlphabet as $alphabet)
                                                <option
                                                    @if($driver->vehicleLicensePlatePartB == $alphabet)
                                                    selected
                                                    @endif
                                                    value="{{ $alphabet }}">{{ $alphabet }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2 border border-dark border-3 bg-warning">
                                        <input
                                            style="height: 60px;font-size: 30px"
                                            class="form-control border-0 text-center text-center text-dark bg-warning"
                                            value="{{ $driver->vehicleLicensePlatePartA }}"
                                            name="vehicleLicensePlatePartA" type="text">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر راننده :</label>
                            <input type="file" class="form-control" name="driverImage"/>

                            @if(file_exists($driver->driverImage))
                                <img style="max-width: 100px;max-height: 100px;"
                                     src="{{ url($driver->driverImage) }}"/>
                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/driverImage') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر کارت ملی :</label>
                            <input type="file" class="form-control" name="nationalCardImage"/>
                            @if(file_exists($driver->nationalCardImage))
                                <img width="200" src="{{ url($driver->nationalCardImage) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/nationalCardImage') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر کارت هوشمند خودرو :</label>
                            <input type="file" class="form-control" name="carSmartCardImage"/>
                            @if(file_exists($driver->carSmartCardImage))
                                <img width="200" src="{{ url($driver->carSmartCardImage) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/carSmartCardImage') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر راننده و کارت شناسایی :</label>
                            <input type="file" class="form-control" name="authImage"/>
                            @if(file_exists($driver->authImage))
                                <img width="200" src="{{ url($driver->authImage) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/authImage') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر کارت هوشمند راننده :</label>
                            <input type="file" class="form-control" name="driverSmartCardImage"/>

                            @if(file_exists($driver->driverSmartCardImage))
                                <img width="200" src="{{ url($driver->driverSmartCardImage) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/driverSmartCardImage') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر مدرک محل سکونت :</label>
                            <input type="file" class="form-control" name="imageAddressDoc"/>

                            @if(file_exists($driver->imageAddressDoc))
                                <img width="200" src="{{ url($driver->imageAddressDoc) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/imageAddressDoc') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">تصویر ثبت نام برگه ثنا :</label>
                            <input type="file" class="form-control" name="imageRegisterSana"/>

                            @if(file_exists($driver->imageRegisterSana))
                                <img width="200" src="{{ url($driver->imageRegisterSana) }}"/>

                                <a class="btn btn-danger btn-sm"
                                   href="{{ url('admin/removeDriverFile/imageRegisterSana') }}/{{ $driver->id }}">حذف
                                    تصویر</a>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">توضیحات اپراتور :</label>
                            <textarea rows="6" name="operatorMessage" class="form-control"
                                      placeholder="توضیحات اپراتور"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="basic-default-company">آدرس محل سکونت :</label>
                            <textarea rows="6" name="address" class="form-control"
                                      placeholder="آدرس محل سکونت">{{ $driver->address }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">بروز رسانی اطلاعات</button>

                        {{-- @if ($driver->authLevel == DRIVER_AUTH_SILVER_PENDING || $driver->authLevel == DRIVER_AUTH_GOLD_PENDING) --}}
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal"
                                data-bs-target="#acceptLevel_{{ $driver->id }}">
                                تایید وضعیت
                            </button>
                        {{-- @endif --}}

                    </form>
                    <div id="acceptLevel_{{ $driver->id }}" class="modal fade"
                        role="dialog">
                        <div class="modal-dialog">

                            <!-- Modal content-->
                            <form action="{{ route('driver.updateAuthLevel', $driver->id) }}" method="POST" class="modal-content">
                                @csrf
                                @method('PUT')
                                <div class="modal-header">
                                    <h4 class="modal-title">نوع وضعیت:
                                        @if ($driver->authLevel == DRIVER_AUTH_UN_AUTH)
                                            <span class="badge bg-label-danger"> انجام نشده</span>
                                        @elseif ($driver->authLevel == DRIVER_AUTH_SILVER_PENDING)
                                            <span class="badge bg-label-secondary border border-danger"><span class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                                        @elseif ($driver->authLevel == DRIVER_AUTH_SILVER)
                                            <span class="badge bg-label-secondary">سطح نقره ای</span>
                                        @elseif ($driver->authLevel == DRIVER_AUTH_GOLD_PENDING)
                                        <span class="badge bg-label-warning border border-danger"><span class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                                        @elseif ($driver->authLevel == DRIVER_AUTH_GOLD)
                                            <span class="badge bg-label-warning">سطح طلایی</span>
                                        @endif
                                    </h4>
                                </div>
                                <div class="modal-body">
                                    <p>آیا مایل به تایید کردن وضعیت
                                        <span class="text-primary"> {{ $driver->name }}
                                            {{ $driver->lastName }}</span>
                                         هستید؟
                                    </p>
                                    <div class="mb-3">
                                        {{-- <label class="form-label" for="basic-default-company">توضیحات اپراتور :</label> --}}
                                        <textarea rows="6" name="operatorMessage" class="form-control"
                                                  placeholder="توضیحات اپراتور"></textarea>
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
                                    <input type="hidden" name="authLevel" value="{{ $driver->authLevel }}">
                                </div>
                                <div class="modal-footer text-left">
                                    <button type="submit" class="btn btn-primary">
                                        ثبت
                                    </button>
                                    <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">
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









    <div class="card">
        <h5 class="card-header">
            پیام های اپراتورها
        </h5>
        <div class="card-body">
            @foreach($operatorDriverAuthMessages as $operatorDriverAuthMessage)
                <div class="m-2 p-2 alert text-right
                           @if($operatorDriverAuthMessage->close == true)
                    alert-secondary
@else
                    alert-primary
@endif
                    ">
                    <div class="small text-dark">
                        {{ $operatorDriverAuthMessage->operator }}
                        <span class="float-left">
                                            {{ gregorianDateToPersian($operatorDriverAuthMessage->created_at, '-', true) }}
                                        </span>
                    </div>
                    {{ $operatorDriverAuthMessage->message }}
                </div>
            @endforeach
        </div>
    </div>


@stop
