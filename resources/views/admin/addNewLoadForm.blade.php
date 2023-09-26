@extends('layouts.dashboard')
@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            افزودن بار
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>
    <div class="card-body">
        <form method="POST" id="createNewLoad" action="{{ url('admin/createNewLoad') }}" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="alert alert-info text-right">توجه کادرهای ستاره دار باید حتما وارد شوند</div>
                    <div id="step1" class="steps">

                        <h1 class="step-title pb-5">مشخصات بار</h1>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="smartCode"
                                       class="col-md-4 col-form-label text-md-right">{{ __('عنوان بار') }}<span
                                            style="color: #f00;">*</span></label>
                                <div class="col-md-6">
                                    <input id="title" type="text"
                                           placeholder="عنوان بار"
                                           class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                                           name="title" value="{{ old('title') }}" required>
                                    @if ($errors->has('title'))
                                        <span class="invalid-feedback" role="alert">
                                    <strong>{{ $errors->first('title') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="weight"
                                       class="col-md-4 col-form-label text-md-right">{{ __('وزن بار (تن)') }}<span
                                            style="color: #f00;">*</span></label>

                                <div class="col-md-6">
                                    <input id="weight" type="text"
                                           placeholder="وزن بار"
                                           class="form-control{{ $errors->has('weight') ? ' is-invalid' : '' }}"
                                           name="weight" value="{{ old('weight') }}" required>

                                    @if ($errors->has('weight'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('weight') }}</strong>
                                </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="width"
                                       class="col-md-4 col-form-label text-md-right">{{ __('عرض بار به متر') }}</label>

                                <div class="col-md-6">
                                    <input id="width" type="text"
                                           placeholder="عرض بار  به متر"
                                           class="form-control{{ $errors->has('width') ? ' is-invalid' : '' }}"
                                           name="width" value="{{ old('width') }}">

                                    @if ($errors->has('width'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('width') }}</strong>
                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="length"
                                       class="col-md-4 col-form-label text-md-right">{{ __('طول بار  به متر') }}</label>

                                <div class="col-md-6">
                                    <input id="length" type="text"
                                           placeholder="طول بار  به متر"
                                           class="form-control{{ $errors->has('length') ? ' is-invalid' : '' }}"
                                           name="length" value="{{ old('length') }}">

                                    @if ($errors->has('length'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('length') }}</strong>
                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="height"
                                       class="col-md-4 col-form-label text-md-right">{{ __('ارتفاع بار به متر') }}</label>

                                <div class="col-md-6">
                                    <input id="height" type="text"
                                           placeholder="ارتفاع بار به متر"
                                           class="form-control{{ $errors->has('height') ? ' is-invalid' : '' }}"
                                           name="height" value="{{ old('height') }}">

                                    @if ($errors->has('height'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('height') }}</strong>
                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="senderMobileNumber"
                                       class="col-md-4 col-form-label text-md-right">{{ __('شماره ارسال کننده') }}<span
                                            style="color: #f00;">*</span></label>
                                <div class="col-md-6">
                                    <input id="senderMobileNumber" type="text"
                                           placeholder="شماره ارسال کننده"
                                           class="form-control{{ $errors->has('senderMobileNumber') ? ' is-invalid' : '' }}"
                                           name="senderMobileNumber"
                                           value=""
                                           required
                                           autofocus>
                                    @if ($errors->has('senderMobileNumber'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('senderMobileNumber') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label class="col-md-4 col-form-label text-md-right">{{ __('تاریخ ارسال بار') }}<span
                                            style="color: #f00;">*</span></label>

                                <div class="col-md-6">
                                    <input id="loadingDate" type="text" placeholder="تاریخ ارسال بار"
                                           class="form-control{{ $errors->has('loadingDate') ? ' is-invalid' : '' }}"
                                           name="loadingDate" value="{{ old('loadingDate') }}" required>

                                    @if ($errors->has('loadingDate'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loadingDate') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                @if(isset($bearings))
                                    <label class="col-md-4 col-form-label text-md-right">{{ __('باربری') }}</label>
                                    <div class="col-md-6">
                                        <select id="bearing_id"
                                                class="form-control{{ $errors->has('bearing_id') ? ' is-invalid' : '' }} text-right col-md-12"
                                                name="bearing_id" required>
                                            <option value="0" onclick="a();">باربری</option>
                                            <option value="0">اپراتور</option>

                                            @foreach($bearings as $bearing)
                                                <option value="{{ $bearing->id }}" class="text-right"
                                                        style="text-align: right"
                                                ><?php
                                                    echo str_replace('ك', 'ک', str_replace('ي', 'ی', $bearing->title)).' - '.$bearing->mobileNumber;
                                                    ?></option>
                                            @endforeach

                                        </select>
                                        @if ($errors->has('loadingDate'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loadingDate') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                @elseif(isset($customers))
                                    <label class="col-md-4 col-form-label text-md-right">{{ __('صاحب بار') }}</label>
                                    <div class="col-md-6">
                                        <select id="customer_id"
                                                class="form-control{{ $errors->has('customer_id') ? ' is-invalid' : '' }} text-right col-md-12"
                                                name="customer_id" required>
                                            <option value="0">صاحب بار</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}" class="text-right"
                                                        style="text-align: right"><?php
                                                    echo str_replace('ك', 'ک', str_replace('ي', 'ی', $customer->name)) . ' ' . str_replace('ك', 'ک', str_replace('ي', 'ی', $customer->lastName)).' - '.$customer->mobileNumber;
                                                    ?></option>
                                            @endforeach
                                        </select>
                                        @if ($errors->has('loadingDate'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loadingDate') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="form-group row">
                                <label for="loadingHour"
                                       class="col-md-4 col-form-label text-md-right">{{ __('دقیقه و ساعت') }}<span
                                            style="color: #f00;">*</span></label>
                                <div class="col-md-3">
                                    <select id="loadingMinute"
                                            class="form-control{{ $errors->has('loadingMinute') ? ' is-invalid' : '' }} text-center"
                                            name="loadingMinute" required>
                                        <option value="minute">دقیقه</option>
                                        @for($minute=0;$minute<60;$minute++)
                                            <option value="{{ $minute }}">
                                                @if($minute<10) 0{{ $minute }} @else {{ $minute }} @endif
                                            </option>
                                        @endfor
                                    </select>

                                    @if ($errors->has('loadingMinute'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loadingMinute') }}</strong>
                                    </span>
                                    @endif
                                </div>
                                :
                                <div class="col-md-3">

                                    <select id="loadingHour"
                                            class="form-control{{ $errors->has('loadingHour') ? ' is-invalid' : '' }} text-center"
                                            name="loadingHour" required>
                                        <option value="hour">ساعت</option>
                                        @for($hour=0;$hour<24;$hour++)
                                            <option value="{{ $hour }}">
                                                @if($hour<10) 0{{ $hour }} @else {{ $hour }} @endif
                                            </option>
                                        @endfor
                                    </select>

                                    @if ($errors->has('loadingHour'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('loadingHour') }}</strong>
                                    </span>
                                    @endif
                                </div>

                            </div>

                            <div class="form-group row">
                                <label for="dischargeTime"
                                       class="col-md-4 col-form-label text-md-right">{{ __('زمان تخلیه') }}</label>
                                <div class="col-md-6">
                                    <input id="dischargeTime" type="hidden" name="dischargeTime" value="day">

                                    <button type="button" id="dischargeTimeDay"
                                            class="dischargeTimeItem dischargeTimeDay dischargeTimeSelected">تخلیه در
                                        روز
                                    </button>
                                    <button type="button" id="dischargeTimeNight"
                                            class="dischargeTimeItem dischargeTimeNight dischargeTimeNoSelected">تخلیه
                                        در شب
                                    </button>

                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 offset-md-4 text-center p-5">
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(1,2);">
                                {{ __('بعدی >') }}
                            </button>
                        </div>
                    </div>

                    <div id="step2" class="steps">
                        <h1 class="step-title pb-5" style="">آدرس بارگیری و تخلیه بار</h1>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="city_id"
                                       class="col-md-4 col-form-label text-md-right">{{ __('شهر بارگیری') }}<span
                                            style="color: #f00;">*</span></label>
                                <div class="col-md-6">
                                    <select id="origin_city_id"
                                            class="form-control{{ $errors->has('origin_city_id') ? ' is-invalid' : '' }}"
                                            name="origin_city_id" required>
                                        <option value="0">شهر بارگیری</option>
                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}"><?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?></option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('origin_city_id'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('origin_city_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="loadingAddress"
                                       class="col-md-4 col-form-label text-md-right">{{ __('آدرس بارگیری') }}<span
                                            style="color: #f00;">*</span></label>

                                <div class="col-md-6">
                        <textarea id="loadingAddress"
                                  placeholder="آدرس بارگیری"
                                  class="form-control{{ $errors->has('height') ? ' is-invalid' : '' }}"
                                  name="loadingAddress" value="{{ old('loadingAddress') }}" required></textarea>

                                    @if ($errors->has('loadingAddress'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('loadingAddress') }}</strong>
                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label for="destination_city_id"
                                       class="col-md-4 col-form-label text-md-right">{{ __('شهر تخلیه') }}<span
                                            style="color: #f00;">*</span></label>

                                <div class="col-md-6">
                                    <select id="destination_city_id"
                                            class="form-control{{ $errors->has('destination_city_id') ? ' is-invalid' : '' }} text-right col-md-12"
                                            name="destination_city_id" required>
                                        <option value="0">شهر تخلیه</option>

                                        @foreach($cities as $city)
                                            <option value="{{ $city->id }}" class="text-right"
                                                    style="text-align: right"><?php
                                                echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name));
                                                ?></option>
                                        @endforeach

                                    </select>

                                    @if ($errors->has('destination_city_id'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('destination_city_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <label for="dischargeAddress"
                                       class="col-md-4 col-form-label text-md-right">{{ __('آدرس تخلیه') }}<span
                                            style="color: #f00;">*</span></label>

                                <div class="col-md-6">
                        <textarea id="dischargeAddress"
                                  placeholder="آدرس تخلیه"
                                  class="form-control{{ $errors->has('dischargeAddress') ? ' is-invalid' : '' }}"
                                  name="dischargeAddress" value="{{ old('dischargeAddress') }}" required></textarea>

                                    @if ($errors->has('dischargeAddress'))
                                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('dischargeAddress') }}</strong>
                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12 offset-md-4 text-center p-5">
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(2,1);">
                                {{ __('< قبلی') }}
                            </button>
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(2,3);">
                                {{ __('بعدی >') }}
                            </button>
                        </div>
                    </div>

                    <div id="step3" class="steps">

                        <h1 class="step-title pb-5">انتخاب نوع ناوگان، بسته بندی و تعداد خودرو</h1>

                        <div class="text-center">
                            <div class="form-group d-inline-block text-center">
                                <div class="d-inline-block text-center col-md-12">{{ __('نوع بسته بندی') }}<span
                                            style="color: #f00;">*</span></div>
                                <div class="card m-3 p-3 row d-inline-block">
                                    <div id="packingType" class="text-center">
                                        <img src="{{ url('/assets/img/package.svg') }}" width="128" height="128"
                                             id="packingTypesPic">
                                        <h5 id="packingTypeTitle" class="font-weight-bold m-3 text-center">نوع بسته
                                            بندی</h5>
                                    </div>
                                    <div id="packingTypeModal" class="modal">

                                        <div class="card z-1 shadow" id="packingTypeMenu">
                                            <h5 class="text-center p-2">نوع بسته بندی</h5>
                                            @foreach($packingTypes as $packingType)
                                                <div class="p-1 menuItem"
                                                     onclick="selectPackingType('{{ $packingType->id }}','{{ $packingType->title }}','{{ url($packingType->pic) }}');">
                                                    <img src="{{ url($packingType->pic) }}" width="50" height="50"
                                                         class="pull-right d-inline-block img-thumbnail">
                                                    <div class="d-inline-block font-weight-bold mr-3">{{ $packingType->title }}</div>
                                                </div>
                                            @endforeach

                                        </div>
                                        <span id="closePackingType" class="closeModel">بستن</span>
                                    </div>
                                    <input type="hidden" value="0" name="packing_type_id" id="packing_type_id">
                                    @if ($errors->has('fleet_id'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fleet_id') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group d-inline-block text-center">
                                <div class="d-inline-block text-center col-md-12">{{ __('نوع ناوگان') }}<span
                                            style="color: #f00;">*</span></div>
                                <div class="card m-3 p-3 row d-inline-block">
                                    <div id="fleetType" class="text-center">
                                        <img src="{{ url('/assets/img/truck.svg') }}"
                                             style="max-height: 128px; max-width: 128px;"
                                             id="fleetTypesPic">
                                        <h5 class="font-weight-bold m-3 text-center" id="fleetTypeTitle">
                                            عنوان ناوگان
                                        </h5>
                                    </div>
                                </div>

                                <div id="fleetTypeModal" class="modal">

                                    <div class="card z-1 shadow mb-5" id="fleetTypeMenu">
                                        <h5 class="text-center p-2">نوع ناوگان</h5>
                                        @foreach($fleets as $fleet)
                                            <div class="card-body p-1 menuItem"
                                                 onclick="selectFleetType('{{ $fleet->id }}','{{ $fleet->title }}','{{ url($fleet->pic) }}');">
                                                <img src="{{ url($fleet->pic) }}" width="50" height="50"
                                                     class="pull-right d-inline-block img-thumbnail">
                                                <span class="d-inline-block font-weight-bold mr-3">
                                                    {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                                    - {{ $fleet->title }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <span id="closeFleetType" class="closeModel">بستن</span>
                                </div>

                                <input type="hidden" value="0" name="fleet_id" id="fleet_id">
                                @if ($errors->has('fleet_id'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fleet_id') }}</strong>
                                    </span>
                                @endif
                            </div>

                            <div class="form-group text-center">
                                <div class="card d-inline-block">
                                    <div class="d-inline-block text-center pr-2">{{ __('تعداد خودرو') }}<span
                                                style="color: #f00;">*</span></div>
                                    <div class="m-3 p-3 row d-inline-block">
                                        <input id="numOfTrucks" type="text" placeholder="تعداد خودرو"
                                               class="form-control{{ $errors->has('numOfTrucks') ? ' is-invalid' : '' }} number text-center"
                                               name="numOfTrucks" value="{{ old('numOfTrucks') }}" required>
                                    </div>
                                    @if ($errors->has('numOfTrucks'))
                                        <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('numOfTrucks') }}</strong>
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-12 offset-md-4 text-center p-5">
                                <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(3,2);">
                                    {{ __('< قبلی') }}
                                </button>
                                <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(3,4);">
                                    {{ __('بعدی >') }}
                                </button>
                            </div>
                        </div>


                        <input type="hidden" name="loadMode" value="outerCity">

                    </div>

                    <div id="step4" class="steps">
                        <h1 class="step-title pb-5">مبلغ بیمه، کرایه پیشنهادی و توضیحات</h1>
                        <div class="form-group row">
                            <label for="insuranceAmount"
                                   class="col-md-4 col-form-label text-md-right">{{ __('مبلغ بیمه به تومان (مبلغ بیمه معادل ارزش بار می باشد)') }}
                                <span style="color: #f00;">*</span></label>
                            <div class="col-md-6">
                                <input id="insuranceAmount" type="text" placeholder="مبلغ بیمه به تومان"
                                       class="form-control{{ $errors->has('insuranceAmount') ? ' is-invalid' : '' }} number"
                                       name="insuranceAmount" value="{{ old('insuranceAmount') }}" required>
                                @if ($errors->has('insuranceAmount'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('insuranceAmount') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="insuranceAmount"
                                   class="col-md-4 col-form-label text-md-right">{{ __('کرایه پیشنهادی به راننده') }}
                                <span style="color: #f00;">*</span></label>
                            <div class="col-md-6">
                                <input id="proposedPriceForDriver" type="text" placeholder="کرایه پیشنهادی به راننده"
                                       class="form-control{{ $errors->has('proposedPriceForDriver') ? ' is-invalid' : '' }} number"
                                       name="proposedPriceForDriver" value="{{ old('proposedPriceForDriver') }}"
                                       required>
                                @if ($errors->has('proposedPriceForDriver'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('proposedPriceForDriver') }}</strong>
                                    </span>
                                @endif
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="suggestedPrice"
                                   class="col-md-4 col-form-label text-md-right">{{ __('مبلغ پیشنهادی به تومان') }}</label>
                            <div class="col-md-6">
                                <input id="suggestedPrice" type="text" placeholder="مبلغ پیشنهادی به تومان"
                                       class="form-control{{ $errors->has('suggestedPrice') ? ' is-invalid' : '' }} number"
                                       name="suggestedPrice" value="{{ old('suggestedPrice') }}">
                                @if ($errors->has('suggestedPrice'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('suggestedPrice') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="form-group row">
                            <label for="suggestedPrice"
                                   class="col-md-4 col-form-label text-md-right">{{ __('توضیحات') }}</label>
                            <div class="col-md-6">
                                <textarea id="description"
                                          placeholder="توضیحات"
                                          class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}"
                                          name="description" value="{{ old('description') }}"></textarea>
                                @if ($errors->has('description'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('description') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-12 offset-md-4 text-center p-5">
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(4,3);">
                                {{ __('< قبلی') }}
                            </button>
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(4,5);">
                                {{ __('بعدی >') }}
                            </button>
                        </div>
                    </div>

                    <div id="step5" class="steps text-center">

                        <h1 class="step-title pb-5">تصویر بار</h1>
                        <div class="alert alert-info d-inline-block">تصویر مربوط به بار خود را در صورت نیاز
                            انتخاب نمایید
                        </div>
                        <div class="form-group col-sm-12">
                            <img src="{{url('assets/img/add_pic.svg')}}" style="max-width: 256px; max-height: 256px;"
                                 id="selected-pic" class="mb-2"><br>
                            <div type="button" id="selected-pic-button" class="btn btn-primary">انتخاب عکس</div>
                            <div type="button" id="remove-pic-button" class="btn btn-danger">حذف عکس</div>

                            <input id="pic" type="file" name="pic" value="{{ old('pic') }}">
                        </div>
                        <div class="col-md-12 offset-md-4 text-center p-5">
                            <button type="button" class="btn btn-primary stepsButtons" onclick="showStep(5,4);">
                                {{ __('< قبلی') }}
                            </button>
                            <button type="submit" class="btn btn-primary m-5 stepsButtons">
                                {{ __('ثبت بار جدید') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script src="{{ url('/assets/js/addNewLoadActions.js') }}"></script>

@stop
