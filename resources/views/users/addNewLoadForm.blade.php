<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth()->check() && (auth()->user()->role == ROLE_ADMIN || auth()->user()->role == ROLE_OPERATOR))
    <?php $dashboard = 'layouts.dashboard';?>
@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif


@extends($dashboard)


@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            افزودن بار
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>

    @if(isset($message) && count($message))
        <div class="alert alert-success text-right">
            @foreach($message as $item)
                {{ $item }}
            @endforeach
        </div>
    @endif

    <div class="card-body">
        <form method="POST" id="createNewLoad"
              @if($userType==ROLE_ADMIN || $userType==ROLE_OPERATOR) action="{{ url('admin/createNewLoad') }}"
              @else action="{{ url('user/createNewLoad') }}" @endif enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-lg-12">

                    <div class="card m-3 shadow">
                        <div id="cargoForm" class="m-2">

                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5">
                                    چه باری دارید؟ (اختیاری)
                                </label>
                                <input id="title" type="text"
                                       autofocus
                                       onkeypress="handle(event)"
                                       placeholder="عنوان بار (مثال : مواد اولیه پلاستیک، سیمان و...)"
                                       class="form-control text-center" name="title">
                            </div>


                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5 col-lg-12">
                                    مبدا :
                                </label>

                                <select id="origin_city_id" class="form-control" style="width: 100%;"
                                        name="origin_city_id">
                                    <option value="0">مبدا</option>
                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}">
                                            {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $city->state)) }}
                                            - {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group col-lg-3 mt-2">
                                <label for="title" class="text-right h5 col-lg-12">
                                    مقصد :
                                </label>
                                <select id="destination_city_id" class="form-control text-right" style="width: 100%;"
                                        name="destination_city_id">
                                    <option value="0">مقصد</option>

                                    @foreach($cities as $city)
                                        <option value="{{ $city->id }}" class="text-right" style="text-align: right">
                                            {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $city->state)) }}
                                            - {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)) }}
                                        </option>
                                    @endforeach

                                </select>
                            </div>

                            <div class="form-group col-lg-3 mt-2">
                                <label for="mobileNumberForCoordination" class="text-right h5 col-lg-12">
                                    شماره تماس جهت هماهنگی :
                                </label>

                                <input id="mobileNumberForCoordination" type="text" placeholder="شماره تماس جهت هماهنگی"
                                       onkeypress="handle(event)"
                                       class="form-control" name="mobileNumberForCoordination"
                                       autofocus>
                                <input type="hidden" name="senderMobileNumber" @if(auth('bearing')->check())
                                value="{{ auth('bearing')->user()->mobileNumber }}"
                                       onkeypress="handle(event)"
                                       @elseif (auth('customer')->check())
                                       value="{{ auth('customer')->user()->mobileNumber }}"
                                    @endif>
                            </div>

                            <div class="col-lg-12 mt-4 border-top border-dark p-2 pt-3 ml-2">
                                <div class="form-group">
                                    <label for="title" class="text-right h5 col-lg-12 mb-2">
                                        انتخاب ناوگان پیشنهادی :
                                    </label>

                                    <div id="fleetType" class="border rounded col-lg-4">
                                        <img src="{{ url('/assets/img/truck.svg') }}"
                                             style="max-height: 64px; max-width: 64px;" id="fleetTypesPic" class="mr-3">
                                        <span class="font-weight-bold m-3 text-center" id="fleetTypeTitle">
                                        عنوان ناوگان
                                    </span>
                                    </div>


                                    <div class="form-group col-lg-4 row text-center m-3">
                                        <span class="mt-2 h5">تعداد ناوگان مورد نیاز : </span>
                                        <input id="numOfFleets" type="text" placeholder="تعداد"
                                               onkeypress="handle(event)"
                                               value="1"
                                               class="form-control number text-center col-lg-2 ml-2 mr-2"
                                               name="numOfFleets">
                                        <span class="mt-2 h5">دستگاه</span>
                                        <input type="hidden" name="numOfTrucks" value="0">

                                    </div>

                                    <button type="button" class="btn btn-primary mt-2" id="addToSelectedFleetsList">
                                        اضافه به لیست ناوگان
                                    </button>

                                    <div class="mt-3 ">
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

                                            </tbody>
                                        </table>
                                    </div>

                                    <input type="hidden" name="fleetList" id="fleetListArray">

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
                            </div>

                            <div class="form-group col-lg-12 mt-4 p-2  pt-3 border-top border-dark">
                                <label class="text-right h5 col-lg-12">
                                    انتخاب نوع قیمت :
                                </label>

                                <div>
                                    <label class="text-right d-inline">
                                        <input type="radio" name="priceType" id="agreedPrice" checked>
                                        قیمت توافقی
                                    </label>
                                    <label class="text-right mr-3 d-inline">
                                        <input type="radio" name="priceType" id="proposedPrice">
                                        قیمت پیشنهادی
                                    </label>
                                </div>
                                <div class="form-group col-lg-12 mt-2" id="suggestedPriceForm" style="display: none">

                                    <h5 class="text-right">
                                        قیمت پیشنهادی :
                                    </h5>

                                    <div class="form-group row col-lg-4 p-2">
                                        <label class="text-right">
                                            <input type="radio" checked name="priceBased" value="به ازای هر تن">
                                            به ازای هر تن
                                        </label>
                                        <label class="text-right mr-3">
                                            <input type="radio" name="priceBased" value="به ازای کل بار">
                                            به ازای کل بار
                                        </label>

                                        @if(auth('customer')->check())
                                            <input id="suggestedPrice" type="hidden" name="suggestedPrice" value="0">
                                        @else
                                            <input id="suggestedPrice" type="text" placeholder="کرایه پیشنهادی (تومان)"
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
                                          class="form-control text-right" name="description"></textarea>
                            </div>

                            <div class="form-group col-lg-4 mt-2">
                                <label for="weight" class="text-right h5">
                                    بار شما چند تن است؟
                                    <span class="text-danger small">اختیاری</span>
                                </label>

                                <div>
                                    <label class="col-sm-3">
                                        <input type="checkbox" id="tonaje" name="tonaje" onclick="setFreeTonaje()">
                                        تناژ آزاد
                                    </label>
                                    <input type="number" onkeypress="handle(event)" placeholder="وزن"
                                           class="form-control col-sm-9 text-center" name="weight"
                                           id="weight">
                                </div>
                            </div>

                        </div>


                        <div class="col-lg-12 border-top border-primary">
                            <div class="col-lg-12 mb-3 text-center pt-3 btn"
                                 data-toggle="collapse"
                                 data-target="#collapseFormB" aria-expanded="false" aria-controls="collapseFormB">
                                پر کردن اطلاعات زیر
                                <span class="text-danger">اختیاری</span>
                                است
                                <span class="fa fa-chevron-down"></span>
                            </div>
                        </div>

                        <div class="collapse" id="collapseFormB">


                            <div class="form-group col-lg-6 mt-2">

                                <label for="loadingDate" class="text-right h5">
                                    تاریخ بارگیری :
                                </label>

                                <input id="loadingDate" type="text" placeholder="تاریخ ارسال بار"
                                       class="form-control datepicker" name="loadingDate">
                            </div>


                            <div class="form-group col-lg-6 mt-2">
                                <label class="text-right h5">
                                    انتخاب نوع بسته بندی :
                                </label>
                                <div class="border rounded">
                                    <div id="packingType" class="text-center row">
                                        <img src="{{ url('/assets/img/package.svg') }}" width="28" height="28"
                                             id="packingTypesPic" class="mr-4 mt-1 mb-1">
                                        <span id="packingTypeTitle" class="mt-1 mr-3 text-center">نوع بسته
                                            بندی</span>
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
                                    <input type="hidden" value="0" name="packing_type_id" id="packing_type_id">
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
                                <input id="width" type="text" placeholder="عرض" class="form-control text-center"
                                       onkeypress="handle(event)" name="width">
                            </div>

                            <div class="form-group col-lg-6 mt-2">
                                <label for="length" class="text-right h5"> طول بار (متر) : </label>
                                <input id="length" type="text" placeholder="طول" class="form-control text-center"
                                       onkeypress="handle(event)" name="length">
                            </div>

                            <div class="form-group col-lg-6 mt-2">
                                <label for="height" class="text-right h5"> ارتفاع بار (متر) : </label>
                                <input id="height" type="text" placeholder="ارتفاع" class="form-control text-center"
                                       onkeypress="handle(event)" name="height">
                            </div>

                            <div class="form-group col-lg-6 mt-2">
                                <label for="loadingAddress" class="text-right h5">
                                    آدرس بارگیری :
                                </label>

                                <textarea id="loadingAddress" placeholder="آدرس بارگیری" class="form-control"
                                          name="loadingAddress"></textarea>
                            </div>


                            <div class="form-group col-lg-6 mt-2">
                                <label for="dischargeAddress" class="text-right h5">
                                    آدرس تخلیه :
                                </label>

                                <textarea id="dischargeAddress" placeholder="آدرس تخلیه" class="form-control"
                                          name="dischargeAddress"></textarea>
                            </div>
                            <div class="form-group col-lg-6 mt-2">
                                <label for="loadingHour" class="text-right h5 col-lg-12">
                                    ساعت بارگیری :
                                </label>

                                <select id="loadingHour" class="form-control col-lg-3 text-center"
                                        name="loadingHour">
                                    <option value="0">ساعت</option>
                                    @for($hour=0;$hour<24;$hour++)
                                        <option value="{{ $hour }}">
                                            @if($hour<10) 0{{ $hour }} @else {{ $hour }} @endif </option>
                                    @endfor
                                </select>
                                <div class="col-lg-1">:</div>
                                <select id="loadingMinute" class="form-control text-center col-lg-3"
                                        name="loadingMinute">
                                    <option value="0">دقیقه</option>
                                    @for($minute=0;$minute<60;$minute++)
                                        <option value="{{ $minute }}">
                                            @if($minute<10) 0{{ $minute }} @else {{ $minute }} @endif </option>
                                    @endfor
                                </select>
                            </div>


                            <div class="form-group col-lg-12 mt-2">
                                <label for="insuranceAmount" class="text-right h5 col-lg-12">
                                    مبلغ بیمه (تومان) :
                                    <span class="small text-black-50">مبلغ بیمه معادل ارزش بار می باشد.</span>
                                </label>

                                <input id="insuranceAmount" type="text"
                                       onkeypress="handle(event)"
                                       placeholder="مبلغ بیمه معادل ارزش بار می باشد" class="form-control"
                                       name="insuranceAmount">
                            </div>

                            <div class="form-group col-lg-12 mt-2">
                                <label for="insuranceAmount" class="text-right h5 col-lg-12">
                                    تصویر بار خود را ارسال نمایید :
                                </label>

                                <div class="mt-2">
                                    <img src="{{url('assets/img/add_pic.svg')}}"
                                         style="max-width: 256px; max-height: 256px;" id="selected-pic"
                                         class="mb-2"><br>
                                    <div type="button" id="selected-pic-button" class="btn btn-primary">انتخاب عکس
                                    </div>
                                    <div type="button" id="remove-pic-button" class="btn btn-danger">حذف عکس</div>

                                    <input id="pic" type="file" class="d-none" name="pic" value="{{ old('pic') }}">
                                </div>
                            </div>
                        </div>

                        @if(auth('bearing')->check())
                            <input type="hidden" name="userType" value="transportation_company">
                        @elseif (auth('customer')->check())
                            <input type="hidden" name="userType" value="user">
                        @endif

                        <button class="btn btn-primary m-2 p-2" style="font-size:25px;" type="button"
                                onclick="checkForm();">ثبت اطلاعات بار
                        </button>
                    </div>
                </div>

            </div>
        </form>
    </div>



    <script>

        function checkForm() {

            let title = $("#title").val();
            let weight = $("#weight").val();
            let fleetListArray = $("#fleetListArray").val();
            let origin_city_id = $("#origin_city_id").val();
            let destination_city_id = $("#destination_city_id").val();
            let mobileNumberForCoordination = $("#mobileNumberForCoordination").val();
            let loadingDate = $("#loadingDate").val();

            let error = false;

            let message = "";

            // if (title.length == 0) {
            //     error = true;
            //     message += "عنوان بار الزامی است!" + "<br>";
            //     scrollToAnchor('title');
            // }

            if (fleetListArray.length == 0) {
                error = true;
                message += "انتخاب ناوگان الزامی است!" + "<br>";

            }
            if (origin_city_id < 1) {
                error = true;
                message += "مبدا الزامی است!" + "<br>";

            }
            if (destination_city_id < 1) {
                error = true;
                message += "مقصد الزامی است!" + "<br>";

            }
            if (mobileNumberForCoordination < 1) {
                error = true;
                message += "شماره تماس جهت هماهنگی الزامی است!" + "<br>";
                scrollToAnchor('mobileNumberForCoordination');
            }
            // if (loadingDate.length == 0) {
            //     error = true;
            //     message += "تاریخ بارگیری الزامی است!" + "<br>";
            //     scrollToAnchor('loadingDate');
            // }

            if (error) {
                swal({
                        title: "خطا!",
                        html: message,
                        type: "warning",
                        confirmButtonClass: "btn-danger",
                        cancelButtonText: "بستن",
                        closeOnConfirm: false
                    }
                )
            } else {
                $("#createNewLoad").submit();
            }
        }

        function handle(e) {
            if (e.keyCode === 13) {
                e.preventDefault(); // Ensure it is only this code that runs
                checkForm();
            }
        }

        function scrollToAnchor(aid) {
            var aTag = $("input[name='" + aid + "']");
            $('html,body').animate({scrollTop: aTag.offset().top}, 'slow');
        }

        function setFreeTonaje() {

            if ($("#tonaje").is(":checked")) {
                $("#weight").val(-1);
            } else {
                $("#weight").val(0);
            }
            $("#weight").fadeToggle();

        }

    </script>

@stop
