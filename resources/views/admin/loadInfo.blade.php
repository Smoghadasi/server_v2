@extends('layouts.dashboard')

@section('content')
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            اطلاعات بار
        </li>
    </ol>
    <div class="text-right">
        <p>
            <a class="btn btn-primary" href="{{ url('admin/editLoadInfoForm') }}/{{ $load->id }}"> ویرایش اطلاعات بار</a>
            <a class="btn btn-danger" href="{{ url('admin/removeLoadInfo') }}/{{ $load->id }}"> حذف اطلاعات بار</a>
        </p>
    </div>
    <div class="container">

        <div class="text-right">
            <div class="card mb-2">
                <div class="table-responsive">
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
                                    @if ($load->weight == -1)
                                        تناژ آزاد
                                    @else
                                        {{ $load->weight }} تن
                                    @endif
                                </td>

                                <td class="font-weight-bold">نام مشتری</td>
                                <td>
                                    @if ($load->userType == ROLE_TRANSPORTATION_COMPANY)
                                        {{ \App\Http\Controllers\BearingController::getBearingTitle($load->user_id) }}
                                    @else
                                        {{ \App\Http\Controllers\CustomerController::getCustomerName($load->user_id) }}
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">نام باربری</td>
                                <td>{{ $load->bearing_id }}</td>

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
                                    @if ($load->loadMode == 'innerCity')
                                        درون شهری
                                    @elseif($load->loadMode == 'outerCity')
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
                                <td>{{ $load->loadingAddress ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">به شهر</td>
                                <td>{{ $path['stateTo'] }} - {{ $path['to'] }}</td>
                                <td class="font-weight-bold">آدرس محل تخلیه بار</td>
                                <td>{{ $load->dischargeAddress ?? '-' }}</td>
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
                                @if (Auth::user()->role == 'admin')
                                    <td class="font-weight-bold">موقعیت جغرافیایی</td>
                                    <td>
                                        <a class="text-danger"
                                            href="http://maps.google.com/maps?f=q&q={{ $load->originLatitude }},{{ $load->originLongitude }}">نقشه
                                            مپ</a>
                                    </td>
                                @endif
                            </tr>
                        </tbody>
                    </table>
                </div>
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

                            @foreach ($fleetLoads as $item)
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
                    @if ($load->status > 3 && $load->bearing_id == auth('bearing')->id())
                        <div class="m-2 p-2 alert alert-success" style="font-size: 16px;">
                            تلفن ارسال کننده بار :
                            {{ $load->senderMobileNumber }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        var modal = document.getElementById('myModal');
        var img = document.getElementById('loadPic');
        var modalImg = document.getElementById("img01");
        var captionText = document.getElementById("caption");
        img.onclick = function() {
            modal.style.display = "block";
            modalImg.src = this.src;
            captionText.innerHTML = this.alt;
        }
        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            modal.style.display = "none";
        }
        modal.onclick = function() {
            modal.style.display = "none";
        }
    </script>
@stop
