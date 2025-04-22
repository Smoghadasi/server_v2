<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth('bearing')->check())

@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif

@extends($dashboard)

@section('content')
    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{--<a href="#">داشبورد</a>--}}
            اطلاعات بار
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>

    <div class="container">
        @if(auth('customer')->check())
            <div class="col-md-12">
                <div class="text-right">
                    {{--<p><a class="btn btn-primary" href="{{ route('operators.create') }}"> + افزودن اپراتور</a></p>--}}
                    <div class="table-responsive col-md-9">
                        <table class="table table-striped" cellspacing="0">
                            <tbody>
                            <tr>
                                <td class="font-weight-bold">عنوان بار</td>
                                <td>{{ $load->title }}</td>

                                <td class="font-weight-bold">عرض</td>
                                <td>{{ $load->width }} متر</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">طول</td>
                                <td>{{ $load->length }} متر</td>

                                <td class="font-weight-bold">ارتفاع</td>
                                <td>{{ $load->height }} متر</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">وزن</td>
                                <td>
                                    @if($load->weight == -1)
                                        تناژ آزاد
                                    @else
                                        {{ $load->weight }} تن
                                    @endif
                                </td>

                                <td class="font-weight-bold">تلفن ارسال کننده بار</td>
                                <td>{{ $load->senderMobileNumber }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">زمان تخلیه</td>
                                <td>{{ $load->dischargeTime }}</td>

                                <td class="font-weight-bold">تاریخ بارگیری</td>
                                <td>{{ $load->loadingDate }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">میزان بیمه</td>
                                <td>{{ number_format($load->insuranceAmount) }} تومان</td>

                                <td class="font-weight-bold">قیمت پیشنهادی صاحب بار</td>
                                <td>
                                    {{ $load->priceBased }}
                                    @if(strlen($load->suggestedPrice) && $load->suggestedPrice > 0)
                                        - {{ number_format($load->suggestedPrice) }} تومان
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">نوع ناوگان</td>
                                <td>
                                    {{ \App\Http\Controllers\FleetController::getFleetName($load->fleet_id) }}
                                </td>
                                <td class="font-weight-bold">تعداد خودرو</td>
                                <td>{{ $load->numOfTrucks  }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">نوع بسته بندی</td>
                                <td>{{ $load->packingTypeTitle }}</td>
                                <td class="font-weight-bold"></td>
                                <td></td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">زمان</td>
                                <td>
                                    {{ $load->loadingHour }}:{{ $load->loadingMinute }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">توضیحات</td>
                                <td>{{ $load->description }}</td>

                                <td class="font-weight-bold">وضعیت</td>
                                <td>
                                    <div class="alert alert-primary" style="font-size: 12px; padding: 5px">
                                        {{ \App\Http\Controllers\LoadController::getLoadStatusTitle($load->status) }}
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">از شهر</td>
                                <td>{{ $path['from'] }}</td>
                                <td class="font-weight-bold">آدرس محل ارسال بار</td>
                                <td>{{ $load->loadingAddress }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">به شهر</td>
                                <td>{{ $path['to'] }}</td>
                                <td class="font-weight-bold">آدرس محل تخلیه بار</td>
                                <td>{{ $load->dischargeAddress }}</td>
                            </tr>

                            </tbody>
                        </table>
                        <div class="card mb-2">
                            <h5 class="card-header">لیست ناوگان انتخاب شده</h5>
                            <div class="card-body">
                                <table class="table">
                                    <thead>
                                    <tr>
                                        <th>ردیف</th>
                                        <th>نوع ناوگان</th>
                                        <th>تعداد خودرو</th>
                                    </tr>
                                    </thead>
                                    <tbody>

                                    @php
                                        $i = 1;
                                    @endphp

                                    @foreach($fleetLoads as $item)
                                        <tr>
                                            <td>{{ $i++ }}</td>
                                            <td>{{ \App\Http\Controllers\FleetController::getFleetName($item->fleet_id) }}</td>
                                            <td>{{ $item->numOfFleets }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        @if(strlen($load->loadPic))
                            <div class="card card-body">
                                <div class="text-center mb-2">تصویر بار</div>
                                <br>

                                <img src="{{ url($load->loadPic) }}" class="col-md-12" style="cursor: pointer;"
                                     id="loadPic">
                                <div class="p-2 text-center" style="font-size: 14px;">
                                    برای بزرگنمایی روی تصویر کلیک کنید
                                </div>
                                <div id="myModal" class="modal">
                                    <span class="close">&times;</span>
                                    <img class="modal-content" id="img01">
                                    <div id="caption"></div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>


            </div>
        @elseif(auth('bearing')->check())
            <div class="col-md-12">
                <div class="text-right">
                    <div class="table-responsive col-md-9">
                        <table class="table table-striped " cellspacing="0">
                            <tbody>
                            <tr>
                                <td class="font-weight-bold">عنوان بار</td>
                                <td>{{ $load->title }}</td>

                                <td class="font-weight-bold">عرض</td>
                                <td>{{ $load->width }} متر</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">طول</td>
                                <td>{{ $load->length }} متر</td>

                                <td class="font-weight-bold">ارتفاع</td>
                                <td>{{ $load->height }} متر</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">وزن</td>
                                <td>{{ $load->weight }} تن</td>

                                <td class="font-weight-bold">نام مشتری</td>
                                <td>{{ \App\Http\Controllers\BearingController::getBearingTitle($load->bearing_id) }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold"></td>
                                <td></td>
                                <td class="font-weight-bold">تاریخ بارگیری</td>
                                <td>{{ $load->loadingDate }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">میزان بیمه</td>
                                <td>{{ number_format($load->insuranceAmount) }} تومان</td>

                                <td class="font-weight-bold">قیمت پیشنهادی باربری</td>
                                <td>
                                    {{ $load->priceBased }}
                                    @if(strlen($load->suggestedPrice) && $load->suggestedPrice > 0)
                                        - {{ number_format($load->suggestedPrice) }} تومان
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">زمان تخلیه</td>
                                <td>{{ $load->dischargeTime }}</td>
                                <td class="font-weight-bold">زمان</td>
                                <td>
                                    {{ $load->loadingHour }}:{{ $load->loadingMinute }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">نوع ناوگان</td>
                                <td>
                                    {{ \App\Http\Controllers\FleetController::getFleetName($load->fleet_id) }}
                                </td>

                                <td class="font-weight-bold">تعداد خودرو</td>
                                <td>{{ $load->numOfTrucks  }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">توضیحات</td>
                                <td>{{ $load->description }}</td>

                                <td class="font-weight-bold">وضعیت</td>
                                <td>
                                    <div class="alert alert-primary" style="font-size: 12px; padding: 5px">
                                        {{ \App\Http\Controllers\LoadController::getLoadStatusTitle($load->status) }}
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>


                        <div class="card mb-2">

                            <h5 class="card-header">محدوده بارگیری و تخلیه</h5>
                            <div class="card-body">
                                <div class="m-2 p-2 alert alert-success" style="font-size: 16px;">
                                    محدوده بارگیری :
                                    {{ $path['from'] }}
                                    -
                                    {{ $load->loadingRange }}
                                </div>
                                <div class="m-2 p-2 alert alert-success" style="font-size: 16px;">
                                    محدوده تخلیه :
                                    {{ $path['to'] }}
                                    -
                                    {{ $load->dischargeRange }}
                                </div>
                                @if($load->status > 3 && ($load->bearing_id==auth('bearing')->id()))
                                    <div class="m-2 p-2 alert alert-success" style="font-size: 16px;">
                                        تلفن ارسال کننده بار :
                                        {{ $load->senderMobileNumber }}</div>
                                @endif
                            </div>
                        </div>


                    </div>
                    <div class="col-md-3">
                        @if($load->loadPic)
                            <div class="card card-body">
                                <div class="text-center">تصویر بار</div>
                                <br>

                                <img src="{{ url($load->loadPic) }}" class="col-md-12">
                                <div class="alert text-center"><a class="btn btn-primary"
                                                                  href="{{ url($load->loadPic) }}"
                                                                  target="_blank">بزرگنمایی تصویر</a></div>

                            </div>
                        @endif
                    </div>
                    <div class="card mb-2">
                        <h5 class="card-header">لیست ناوگان انتخاب شده</h5>
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                <tr>
                                    <th>ردیف</th>
                                    <th>نوع ناوگان</th>
                                    <th>تعداد خودرو</th>
                                </tr>
                                </thead>
                                <tbody>

                                @php
                                    $i = 1;
                                @endphp

                                @foreach($fleetLoads as $item)
                                    <tr>
                                        <td>{{ $i++ }}</td>
                                        <td>{{ \App\Http\Controllers\FleetController::getFleetName($item->fleet_id) }}</td>
                                        <td>{{ $item->numOfFleets }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        @endif

        @if($load->status>0)
            <div class="col-md-12 text-center">
                <div class="alert alert-info col-md-3 text-center center-block">
                    زمان باقیمانده جهت ثبت قیمت
                    <h3 id="displayTimer" class="font-weight-bold text-center"></h3>
                </div>
            </div>
        @endif
        <div class="col-md-9">
            @if(isset($result))
                @if($result['result']==1)
                    <div class="alert alert-success text-center">
                        قیمت پیشنهادی شما ثبت شد
                    </div>
                @else
                    <div class="alert alert-warning text-center">
                        {{ $result['message'] }}
                    </div>
                @endif
            @endif
            <div class="card">
                <div class="card-header"> قیمت برای این بار به تومان</div>

                @if(auth('customer')->check())
                    @if(count($tenders)==0)
                        <div class="alert alert-info m-3">لطفا تا اعلام قیمت توسط باربری ها صبر کنید</div>
                    @endif
                    @if($load->status==-2)
                        <div class="alert alert-danger">بار مورد نظر تایید نشد <br> پیام مربوط به عدم تایید
                            : {{ $load->adminMessage }}</div>
                    @endif
                    <?php $i = 1; ?>
                    <div id="suggestionsList">

                        @foreach($tenders as $tender)

                            @if($load->status>2 && $tender->bearing_id != $load->bearing_id)
                                @continue
                            @endif

                            <div
                                style="background-color: #f1f1f1; @if($load->status>3) font-size: 18px; font-weight: bold; @endif"
                                class="card m-1 p-3 @if($load->status>3) alert alert-info @endif">

                                <div class="alert bg-light p-1">عنوان باربری : {{ $tender->bearingTitle }}</div>

                                @if($load->status>3)
                                    قیمت پذیرفته شده
                                @else
                                    قیمت پیشنهادی
                                @endif

                                @if($load->status<=2){{ $i++ }} @endif
                                : {{ number_format($tender->suggestedPrice) }} تومان
                                @if($load->status==2)
                                    <button onClick='selectBearing({{ $tender->bearing_id }});'
                                            class='btn btn-primary mt-2 pull-left col-md-3'>انتخاب این باربری
                                    </button>
                                @endif

                            </div>
                        @endforeach
                    </div>
                    @if($load->status<=2)

                        <script>
                            $(document).ready(function () {
                                var MyTimer = setInterval(function () {
                                    $.ajax({
                                        url: "<?php echo "/api/v1/customer/requestSuggestionsOfATender/" . $load->id; ?>",
                                        success: function (result) {

                                            if (result.result == 1) {
                                                var numOfRows = result.suggestions.length;
                                                $("#suggestionsList").html("");
                                                for (var index = 0; index < numOfRows; index++) {
                                                    $("#suggestionsList").append("<div style='background-color: #f1f1f1' class='card m-1 p-3'><div class='alert bg-light p-1'>عنوان باربری : " + result.suggestions[index].bearingTitle + "</div> قیمت پیشنهادی " + (index + 1) + " : " + result.suggestions[index].suggestedPrice + " تومان   <span onClick='selectBearing(" + result.suggestions[index].bearing_id + ");' class='btn btn-primary mt-2 pull-left col-md-3'>انتخاب این باربری </span> </div>");
                                                }
                                            } else if (result.result == 2) {
                                                clearInterval(MyTimer);
                                                swal({
                                                        title: result.message,
                                                        text: "لطفا باربری مورد نظر را انتخاب نمایید",
                                                        type: "info",
                                                        confirmButtonClass: "btn-danger",
                                                        confirmButtonText: "بستن",
                                                        closeOnConfirm: false
                                                    }
                                                )
                                            }
                                        },
                                        error: function () {

                                        }
                                    });
                                }, 60000);
                            });

                            function selectBearing(bearing_id) {
                                $.ajax({
                                    type: "post",
                                    data: {
                                        load_id: "{{ $id }}",
                                        customer_id: "{{ $load->user_id }}",
                                        bearing_id: bearing_id
                                    },
                                    url: "<?php echo "/api/v1/customer/selectBearingForLoad"; ?>",
                                    success: function (result) {

                                        if (result.result == 1) {
                                            swal({
                                                    title: "انتخاب بابری",
                                                    text: "باربری مورد نظر با موفقیت انتخاب شد",
                                                    type: "success",
                                                    confirmButtonClass: "btn-danger",
                                                    confirmButtonText: "بستن",
                                                    closeOnConfirm: false
                                                }
                                            )
                                            setTimeout(function () {
                                                location.reload();
                                            }, 2000);
                                        } else if (result.result == 0) {
                                            swal({
                                                    title: result.message,
                                                    type: "info",
                                                    confirmButtonClass: "btn-danger",
                                                    confirmButtonText: "بستن",
                                                    closeOnConfirm: false
                                                }
                                            )
                                        }
                                    },
                                    error: function () {

                                    }
                                });
                            }
                        </script>
                    @elseif($load->status==3)


                        <h5 class="alert alert-primary">عنوان باربری
                            : {{ \App\Http\Controllers\BearingController::getBearingTitle($load->bearing_id) }}
                            <br>
                            به زودی جهت حمل بار از طرف این باربری با شما تماس گرفته می شود.
                        </h5>

                        <script>
                            $(document).ready(function () {
                                var MyTimer = setInterval(function () {
                                    $.ajax({
                                        url: "<?php echo "/api/v1/customer/requestLoadStatus/" . $load->id; ?>",
                                        success: function (result) {

                                            if (result > 3) {
                                                clearInterval(MyTimer);
                                                swal({
                                                        title: "درحال بروز رسانی",
                                                        text: "از طرف باربری مورد نظر هزینه پرداخت شد، جهت بروز رسانی لطفا صبر کنید",
                                                        type: "success",
                                                        showConfirmButton: false
                                                    }
                                                )

                                                setTimeout(function () {
                                                    location.reload();
                                                }, 3000);
                                            }
                                        },
                                        error: function () {

                                        }
                                    });
                                }, 60000);
                            });
                        </script>
                    @elseif($load->status > 3 && $load->bearing_id > 0)
                        <?php $bearing = \App\Http\Controllers\BearingController::getBearingInfo($load->bearing_id);?>
                        <h5 class="alert alert-primary">
                            به زودی توسط باربری جهت حمل بار از طرف باربری با شما تماس گرفته می شود.
                        </h5>

                        <table class="table table-striped" cellspacing="0">
                            <tr>
                                <td>عنوان باربری</td>
                                <td>{{ $bearing->title }}</td>
                            </tr>
                            <tr>
                                <td>نام متصدی</td>
                                <td>{{ $bearing->operatorName  }}</td>
                            </tr>
                            <tr>
                                <td>شماره تلفن همراه</td>
                                <td>{{ $bearing->mobileNumber  }}</td>
                            </tr>
                            <tr>
                                <td>شماره تلفن ثابت</td>
                                <td>{{ $bearing->phoneNumber  }}</td>
                            </tr>
                        </table>

                    @endif
                @elseif(auth('bearing')->check())

                    <form class="card-body" method="post" id="suggestionPriceForm"
                          action="{{ url('user/suggestionPrice') }}">
                        <div class="form-group">
                            @csrf
                            <input type="hidden" name="load_id" value="{{ $id }}">
                            <input type="hidden" name="bearing_id" value="{{ auth('bearing')->id() }}">

                            <?php
                            $check = 1;
                            foreach ($tenders as $tender) {
                                if ($tender->bearing_id == auth('bearing')->id())
                                    $check = 2;
                            }
                            $i = 1;
                            echo '<input type="hidden" id="insertOrUpdate" name="insertOrUpdate" value="' . $check . '">';
                            ?>

                            @if($load->status>=0 && $load->status<=1)
                                @if(\App\Tender::where([['load_id',$load->id],['bearing_id',auth('bearing')->id()]])->count()>0)

                                    @foreach($tenders as $tender)
                                        <div class="m-1"
                                             @if($tender->bearing_id==auth('bearing')->id())
                                             style="color: #218838">
                                            <img width="32" height="32" class="m-2"
                                                 src="{{ url('../../assets/img/tag_blue.svg') }}">
                                            <div
                                                style="float: left;display: inline-block; background: #2fa360; color: #ffffff; border-radius: 3px;"
                                                class="p-1">
                                                قیمت پیشنهادی شما
                                            </div>
                                            @else
                                                class="m-1">
                                                <img width="32" height="32" class="m-2"
                                                     src="{{ url('../../assets/img/tag_blue.svg') }}">
                                            @endif
                                            قیمت پیشنهادی
                                            @if($load->status<=2){{ $i++ }} @endif
                                            : {{ number_format($tender->suggestedPrice) }} تومان

                                        </div>
                                        <hr>
                                    @endforeach
                                @elseif(\App\Http\Controllers\DateController::getSecondFromCreateRowToPresent($load->tender_start)>0)
                                    <input type="text" class="form-control col-md-8 mt-2 number" id="suggestedPrice"
                                           name="suggestedPrice"
                                           placeholder="ثبت قیمت به تومان">
                                    <button id="submitSuggestedPrice" class="btn btn-primary  mt-2 col-md-3 mr-2">
                                        ثبت قیمت
                                    </button>
                                @endif
                            @else
                                @if($load->status == 3)
                                    <div class="alert alert-primary text-center">
                                        این مناقصه به پایان رسیده است
                                    </div>

                                    @foreach($tenders as $tender)

                                        <h5 class="m-1"
                                            @if($tender->bearing_id==auth('bearing')->id())
                                            style="color: #218838">
                                            <img width="32" height="32" class="m-2"
                                                 src="{{ url('../../assets/img/tag_blue.svg') }}">
                                            قیمت پیشنهادی شما
                                            @if($load->status<=2){{ $i++ }} @endif
                                            : {{ number_format($tender->suggestedPrice) }} تومان

                                            @break
                                            @endif
                                        </h5>
                                    @endforeach
                                    <br>
                                @elseif($load->status > 3 || $load->status == 2)
                                    @foreach($tenders as $tender)
                                        <h5
                                            @if($tender->bearing_id==auth('bearing')->id())
                                            style="color: #218838">
                                            <img width="32" height="32" class="m-2"
                                                 src="{{ url('../../assets/img/tag_blue.svg') }}">
                                            قیمت پیشنهادی شما
                                            @if($load->status<=2){{ $i++ }} @endif
                                            : {{ number_format($tender->suggestedPrice) }} تومان

                                            @break
                                            @endif
                                        </h5>
                                    @endforeach
                                @endif
                                @if($load->status == 3 && $load->bearing_id == auth('bearing')->id())
                                    <?php $suggesttion = \App\Http\Controllers\TenderController::requestABearingPriceInTender($load->id, $load->bearing_id);?>
                                    هزینه قابل پرداخت جهت نمایش کامل اطلاعات :
                                    @if($load->numOfTrucks>0)
                                        {{ (($suggesttion*$load->numOfTrucks)/100)*2 }}
                                    @else
                                        {{ ($suggesttion/100)*2 }}
                                    @endif
                                    تومان
                                    <br>
                                    <div class="btn btn-primary mt-2"
                                         onclick="payForDisplayingTheLoadInformation({{ $load->bearing_id }});">
                                        پرداخت هزینه
                                    </div>
                                    <script>
                                        function payForDisplayingTheLoadInformation(bearing_id) {
                                            $(document).ready(function () {

                                                $.ajax({
                                                    type: "post",
                                                    data: {
                                                        load_id: "{{ $id }}",
                                                        bearing_id: bearing_id
                                                    },
                                                    url: "<?php echo "/api/v1/bearing/payForDisplayingTheLoadInformation"; ?>",
                                                    success: function (result) {

                                                        if (result.result == 1) {

                                                            swal({
                                                                    title: " هزینه پرداخت شد",
                                                                    text: "درحال بروز رسانی صفحه",
                                                                    type: "success",
                                                                    showConfirmButton: false
                                                                }
                                                            )
                                                            setTimeout(function () {
                                                                location.reload();
                                                            }, 3000);
                                                        } else if (result.result == 2) {

                                                            swal({
                                                                title: "موجودی کیف پول کافی نیست",
                                                                text: "موجودی کیف پول شما کمتر از مبلغ مورد نظر می باشد آیا مایل به افزایش موجوی خود هستید",
                                                                type: "warning",
                                                                showCancelButton: true,
                                                                confirmButtonColor: "#DD6B55 !important",
                                                                cancelButtonColor: "#FF0000",
                                                                confirmButtonText: "افزایش کیف پول",
                                                                cancelButtonText: "بستن",
                                                                closeOnConfirm: false
                                                            }).then(function () {
                                                                window.location.replace("/user/wallet")
                                                            });

                                                        } else if (result.result == 0) {
                                                            swal({
                                                                    title: result.message,
                                                                    type: "info",
                                                                    confirmButtonClass: "btn-danger",
                                                                    confirmButtonText: "بستن",
                                                                    closeOnConfirm: false
                                                                }
                                                            )
                                                        }
                                                    },
                                                    error: function () {

                                                    }
                                                });
                                                swal({
                                                    title: "درحال کسر هزینه از کیف پول",
                                                    text: "لطفا منتظر بمانید",
                                                    showConfirmButton: false
                                                });
                                            });
                                        }
                                    </script>
                                @endif
                            @endif
                        </div>
                    </form>
                @endif
            </div>
        </div>

        @if(auth('customer')->check() && $load->score == 0 && $load->staus >3)

            <form class="col-lg-5 mt-3">
                <input type="hidden" name="score" id="score" value="0">
                <div class="card">
                    <div class="card-header">
                        ارسال نظر
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            امتیاز :
                            <div class="d-inline-block">
                                <div class="scoreItem" id="scoreItem_1"
                                     onclick="selectScore(1);"
                                     onmouseover="onMouseOverItem(1);"
                                     onmouseout="onMouseOutItem();">1
                                </div>
                                <div class="scoreItem" id="scoreItem_2"
                                     onclick="selectScore(2);"
                                     onmouseover="onMouseOverItem(2);"
                                     onmouseout="onMouseOutItem();">2
                                </div>
                                <div class="scoreItem" id="scoreItem_3"
                                     onclick="selectScore(3);"
                                     onmouseover="onMouseOverItem(3);"
                                     onmouseout="onMouseOutItem();">3
                                </div>
                                <div class="scoreItem" id="scoreItem_4"
                                     onclick="selectScore(4);"
                                     onmouseover="onMouseOverItem(4);"
                                     onmouseout="onMouseOutItem();">4
                                </div>
                                <div class="scoreItem" id="scoreItem_5"
                                     onclick="selectScore(5);"
                                     onmouseover="onMouseOverItem(5);"
                                     onmouseout="onMouseOutItem();">5
                                </div>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label>نظر : <span style="font-size: 12px; color: #218838;">(لطفا نظر خودرا در مورد این باربری ارسال نمایید)</span></label>
                                <textarea name="comment" id="comment" class="form-control"
                                          placeholder="ثبت نظر"></textarea>
                            </div>
                            <div class="form-group">
                                <button type="button" id="btnSaveScore" class="btn btn-primary pull-left">ثبت</button>
                            </div>
                        </div>

                    </div>
                </div>
            </form>

            <script>

                function onMouseOverItem(id) {
                    document.getElementById("scoreItem_1").className = "scoreItem";
                    document.getElementById("scoreItem_2").className = "scoreItem";
                    document.getElementById("scoreItem_3").className = "scoreItem";
                    document.getElementById("scoreItem_4").className = "scoreItem";
                    document.getElementById("scoreItem_5").className = "scoreItem";
                    switch (id) {
                        case 1:
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            break;
                        case 2:
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            break;
                        case 3:
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            break;
                        case 4:
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            document.getElementById("scoreItem_4").className = "scoreItemSelected";
                            break;
                        case 5:
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            document.getElementById("scoreItem_4").className = "scoreItemSelected";
                            document.getElementById("scoreItem_5").className = "scoreItemSelected";
                            break;
                    }
                }

                function onMouseOutItem() {

                    document.getElementById("scoreItem_1").className = "scoreItem";
                    document.getElementById("scoreItem_2").className = "scoreItem";
                    document.getElementById("scoreItem_3").className = "scoreItem";
                    document.getElementById("scoreItem_4").className = "scoreItem";
                    document.getElementById("scoreItem_5").className = "scoreItem";

                    switch (document.getElementById("score").value) {
                        case "1":
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            break;
                        case "2":
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            break;
                        case "3":
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            break;
                        case "4":
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            document.getElementById("scoreItem_4").className = "scoreItemSelected";
                            break;
                        case "5":
                            document.getElementById("scoreItem_1").className = "scoreItemSelected";
                            document.getElementById("scoreItem_2").className = "scoreItemSelected";
                            document.getElementById("scoreItem_3").className = "scoreItemSelected";
                            document.getElementById("scoreItem_4").className = "scoreItemSelected";
                            document.getElementById("scoreItem_5").className = "scoreItemSelected";
                            break;
                    }
                }

                function selectScore(scoreNumber) {
                    document.getElementById("score").value = scoreNumber;
                }

                $(document).ready(function () {

                    $("#btnSaveScore").click(function () {

                        if ($("#score").val() == "0") {
                            swal({
                                    title: "امتیاز مورد نظر خود را انتخاب کنید",
                                    type: "error",
                                    confirmButtonClass: "btn-danger",
                                    confirmButtonText: "بستن",
                                }
                            );
                        } else {
                            $.ajax({
                                type: "post",
                                data: {
                                    load_id: "{{ $id }}",
                                    score: $("#score").val(),
                                    comment: $("#comment").val()
                                },
                                url: "/api/v1/customer/sendScoreAndCommentToLoadFromCustomer",
                                success: function (result) {
                                    swal({
                                            title: result.message,
                                            text: "درحال بروز رسانی صفحه",
                                            type: "success",
                                            confirmButtonClass: "btn-danger",
                                            confirmButtonText: "بستن",
                                            closeOnConfirm: false
                                        }
                                    );

                                    setTimeout(function () {
                                        location.reload();
                                    }, 500);

                                },
                                beforeSend: function () {
                                    swal({
                                        title: "درحال ارسال امتیاز",
                                        text: "لطفا منتظر بمانید",
                                        showConfirmButton: false
                                    });
                                },
                                error: function () {
                                    swal({
                                            title: "خطا! دوباره تلاش کنید",
                                            type: "info",
                                            confirmButtonClass: "btn-danger",
                                            confirmButtonText: "بستن",
                                            closeOnConfirm: false
                                        }
                                    )
                                }
                            });
                        }
                    });

                    var MyTimer = setInterval(function () {
                        $.ajax({
                            url: "<?php echo "/api/v1/customer/requestLoadStatus/" . $load->id; ?>",
                            success: function (result) {

                                if (result != {{ $load->status }}) {
                                    clearInterval(MyTimer);
                                    swal({
                                            title: "درحال بروز رسانی",
                                            text: "لطفا صبر کنید",
                                            type: "info",
                                            showConfirmButton: false
                                        }
                                    );

                                    setTimeout(function () {
                                        location.reload();
                                    }, 3000);
                                }
                            },
                            error: function () {

                            }
                        });
                    }, 20000);

                    $("#suggestedPrice").keydown(function (e) {
                        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                            (e.keyCode == 65 && (e.ctrlKey === true || e.metaKey === true)) ||
                            (e.keyCode == 67 && (e.ctrlKey === true || e.metaKey === true)) ||
                            (e.keyCode == 88 && (e.ctrlKey === true || e.metaKey === true)) ||
                            (e.keyCode >= 35 && e.keyCode <= 39)) {
                            return;
                        }
                        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                            e.preventDefault();
                        }
                    });
                });
            </script>

        @elseif($load->score > 0)
            <div class="col-lg-5 mt-3">

                <div class="card">
                    <div class="card-header">
                        @if(auth('customer')->check())
                            امتیاز شما
                        @else
                            امتیاز صاحب بار
                        @endif
                    </div>
                    <h3 class="card-body">
                        <div class="form-group">
                            امتیاز :
                            {{ $load->score }}
                        </div>

                        <div>
                            <div class="form-group">
                                نظر :
                                {{ $load->comment }}

                            </div>
                        </div>

                    </h3>
                </div>
            </div>
        @endif
    </div>

    <script>


        $('input.number').keyup(function (event) {

            // skip for arrow keys
            if (event.which >= 37 && event.which <= 40) return;

            // format number
            $(this).val(function (index, value) {
                return value
                    .replace(/\D/g, "")
                    .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
            });
        });

    </script>

    <?php $time = \App\Http\Controllers\DateController::getSecondFromCreateRowToPresent($load->tender_start); ?>

    <script>

        var countDown = (900 -<?php echo $time; ?>);

        var initTimer = setInterval(function () {

            if (countDown <= 0) {
                clearInterval(initTimer);
                document.getElementById("displayTimer").innerText = "پایان یافته";
            } else {
                var sec = countDown;
                var min = (sec >= 60) ? parseInt(sec / 60) : 0;
                sec = sec - (min * 60);
                var strSec = sec;
                if (sec < 10)
                    strSec = "0" + sec;
                var strMin = min;
                if (min < 10)
                    strMin = "0" + min;
                document.getElementById("displayTimer").innerText = strMin + ":" + strSec;
                countDown--;
            }
        }, 1000);


        var modal = document.getElementById('myModal');
        var img = document.getElementById('loadPic');
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function () {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function () {
            modal.style.display = "none";
        }
        modal.onclick = function () {
            modal.style.display = "none";
        }


    </script>
@stop
