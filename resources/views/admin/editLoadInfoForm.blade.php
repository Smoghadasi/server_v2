@extends('layouts.dashboard')

@section('content')


    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            ویرایش اطلاعات بار
        </li>
    </ol>


    <div class="card-body">
        <form method="POST" id="createNewLoad" action="{{ url('admin/editLoadInfo') }}/{{ $load->id }}"
              enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">
                    <div class="card m-2 shadow">
                        <div id="cargoForm" class="m-2">

                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5">
                                    چه باری دارید؟ (اختیاری)
                                </label>
                                <input id="title" type="text"
                                       value="{{ $load->title }}"
                                       placeholder="عنوان بار (مثال : مواد اولیه پلاستیک، سیمان و...)"
                                       class="form-control text-center" name="title" required>
                            </div>

                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5 col-lg-12">
                                    مبدا :
                                </label>
                                <select id="origin_city_id"
                                        class="form-control"
                                        style="width: 100%;"
                                        name="origin_city_id" required>
                                    <option value="0">مبدا</option>
                                    @foreach($cities as $city)
                                        <option
                                            @if($load->origin_city_id == $city->id)
                                            selected
                                            @endif
                                            value="{{ $city->id }}"><?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?></option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5 col-lg-12">
                                    مقصد :
                                </label>
                                <select id="destination_city_id"
                                        class="form-control text-right"
                                        style="width: 100%;"
                                        name="destination_city_id" required>
                                    <option value="0">مقصد</option>

                                    @foreach($cities as $city)
                                        <option
                                            @if($load->destination_city_id == $city->id)
                                            selected
                                            @endif
                                            value="{{ $city->id }}" class="text-right"
                                            style="text-align: right"><?php
                                            echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name));
                                            ?></option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group col-lg-3 mt-2">
                                <label for="mobileNumberForCoordination" class="text-right h5 col-lg-12">
                                    شماره تماس جهت هماهنگی :
                                </label>

                                <input id="mobileNumberForCoordination" type="text"
                                       placeholder="شماره تماس جهت هماهنگی"
                                       class="form-control"
                                       name="mobileNumberForCoordination"
                                       value="{{ $load->mobileNumberForCoordination }}"
                                       required autofocus>
                                <input type="hidden" name="senderMobileNumber"
                                       value="{{ $load->senderMobileNumber }}">
                            </div>


                            <div class="col-lg-12 mt-4 border-top border-dark p-2 pt-3 ml-2">
                                <div class="form-group">
                                    <label for="title" class="text-right h5 col-lg-12 mb-2">
                                        انتخاب ناوگان پیشنهادی :
                                    </label>

                                    <div id="fleetType" class="border rounded col-lg-4">
                                        <img src="{{ url('/assets/img/truck.svg') }}"
                                             style="max-height: 64px; max-width: 64px;"
                                             id="fleetTypesPic" class="mr-3">
                                        <span class="font-weight-bold m-3 text-center" id="fleetTypeTitle">
                                            عنوان ناوگان
                                        </span>
                                    </div>

                                    <div class="form-group col-lg-4 row text-center m-3">
                                        <span class="mt-2 h5">تعداد ناوگان مورد نیاز : </span>
                                        <input id="numOfFleets" type="text" placeholder="تعداد" value="1"
                                               class="form-control number text-center col-lg-2 ml-2 mr-2"
                                               name="numOfFleets">
                                        <span class="mt-2 h5">دستگاه</span>
                                        <input type="hidden" name="numOfTrucks" value="0">

                                    </div>

                                    <button type="button" class="btn btn-primary mt-2" id="addToSelectedFleetsList">
                                        اضافه به لیست ناوگان
                                    </button>

                                    <div class="mt-3 border-top">
                                        <table class="table">
                                            <thead>
                                            <tr>
                                                <th>ردیف</th>
                                                <th>عنوان</th>
                                                <th>تعداد</th>
                                                <th>حذف</th>
                                            </tr>
                                            </thead>
                                            <tbody id="selectedFleetsList" class="text-right">
                                            @php $i = 0; @endphp

                                            @if($load->fleets!=null)
                                                @foreach(json_decode($load->fleets) as $selectedFleet)
                                                    @if($selectedFleet->userType == ROLE_ADMIN || $selectedFleet->userType == ROLE_OPERATOR || $selectedFleet->userType == ROLE_TRANSPORTATION_COMPANY)
                                                        <tr>
                                                            <td>{{ ++$i }}</td>
                                                            <td>{{ $selectedFleet->title }}</td>
                                                            <td>{{ $selectedFleet->numOfFleets }}</td>
                                                            <td>
                                                                <button class='btn btn-sm btn-danger' type='button'
                                                                        onclick='removeSelectedFleet({{ $selectedFleet->fleet_id }})'>
                                                                    حذف
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    <input type="hidden" name="fleetList" id="fleetListArray"
                                           value="[{{ $fleetListArray }}]">

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


                                </div>
                                <input type="hidden" name="loadMode" value="outerCity">

                            </div>


                            <div class="form-group col-lg-12 mt-4 p-2  pt-3 border-top border-dark">
                                <label class="text-right h5 col-lg-12">
                                    انتخاب نوع قیمت :
                                </label>

                                <div>
                                    <label class="text-right d-inline">
                                        <input type="radio" name="priceType"
                                               @if($load->suggestedPrice == 0)
                                               checked
                                               @endif
                                               id="agreedPrice">
                                        قیمت توافقی
                                    </label>
                                    <label class="text-right mr-3 d-inline">
                                        <input type="radio" name="priceType"
                                               @if($load->suggestedPrice > 0)
                                               checked
                                               @endif
                                               id="proposedPrice">
                                        قیمت پیشنهادی
                                    </label>
                                </div>
                                <div class="form-group col-lg-12 mt-2" id="suggestedPriceForm"
                                     @if($load->suggestedPrice == 0)
                                     style="display: none"
                                    @endif>

                                    <h5 class="text-right">
                                        قیمت پیشنهادی :
                                    </h5>

                                    <div class="form-group row col-lg-4 p-2">
                                        <label class="text-right">
                                            <input type="radio" checked name="priceBased"
                                                   @if($load->priceBased == "به ازای هر تن")
                                                   checked
                                                   @endif
                                                   value="به ازای هر تن">
                                            به ازای هر تن
                                        </label>
                                        <label class="text-right mr-3">
                                            <input type="radio" name="priceBased"
                                                   @if($load->priceBased == "به ازای کل بار")
                                                   checked
                                                   @endif
                                                   value="به ازای کل بار">
                                            به ازای کل بار
                                        </label>

                                        @if(auth('customer')->check())
                                            <input id="suggestedPrice" type="hidden" name="suggestedPrice" value="0">
                                        @else
                                            <input id="suggestedPrice" value="{{ $load->suggestedPrice }}" type="text"
                                                   placeholder="کرایه پیشنهادی (تومان)"
                                                   onkeypress="handle(event)"
                                                   class="form-control" name="suggestedPrice">
                                        @endif
                                    </div>
                                </div>
                            </div>


                            <div class="col-lg-12 mt-4 p-2  pt-3 border-top border-dark"></div>

                            <input type="hidden" name="loadMode" value="outerCity">


                            <div class="form-group col-lg-6 mt-2">
                                <label for="description" class="text-right">
                                <span class="h5">
                                    توضیحات :
                                </span>
                                    <span class="text-danger small">اختیاری</span>
                                </label>
                                <textarea id="description" placeholder="توضیحات مورد نظر خود را وارد نمایید"
                                          class="form-control text-right"
                                          name="description">{{ $load->description }}</textarea>
                            </div>

                            <div class="form-group col-lg-4 mt-2">
                                <label for="weight" class="text-right h5">
                                    بار شما چند تن است؟
                                    <span class="text-danger small">اختیاری</span>
                                </label>

                                <div>
                                    <label class="col-sm-3">
                                        <input type="checkbox"
                                               @if($load->weight==-1)
                                               checked
                                               @endif
                                               id="tonaje" name="tonaje" onclick="setFreeTonaje()">
                                        تناژ آزاد
                                    </label>
                                    <input type="number" onkeypress="handle(event)" placeholder="وزن"
                                           value="{{ $load->weight }}"
                                           @if($load->weight==-1)
                                           style="display: none"
                                           @endif
                                           class="form-control col-sm-9 text-center" name="weight"
                                           id="weight">
                                </div>
                            </div>


                            <div class="col-lg-12 border-top border-primary">
                                <div class="col-lg-12 mb-3 text-center pt-3 btn"
                                     data-toggle="collapse"
                                     data-target="#collapseFormA" aria-expanded="false" aria-controls="collapseFormA">
                                    پر کردن اطلاعات زیر
                                    <span class="text-danger">اختیاری</span>
                                    است
                                    <span class="fa fa-chevron-down"></span>
                                </div>
                            </div>


                            <div class="collapse" id="collapseFormA">
                                <div class="form-group col-lg-6 mt-2">
                                    <label class="text-right h5">
                                        انتخاب نوع بسته بندی :
                                    </label>
                                    <div class="border rounded">
                                        <div id="packingType" class="text-center row">

                                            @if($load->packing_type_id > 0)
                                                @foreach($packingTypes as $packingType)
                                                    @if($load->packing_type_id == $packingType->id)
                                                        <img src="{{ url($packingType->pic) }}" width="28" height="28"
                                                             id="packingTypesPic" class="mr-4 mt-1 mb-1">
                                                        <span id="packingTypeTitle" class="mt-1 mr-3 text-center">
                                                    {{ $packingType->title }}
                                                </span>
                                                        @break
                                                    @endif
                                                @endforeach
                                            @else

                                                <img src="{{ url('/assets/img/package.svg') }}" width="28" height="28"
                                                     id="packingTypesPic" class="mr-4 mt-1 mb-1">
                                                <span id="packingTypeTitle" class="mt-1 mr-3 text-center">نوع بسته
                                                بندی</span>
                                            @endif

                                        </div>
                                        <div id="packingTypeModal" class="modal">

                                            <div class="card z-1 shadow" id="packingTypeMenu">
                                                <h5 class="text-center p-2">نوع بسته بندی</h5>
                                                @foreach($packingTypes as $packingType)
                                                    <div class="p-1 menuItem"
                                                         onclick="selectPackingType('{{ $packingType->id }}','{{ $packingType->title }}','{{ url($packingType->pic) }}');">
                                                        <img src="{{ url($packingType->pic) }}" width="50" height="50"
                                                             class="pull-right d-inline-block img-thumbnail">
                                                        <div
                                                            class="d-inline-block font-weight-bold mr-3">{{ $packingType->title }}</div>
                                                    </div>
                                                @endforeach

                                            </div>
                                            <span id="closePackingType" class="closeModel">بستن</span>
                                        </div>
                                        <input type="hidden" value="{{ $load->packing_type_id }}" name="packing_type_id"
                                               id="packing_type_id">
                                        @if ($errors->has('fleet_id'))
                                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('fleet_id') }}</strong>
                                    </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="width" class="text-right h5">
                                        عرض بار (متر) :
                                    </label>
                                    <input type="number"
                                           placeholder="عرض"
                                           value="{{ $load->width }}"
                                           class="form-control text-center" name="width">
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="length" class="text-right h5"> طول بار (متر) : </label>
                                    <input type="number" placeholder="طول"
                                           value="{{ $load->length }}"
                                           class="form-control text-center" name="length">
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="height" class="text-right h5"> ارتفاع بار (متر) : </label>
                                    <input type="number" placeholder="ارتفاع"
                                           value="{{ $load->height }}"
                                           class="form-control text-center" name="height">
                                </div>

                                <div class="form-group col-lg-6 mt-2">

                                    <label for="loadingDate" class="text-right h5">
                                        تاریخ بارگیری :
                                    </label>

                                    <input id="loadingDate" type="text" placeholder="تاریخ ارسال بار"
                                           class="form-control datepicker"
                                           value="{{ persianDateToGregorian($load->loadingDate) }}"
                                           name="loadingDate" required>
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="loadingAddress" class="text-right h5">
                                        آدرس بارگیری :
                                    </label>

                                    <textarea id="loadingAddress"
                                              placeholder="آدرس بارگیری"
                                              class="form-control"
                                              name="loadingAddress">{{ $load->loadingAddress }}</textarea>
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="dischargeAddress" class="text-right h5">
                                        آدرس تخلیه :
                                    </label>

                                    <textarea id="dischargeAddress"
                                              placeholder="آدرس تخلیه"
                                              class="form-control"
                                              name="dischargeAddress">{{ $load->dischargeAddress }}</textarea>
                                </div>

                                <div class="form-group col-lg-6 mt-2">
                                    <label for="loadingHour" class="text-right h5 col-lg-12">
                                        ساعت بارگیری :
                                    </label>

                                    <select id="loadingHour"
                                            class="form-control col-lg-3 text-center"
                                            name="loadingHour" required>
                                        <option value="hour">ساعت</option>
                                        @for($hour=0;$hour<24;$hour++)
                                            <option
                                                @if($load->loadingHour == $hour)
                                                selected
                                                @endif
                                                value="{{ $hour }}">
                                                @if($hour<10) 0{{ $hour }} @else {{ $hour }} @endif
                                            </option>
                                        @endfor
                                    </select>
                                    <div class="col-lg-1">:</div>
                                    <select id="loadingMinute"
                                            class="form-control text-center col-lg-3"
                                            name="loadingMinute" required>
                                        <option value="minute">دقیقه</option>
                                        @for($minute=0;$minute<60;$minute++)
                                            <option
                                                @if($load->loadingMinute == $hour)
                                                selected
                                                @endif
                                                value="{{ $minute }}">
                                                @if($minute<10) 0{{ $minute }} @else {{ $minute }} @endif
                                            </option>
                                        @endfor
                                    </select>
                                </div>

                                <div class="form-group col-lg-12 mt-2">
                                    <label for="insuranceAmount" class="text-right h5 col-lg-12">
                                        مبلغ بیمه (تومان) :
                                        <span class="small text-black-50">مبلغ بیمه معادل ارزش بار می باشد.</span>
                                    </label>

                                    <input id="insuranceAmount" type="text"
                                           placeholder="مبلغ بیمه معادل ارزش بار می باشد"
                                           class="form-control"
                                           value="{{ $load->insuranceAmount }}"
                                           name="insuranceAmount">
                                </div>


                                <div class="form-group col-lg-12 mt-2">
                                    <label for="insuranceAmount" class="text-right h5 col-lg-12">
                                        تصویر بار خود را ارسال نمایید :
                                    </label>

                                    <div class="mt-2">
                                        <img
                                            @if(strlen($load->loadPic))
                                            src="{{url($load->loadPic)}}"
                                            @else
                                            src="{{url('assets/img/add_pic.svg')}}"
                                            @endif
                                            style="max-width: 256px; max-height: 256px;"
                                            id="selected-pic" class="mb-2"><br>
                                        <div type="button" id="selected-pic-button" class="btn btn-primary">انتخاب عکس
                                        </div>
                                        <div type="button" id="remove-pic-button" class="btn btn-danger">حذف عکس</div>

                                        <input id="pic" type="file" class="d-none" name="pic"
                                               value="{{ $load->loadPic }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if(auth('bearing')->check())
                            <input type="hidden" name="userType" value="transportation_company">
                        @elseif (auth('customer')->check())
                            <input type="hidden" name="userType" value="user">
                        @endif

                        <button class="btn btn-primary m-2 p-2" style="font-size:25px;" type="submit">ثبت اطلاعات جدید
                        </button>

                    </div>
                </div>
            </div>
        </form>

    </div>

    <script>
        $("#proposedPrice").click(function () {
            $("#suggestedPriceForm").slideDown();
        });

        function setFreeTonaje() {

            if ($("#tonaje").is(":checked")) {
                $("#weight").val(-1);
            } else {
                $("#weight").val(0);
            }
            $("#weight").fadeToggle();

        }
    </script>
    <script src="{{ asset('/assets/js/editNewLoadActions.js') }}"></script>

@stop
