@extends('layouts.dashboard')

@section('content')
    <style>
        /* Remove default bullets */
        ul,
        #myUL {
            list-style-type: none;
        }

        /* Remove margins and padding from the parent ul */
        #myUL {
            margin: 0;
            padding: 0;
        }

        /* Style the caret/arrow */
        .caret {
            cursor: pointer;
            user-select: none;
            /* Prevent text selection */
        }

        /* Create the caret/arrow with a unicode, and style it */
        .caret::before {
            content: "\25C0";
            color: black;
            display: inline-block;
            margin-right: 6px;
        }

        /* Rotate the caret/arrow icon when clicked on (using JavaScript) */
        .caret-down::before {
            transform: rotate(90deg);
        }

        /* Hide the nested list */
        .nested {
            display: none;
        }

        /* Show the nested list when the user clicks on the caret/arrow (with JavaScript) */
        .active {
            display: block;
        }
    </style>
    <div class="card">
        <h5 class="card-header">
            اپراتورها
        </h5>
        <div class="card-body">
            <p>
                <a class="btn btn-primary" href="{{ route('operators.create') }}"> + افزودن اپراتور</a>
                <a class="btn btn-primary" href="{{ route('vacations.index') }}"> + مرخصی روزانه</a>
                <a class="btn btn-primary" href="{{ route('vacationHour.index') }}"> + مرخصی ساعتی</a>
            </p>
            <div class="table-responsive">


            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تصویر</th>
                        <th>نام و نام خانوادگی</th>
                        <th>کد ملی</th>
                        <th>مجوز دسترسی</th>
                        <th>موبایل</th>
                        <th>ایمیل</th>
                        {{-- <th>جنسیت</th> --}}
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach ($users as $user)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td><img class="img-thumbnail" width="64" height="64"
                                    src="{{ url('pictures/users') }}/{{ $user->pic }}"></td>
                            <td>
                                <a href="{{ route('operators.show', ['operator' => $user->id]) }}">
                                    {{ $user->name }} {{ $user->lastName }}
                                </a>
                                @if ($user->status == 0)
                                    <span class="alert small alert-warning p-1">مسدود</span>
                                @else
                                    <span class="alert small alert-success p-1">فعال</span>
                                @endif
                            </td>
                            <td>{{ $user->nationalCode }}</td>
                            <td>
                                <a href="#"  data-bs-toggle="modal" data-bs-target="#modalCenter_{{ $user->id }}">
                                    @switch($user->accessDevice)
                                        @case('Both')
                                            تمام دستگاه ها
                                            @break
                                        @case('Mobile')
                                            موبایل
                                            @break
                                        @case('Desktop')
                                            کامپیوتر
                                            @break
                                    @endswitch
                                </a>
                                <div class="modal fade" id="modalCenter_{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                      <form class="modal-content" method="post" action="{{ route('admin.changeAccessDevice', $user->id) }}">
                                        @csrf
                                        @method('patch')
                                        <div class="modal-header">
                                          <h5 class="modal-title" id="modalCenterTitle">دسترسی دستگاه</h5>
                                          <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="modal"
                                            aria-label="Close"
                                          ></button>
                                        </div>
                                        <div class="modal-body">
                                          <div class="row">
                                            <div class="col mb-3">
                                              <label for="nameWithTitle" class="form-label">دسترسی</label>
                                                <select name="accessDevice" class="form-control form-select">
                                                    <option @if($user->accessDevice == 'Both') selected @endif value="Both">همه</option>
                                                    <option @if($user->accessDevice == 'Mobile') selected @endif value="Mobile">موبایل</option>
                                                    <option @if($user->accessDevice == 'Desktop') selected @endif value="Desktop">کامپیوتر</option>
                                                </select>
                                            </div>
                                          </div>
                                        </div>
                                        <div class="modal-footer">
                                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            بستن
                                          </button>
                                          <button type="submit" class="btn btn-primary">ذخیره</button>
                                        </div>
                                      </div>
                                    </form>
                                </div>
                            </td>
                            <td>{{ $user->mobileNumber }}</td>
                            <td>{{ $user->email }}</td>
                            {{-- <td>
                            @if ($user->sex == 0)
                                خانم
                            @else
                                آقا
                            @endif
                        </td> --}}

                            <td>


                                <div class="dropdown">
                                    <div class="btn-group dropstart">
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            عملیات
                                        </button>
                                        <ul class="dropdown-menu">
                                            @if (auth()->user()->role == 'admin')
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                        data-bs-target="#operatorAccess_{{ $user->id }}">
                                                        دسترسی ها
                                                    </button>
                                                </li>
                                            @endif

                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#operatorCargoListAccess_{{ $user->id }}">
                                                    دسترسی ثبت بار
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                                <div id="operatorAccess_{{ $user->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form class="modal-content"
                                            action="{{ url('admin/operatorAccess') }}/{{ $user->id }}" method="post">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title">دسترسی ها</h4>
                                            </div>
                                            <div class="modal-body text-right">
                                                <ul id="myUL">
                                                    <li><span class="caret">داشبورد</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboard"
                                                                            @if (in_array('dashboard', $user->userAccess)) checked @endif>
                                                                        داشبورد
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllCargo"
                                                                            @if (in_array('dashboardAllCargo', $user->userAccess)) checked @endif>
                                                                        کل بارها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllOwner"
                                                                            @if (in_array('dashboardAllOwner', $user->userAccess)) checked @endif>
                                                                        کل صاحبان بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllBearing"
                                                                            @if (in_array('dashboardAllBearing', $user->userAccess)) checked @endif>
                                                                        کل باربری ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllCustomers"
                                                                            @if (in_array('dashboardAllCustomers', $user->userAccess)) checked @endif>
                                                                        کل صاحب بارها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllMessage"
                                                                            @if (in_array('dashboardAllMessage', $user->userAccess)) checked @endif>
                                                                        کل پیام ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="dashboardAllDriver"
                                                                            @if (in_array('dashboardAllDriver', $user->userAccess)) checked @endif>
                                                                        کل رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">احراز هویت</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driversAuthentication"
                                                                            @if (in_array('driversAuthentication', $user->userAccess)) checked @endif>
                                                                        رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="ownersAuthentication"
                                                                            @if (in_array('ownersAuthentication', $user->userAccess)) checked @endif>
                                                                        صاحبان بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">بار ها</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="warehouse"
                                                                            @if (in_array('warehouse', $user->userAccess)) checked @endif>
                                                                        انبار بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <label>
                                                                            <input type="checkbox"
                                                                                name="rejectedCargoFromCargoList"
                                                                                @if (in_array('rejectedCargoFromCargoList', $user->userAccess)) checked @endif>
                                                                            بار های رد شده
                                                                        </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <label>
                                                                            <input type="checkbox" name="copyLoad"
                                                                                @if (in_array('copyLoad', $user->userAccess)) checked @endif>
                                                                            کپی بار
                                                                        </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <label>
                                                                            <input type="checkbox"
                                                                                name="duplicateCargoFromCargoList"
                                                                                @if (in_array('duplicateCargoFromCargoList', $user->userAccess)) checked @endif>
                                                                            بار تکراری
                                                                        </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <label>
                                                                            <input type="checkbox" name="fleetlessNumber"
                                                                                @if (in_array('fleetlessNumber', $user->userAccess)) checked @endif>
                                                                            شماره های بدون ناوگان
                                                                        </label>
                                                                </div>
                                                            </li>

                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="loadOwner"
                                                                            @if (in_array('loadOwner', $user->userAccess)) checked @endif>
                                                                        بار های ثبت شده توسط صاحبین بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="loadBearing"
                                                                            @if (in_array('loadBearing', $user->userAccess)) checked @endif>
                                                                        بار های ثبت شده توسط باربری
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="loadsReport"
                                                                            @if (in_array('loadsReport', $user->userAccess)) checked @endif>
                                                                        گزارش بار ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driversPaymentReport"
                                                                            @if (in_array('driversPaymentReport', $user->userAccess)) checked @endif>
                                                                        لیست پرداختی و تمدید اعتبار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="detailDriver"
                                                                            @if (in_array('detailDriver', $user->userAccess)) checked @endif>
                                                                        جزئیات و ویرایش
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="loadRegistration"
                                                                            @if (in_array('loadRegistration', $user->userAccess)) checked @endif>
                                                                        ثبت بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="confirmLoads"
                                                                            @if (in_array('confirmLoads', $user->userAccess)) checked @endif>
                                                                        تایید بار ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="EquivalentWordsInCargoRegistration"
                                                                            @if (in_array('EquivalentWordsInCargoRegistration', $user->userAccess)) checked @endif>
                                                                        کلمات معادل در ثبت بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="listOfLoadsByOperator"
                                                                            @if (in_array('listOfLoadsByOperator', $user->userAccess)) checked @endif>
                                                                        بار ها به تفکیک اپراتور
                                                                    </label>
                                                                </div>
                                                            </li>

                                                        </ul>

                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">گزارش ها</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="fleetReportSummary"
                                                                            @if (in_array('fleetReportSummary', $user->userAccess)) checked @endif>
                                                                        خلاصه رانندگان بر اساس ناوگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="reportcargofleets"
                                                                            @if (in_array('reportcargofleets', $user->userAccess)) checked @endif>
                                                                        بار ها به تفکیک ناوگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="storeCargoOperator"
                                                                            @if (in_array('storeCargoOperator', $user->userAccess)) checked @endif>
                                                                        ثبت بار دستی
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <ul id="myUL">
                                                                <li><span class="caret">استفاده کنندگان</span>
                                                                    <ul class="nested">
                                                                        <li>
                                                                            <div class="form-group">
                                                                                <label>
                                                                                    <input type="checkbox"
                                                                                        name="separationOfTheCity"
                                                                                        @if (in_array('separationOfTheCity', $user->userAccess)) checked @endif>
                                                                                    تفکیک شهرستان
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                        <li>
                                                                            <div class="form-group">
                                                                                <label>
                                                                                    <input type="checkbox"
                                                                                        name="separationOfTheState"
                                                                                        @if (in_array('dashboardAllDriverState', $user->userAccess)) checked @endif>
                                                                                    تفکیک استان
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="ownersNissan"
                                                                            @if (in_array('ownersNissan', $user->userAccess)) checked @endif>
                                                                        صاحبان بار نیسان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="freeSubscription"
                                                                            @if (in_array('freeSubscription', $user->userAccess)) checked @endif>
                                                                        اشتراک و تماس رایگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driversInMonth"
                                                                            @if (in_array('driversInMonth', $user->userAccess)) checked @endif>
                                                                        فعالیت رانندگان غیر تکراری
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driverActivityReport"
                                                                            @if (in_array('driverActivityReport', $user->userAccess)) checked @endif>
                                                                        فعالیت رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="paymentOfDrivers"
                                                                            @if (in_array('paymentOfDrivers', $user->userAccess)) checked @endif>
                                                                        پرداخت رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="fleetByCall"
                                                                            @if (in_array('fleetByCall', $user->userAccess)) checked @endif>
                                                                        ناوگان بر اساس تماس
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="driversBasedOnTheMostCalls"
                                                                            @if (in_array('driversBasedOnTheMostCalls', $user->userAccess)) checked @endif>
                                                                        رانندگان بر اساس بیشترین تماس
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driversBasedOnTime"
                                                                            @if (in_array('driversBasedOnTime', $user->userAccess)) checked @endif>
                                                                        فعالیت رانندگان بر اساس زمان (امروز)
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="summaryOfTheDayReport"
                                                                            @if (in_array('summaryOfTheDayReport', $user->userAccess)) checked @endif>
                                                                        خلاصه گزارش روز
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="bearingActivities"
                                                                            @if (in_array('bearingActivities', $user->userAccess)) checked @endif>
                                                                        فعالیت باربری ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="cargoOwnersActivityReport"
                                                                            @if (in_array('cargoOwnersActivityReport', $user->userAccess)) checked @endif>
                                                                        فعالیت صاحب بارها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="activityOfOperators"
                                                                            @if (in_array('activityOfOperators', $user->userAccess)) checked @endif>
                                                                        فعالیت اپراتور ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="combinedReport"
                                                                            @if (in_array('combinedReport', $user->userAccess)) checked @endif>
                                                                        گزارش ترکیبی
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="driverInstallationInLast30Days"
                                                                            @if (in_array('driverInstallationInLast30Days', $user->userAccess)) checked @endif>
                                                                        نصب رانندگان در 30 روز
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="fleetRatioToDriverActivityReport"
                                                                            @if (in_array('fleetRatioToDriverActivityReport', $user->userAccess)) checked @endif>
                                                                        نسبت راننده به بار
                                                                    </label>
                                                                </div>
                                                            </li>

                                                        </ul>

                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">پرداخت ها</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="driverPayment"
                                                                            @if (in_array('driverPayment', $user->userAccess)) checked @endif>
                                                                        راننده ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="theHighestPayingDrivers"
                                                                            @if (in_array('theHighestPayingDrivers', $user->userAccess)) checked @endif>
                                                                        بیشترین پرداخت رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="paymentBasedOnFleet"
                                                                            @if (in_array('paymentBasedOnFleet', $user->userAccess)) checked @endif>
                                                                        پرداخت بر اساس ناوگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">صاحبان بار</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="registeredLoadsByOperators"
                                                                            @if (in_array('registeredLoadsByOperators', $user->userAccess)) checked @endif>
                                                                        اپراتور
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="registeredLoadsByOwners"
                                                                            @if (in_array('registeredLoadsByOwners', $user->userAccess)) checked @endif>
                                                                        صاحب بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">شکایات و انتقادات</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="complaintsOfDrivers"
                                                                            @if (in_array('complaintsOfDrivers', $user->userAccess)) checked @endif>
                                                                        رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="complaintsOfOwners"
                                                                            @if (in_array('complaintsOfOwners', $user->userAccess)) checked @endif>
                                                                        صاحبان بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="blockages"
                                                                            @if (in_array('blockages', $user->userAccess)) checked @endif>
                                                                        مسدودی ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="IPBlocked"
                                                                            @if (in_array('IPBlocked', $user->userAccess)) checked @endif>
                                                                        IP های مسدود
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="messages"
                                                                            @if (in_array('messages', $user->userAccess)) checked @endif>
                                                                        پیام ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="onlineUsers"
                                                                            @if (in_array('onlineUsers', $user->userAccess)) checked @endif>
                                                                        کاربران آنلاین
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <ul id="myUL">
                                                                <li><span class="caret">گزارش تخلف</span>
                                                                    <ul class="nested">
                                                                        <li>
                                                                            <div class="form-group">
                                                                                <label>
                                                                                    <input type="checkbox"
                                                                                        name="violationReportOwner"
                                                                                        @if (in_array('violationReportOwner', $user->userAccess)) checked @endif>
                                                                                    صاحب بار
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                        <li>
                                                                            <div class="form-group">
                                                                                <label>
                                                                                    <input type="checkbox"
                                                                                        name="violationReportDrivers"
                                                                                        @if (in_array('violationReportDrivers', $user->userAccess)) checked @endif>
                                                                                    رانندگان
                                                                                </label>
                                                                            </div>
                                                                        </li>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">ناوگان</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="fleet"
                                                                            @if (in_array('fleets', $user->userAccess)) checked @endif>
                                                                        ناوگان ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="myFleets"
                                                                            @if (in_array('myFleets', $user->userAccess)) checked @endif>
                                                                        ناوگان من
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">امکانات</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="operatorsWorkingHoursActivityReport"
                                                                            @if (in_array('operatorsWorkingHoursActivityReport', $user->userAccess)) checked @endif>
                                                                        میزان فعالیت اپراتورها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="searchLoads"
                                                                            @if (in_array('searchLoads', $user->userAccess)) checked @endif>
                                                                        جستجو بارها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="bookmark"
                                                                            @if (in_array('bookmark', $user->userAccess)) checked @endif>
                                                                        علامت گذاری شده ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox"
                                                                            name="personalizedNotification"
                                                                            @if (in_array('personalizedNotification', $user->userAccess)) checked @endif>
                                                                        اعلان شخصی سازی شده
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="operators"
                                                                            @if (in_array('operators', $user->userAccess)) checked @endif>
                                                                        اپراتورها
                                                                    </label>
                                                                </div>
                                                            </li>

                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="callWithOwner"
                                                                            @if (in_array('callWithOwner', $user->userAccess)) checked @endif>
                                                                        تماس با صاحب بار و باربری
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="contactTheDrivers"
                                                                            @if (in_array('contactTheDrivers', $user->userAccess)) checked @endif>
                                                                        تماس با رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="appVersions"
                                                                            @if (in_array('appVersions', $user->userAccess)) checked @endif>
                                                                        ورژن اپلیکیشن ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="provincesAndCities"
                                                                            @if (in_array('provincesAndCities', $user->userAccess)) checked @endif>
                                                                        استان ها و شهر ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="services"
                                                                            @if (in_array('services', $user->userAccess)) checked @endif>
                                                                        خدمات ها
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">رسانه</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="sliders"
                                                                            @if (in_array('sliders', $user->userAccess)) checked @endif>
                                                                        اسلایدر
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="radios"
                                                                            @if (in_array('radios', $user->userAccess)) checked @endif>
                                                                        رادیو
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">حسابداری</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="discrepancies"
                                                                            @if (in_array('discrepancies', $user->userAccess)) checked @endif>
                                                                        صورت مغایرت بانکی
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <ul id="myUL">
                                                    <li><span class="caret">پشتیبانی</span>
                                                        <ul class="nested">
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="unSuccessPayment"
                                                                            @if (in_array('unSuccessPayment', $user->userAccess)) checked @endif>
                                                                        پرداخت ناموفق رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="drivers"
                                                                            @if (in_array('drivers', $user->userAccess)) checked @endif>
                                                                        رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="listOfOwners"
                                                                            @if (in_array('listOfOwners', $user->userAccess)) checked @endif>
                                                                        صاحبان بار
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="transactionManual"
                                                                            @if (in_array('transactionManual', $user->userAccess)) checked @endif>
                                                                        تراکنش های دستی
                                                                    </label>
                                                                </div>
                                                            </li>

                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="incomingCallDriver"
                                                                            @if (in_array('incomingCallDriver', $user->userAccess)) checked @endif>
                                                                        تماس ورودی رانندگان
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="searchAll"
                                                                            @if (in_array('searchAll', $user->userAccess)) checked @endif>
                                                                        جستجو هدر
                                                                    </label>
                                                                </div>
                                                            </li>
                                                            <li>
                                                                <div class="form-group">
                                                                    <label>
                                                                        <input type="checkbox" name="setting"
                                                                            @if (in_array('setting', $user->userAccess)) checked @endif>
                                                                        تنظیمات
                                                                    </label>
                                                                </div>
                                                            </li>
                                                        </ul>
                                                    </li>
                                                </ul>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="finalApprovalAndStoreCargo"
                                                            @if (in_array('finalApprovalAndStoreCargo', $user->userAccess)) checked @endif>
                                                        تایید و ثبت دسته ای بار
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="storeCargoPlus"
                                                            @if (in_array('storeCargoPlus', $user->userAccess)) checked @endif>
                                                        ثبت بار پلاس
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="processingUnitVIP"
                                                            @if (in_array('processingUnitVIP', $user->userAccess)) checked @endif>
                                                        ثبت بار نیمه اتوماتیک
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="driversContactCall"
                                                            @if (in_array('driversContactCall', $user->userAccess)) checked @endif>
                                                        لیست تماس ها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="contactReportWithCargoOwners"
                                                            @if (in_array('contactReportWithCargoOwners', $user->userAccess)) checked @endif>
                                                        پیام ها در جزئیات رانندگان
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <button class="btn btn-primary" type="submit">ثبت دسترسی
                                                </button>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                                <div id="operatorCargoListAccess_{{ $user->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form class="modal-content"
                                            action="{{ url('admin/operatorCargoListAccess') }}/{{ $user->id }}"
                                            method="post">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title">دسترسی ثبت بار</h4>
                                            </div>
                                            <div class="modal-body text-right">
                                                @foreach ($fleets as $fleet)
                                                    <div class="form-group">
                                                        <label>
                                                            <input type="checkbox" name="cargoAccess[]"
                                                                value="{{ $fleet->id }}"
                                                                @if (in_array($fleet->id, $user->cargoAccess)) checked @endif>
                                                            {{ $fleet->title }}
                                                        </label>
                                                    </div>
                                                @endforeach

                                            </div>
                                            <div class="modal-footer text-left">
                                                <button class="btn btn-primary" type="submit">ثبت دسترسی
                                                </button>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        </div>
    </div>

    <script>
        var toggler = document.getElementsByClassName("caret");
        var i;

        for (i = 0; i < toggler.length; i++) {
            toggler[i].addEventListener("click", function() {
                this.parentElement.querySelector(".nested").classList.toggle("active");
                this.classList.toggle("caret-down");
            });
        }
    </script>
@endsection
@section('script')
    <script>
        $('#category-select').on('change', function() {
        var selectedCategory = $(this).val();
            console.log(selectedCategory);
            // $.ajax({
            //     url: '/api/filter',
            //     method: 'POST',
            //     data: {
            //         category: selectedCategory
            //     },
            //     headers: {
            //         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            //     },
            //     success: function(response) {
            //         console.log('Server response:', response);
            //         // اینجا می‌تونی داده‌ها رو در صفحه نمایش بدی
            //     },
            //     error: function(xhr) {
            //         console.error('Error:', xhr.responseText);
            //     }
            // });
        });
    </script>
@endsection
