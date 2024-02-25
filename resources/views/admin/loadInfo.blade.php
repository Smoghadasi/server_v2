@extends('layouts.dashboard')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            اطلاعات بار
        </li>
    </ol>
    <div class="text-right">
        <p>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/admin') }}"> + افزودن بار توسط اپراتور</a>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/bearing') }}"> + افزودن برای باربری</a>
            <a class="btn btn-primary" href="{{ url('admin/addNewLoadForm/customer') }}"> + افزودن بار برای صاحب
                بار</a>
        </p>
    </div>
    <div class="container">

        <div class="text-right">
            <div class="row p-3">
                <a class="btn btn-primary ml-3" href="{{ url('admin/editLoadInfoForm') }}/{{ $load->id }}"> ویرایش
                    اطلاعات بار </a>
                <a class="btn btn-danger ml-3" href="{{ url('admin/removeLoadInfo') }}/{{ $load->id }}"> حذف
                    اطلاعات بار </a>
                <a class="btn btn-info ml-3" href="{{ url('admin/repeatTender') }}/{{ $load->id }}">
                    اجرای مجدد مناقصه </a>
                <a class="btn btn-dark" href="{{ url('admin/changeLoadStatusToPastStatus') }}/{{ $load->id }}">برگشت به
                    مرحله قبل</a>
            </div>
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
                        <td>
                            @if($load->weight == -1)
                                تناژ آزاد
                            @else
                                {{ $load->weight }} تن
                            @endif
                        </td>

                        <td class="font-weight-bold">نام مشتری</td>
                        <td>
                            @if($load->userType == ROLE_TRANSPORTATION_COMPANY)
                                {{ \App\Http\Controllers\BearingController::getBearingTitle($load->user_id) }}
                            @else
                                {{ \App\Http\Controllers\CustomerController::getCustomerName($load->user_id) }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">نام باربری</td>
                        <td>{{ $load->bearing_id  }}</td>

                        <td class="font-weight-bold">تلفن ارسال کننده بار</td>
                        <td>{{ $load->senderMobileNumber }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">تاریخ بارگیری</td>
                        <td>{{ $load->loadingDate }}</td>

                        <td class="font-weight-bold">ساعت بارگیری</td>
                        <td>
                            ساعت
                            @php
                                $pieces = explode(' ', $load->created_at);
                            @endphp
                            {{ $pieces[1] }} دقیقه
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">میزان بیمه</td>
                        <td>{{ number_format($load->insuranceAmount) }} تومان</td>

                        <td class="font-weight-bold">قیمت پیشنهادی</td>
                        <td>{{ number_format($load->suggestedPrice) }} تومان</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">زمان تخلیه</td>
                        <td>{{ $load->dischargeTime }}</td>

                        <td class="font-weight-bold">نوع بار (درون شهری یا برون شهری)</td>
                        <td>
                            @if($load->loadMode=='innerCity')
                                درون شهری
                            @elseif($load->loadMode=='outerCity')
                                برون شهری
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">نوع بسته بندی</td>
                        <td>{{ $load->packingTypeTitle }}</td>
                        <td class="font-weight-bold">هزینه نهایی حمل بار</td>
                        <td>{{ number_format($load->price) }} تومان</td>
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
                        <td>{{ $path['stateFrom'] }} - {{ $path['from'] }}</td>
                        <td class="font-weight-bold">آدرس محل ارسال بار</td>
                        <td>{{ $load->loadingAddress }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">به شهر</td>
                        <td>{{ $path['stateTo'] }} - {{ $path['to'] }}</td>
                        <td class="font-weight-bold">آدرس محل تخلیه بار</td>
                        <td>{{ $load->dischargeAddress }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">تعداد بازدیدکننده</td>
                        <td>{{ $load->driverVisitCount }}</td>
                        <td class="font-weight-bold">تعداد درخواست</td>
                        <td>{{ $load->numOfInquiryDrivers }}</td>
                    </tr>
                    <tr>
                        <td class="font-weight-bold">تعداد تماس</td>
                        <td>{{ $load->numOfDriverCalls }}</td>
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
                        <div class="text-center mb-2">تصویر بار</div>
                        <br>

                        <img src="{{ url($load->loadPic) }}" class="col-md-12" style="cursor: pointer;" id="loadPic">
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
            <div class="col-md-8">

                <form class="card mb-2" method="post" action="{{ url('admin/suggestionToLoadPriceByAdmin') }}">
                    @csrf
                    <input type="hidden" value="{{ $load->id }}" name="load_id">
                    <h5 class="card-header col-lg-12">ثبت قیمت برای باربری</h5>
                    <div class="card-body">
                        <div class="form-group">
                            <select class="form-control" id="bearing_id" name="bearing_id">
                                <option value="0">انتخاب باربری</option>
                                @foreach($bearings as $bearingItem)
                                    <option value="{{ $bearingItem->id }}">{{ $bearingItem->title }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <input class="form-control" id="suggestedPrice" name="suggestedPrice"
                                   placeholder="قیمت پیشنهادی">
                        </div>
                        <div class="form-group">
                            <button class="btn btn-primary" type="submit">ثبت قیمت برای باربری مورد نظر</button>
                        </div>
                    </div>
                </form>

            </div>
        </div>
        <div class="col-lg-12">

            @if($load->status > 0)

                <div class="card">
                    <div class="card-header">قیمت ها</div>
                    <div class="card-body">

                        @foreach($tenders as $tender)
                            <div @if($tender->id == $load->bearing_id)
                                 class="alert alert-success"
                                 @else
                                 class="alert alert-light"
                                @endif
                            >عنوان باربری : {{ $tender->title }} ، قیمت پیشنهادی
                                : {{ number_format($tender->suggestedPrice) }}
                                تومان

                            </div>
                        @endforeach

                    </div>
                </div>

                @if($load->status >= 3)
                    <?php
                    $bearing = \App\Http\Controllers\BearingController::getBearingInfo($load->bearing_id);
                    ?>

                    @if(isset($bearing->title))

                        <table class="table table-striped text-right d-inline-block" cellspacing="0">
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
                @endif
            @else

                <div class="col-md-12 text-right">
                    <h5 class="card-body">تایید یا عدم تایید این بار را ثبت نمایید</h5>

                    <form method="post" action="{{ url('admin/approveOrRejectLoad') }}" class="card card-body col-md-8">
                        @if($load->status==-2)
                            <div class="alert alert-danger">بار مورد نظر تایید نشد <br> پیام مربوط به عدم تایید
                                : {{ $load->adminMessage }}</div>
                        @elseif($load->status==0)
                            <div class="alert alert-success">بار مورد نظر تایید شد <br> پیام مربوط به تایید
                                : {{ $load->adminMessage }}</div>
                        @endif
                        @csrf
                        <input type="hidden" name="load_id" value="{{ $load->id }}">
                        <textarea class="form-control" name="adminMessage"
                                  placeholder="توضیحات مربوط به تایید یا عدم تایید این بار"></textarea>

                        <div class="m-1 row">
                            <textarea class="form-control col-md-6" name="loadingRange"
                                      placeholder="محدوده بارگیری">{{ $load->loadingRange }}</textarea>
                            <textarea class="form-control col-md-6" name="dischargeRange"
                                      placeholder="محدوده تخلیه">{{ $load->dischargeRange }}</textarea>
                        </div>

                        <div class="col-md-8">
                            <input type="radio" name="status" value="-2">
                            عدم تایید
                            <br>
                            <input type="radio" name="status" value="0">
                            تایید
                        </div>
                        <div class="col-md-8">
                            <button class="btn btn-primary">ثبت</button>
                        </div>
                    </form>
                </div>

            @endif
        </div>
    </div>

    <script>
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
