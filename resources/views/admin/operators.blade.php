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
            <p><a class="btn btn-primary" href="{{ url('admin/addNewOperatorForm') }}"> + افزودن اپراتور</a></p>

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>تصویر</th>
                        <th>نام و نام خانوادگی</th>
                        <th>کد ملی</th>
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
                                {{ $user->name }} {{ $user->lastName }}
                                @if ($user->status == 0)
                                    <span class="alert small alert-warning p-1">مسدود</span>
                                @else
                                    <span class="alert small alert-success p-1">فعال</span>
                                @endif
                            </td>
                            <td>{{ $user->nationalCode }}</td>
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
                                            @if ($user->status == 0)
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                                                        تغییر به فعال
                                                    </a>
                                                </li>
                                            @else
                                                <li>
                                                    <a class="dropdown-item"
                                                        href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                                                        تغییر به غیرفعال
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#removeOperator_{{ $user->id }}">حذف
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#changePassOperator_{{ $user->id }}">تغییر رمز عبور
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#operatorAccess_{{ $user->id }}">
                                                    دسترسی ها
                                                </button>
                                            </li>
                                            <li>
                                                <button type="button" class="dropdown-item" data-bs-toggle="modal"
                                                    data-bs-target="#operatorCargoListAccess_{{ $user->id }}">
                                                    دسترسی ثبت بار
                                                </button>
                                            </li>
                                        </ul>
                                    </div>
                                </div>


                                <!-- Modal -->
                                <div id="removeOperator_{{ $user->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">حذف اپراتور</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>آیا مایل به حذف اپراتور
                                                    <span class="text-primary"> {{ $user->name }}
                                                        {{ $user->lastName }}</span>
                                                    هستید؟
                                                </p>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <a class="btn btn-primary"
                                                    href="{{ url('admin/removeOperator') }}/{{ $user->id }}">حذف
                                                    اپراتور</a>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <!-- Modal -->
                                <div id="changePassOperator_{{ $user->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">
                                        <form method="POST" action="{{ route('user.resetPass', $user->id) }}" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalTopTitle">تغییر رمز عبور :
                                                    {{ $user->name }} {{ $user->lastName }}</h5>

                                            </div>
                                            <div class="modal-body">
                                                <div class="row">
                                                    <div class="col mb-3">
                                                        <label for="password" class="form-label">رمز عبور</label>
                                                        <input type="text" id="password" name="password" class="form-control"
                                                            placeholder="" />
                                                    </div>
                                                </div>

                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-outline-secondary"
                                                    data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                                <button type="submit" class="btn btn-primary">ذخیره</button>
                                            </div>
                                        </form>
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
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="dashboard"
                                                            @if (in_array('dashboard', $user->userAccess)) checked @endif>
                                                        داشبورد
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="finalApprovalAndStoreCargo"
                                                            @if (in_array('finalApprovalAndStoreCargo', $user->userAccess)) checked @endif>
                                                        تایید و ثبت دسته ای بار
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="loads"
                                                            @if (in_array('loads', $user->userAccess)) checked @endif>
                                                        بارها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="rejectedCargoFromCargoList"
                                                            @if (in_array('rejectedCargoFromCargoList', $user->userAccess)) checked @endif>
                                                        بار های رد شده
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="transportationCompanies"
                                                            @if (in_array('transportationCompanies', $user->userAccess)) checked @endif>
                                                        باربری ها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="fleetRatioToDriverActivityReport"
                                                            @if (in_array('fleetRatioToDriverActivityReport', $user->userAccess)) checked @endif>
                                                        نسبت راننده به بار
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="customers"
                                                            @if (in_array('customers', $user->userAccess)) checked @endif>
                                                        صاحب بارها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="drivers"
                                                            @if (in_array('drivers', $user->userAccess)) checked @endif>
                                                        راننده ها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="operators"
                                                            @if (in_array('operators', $user->userAccess)) checked @endif>
                                                        اپراتور ها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="searchLoads"
                                                            @if (in_array('searchLoads', $user->userAccess)) checked @endif>
                                                        جستجوی بارها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="operatorsWorkingHoursActivityReport"
                                                            @if (in_array('operatorsWorkingHoursActivityReport', $user->userAccess)) checked @endif>
                                                        میزان فعالیت اپراتورها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="contactReportWithCargoOwners"
                                                            @if (in_array('contactReportWithCargoOwners', $user->userAccess)) checked @endif>
                                                        تماس با صاحبان بار و باربری ها - تماس با رانندگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="driversActivities"
                                                            @if (in_array('driversActivities', $user->userAccess)) checked @endif>
                                                        گزارش فعالیت رانندگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="appVersions"
                                                            @if (in_array('appVersions', $user->userAccess)) checked @endif>
                                                        ورژن اپلیکیشن ها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="provincesAndCities"
                                                            @if (in_array('provincesAndCities', $user->userAccess)) checked @endif>
                                                        استان ها و شهرها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="SOSList"
                                                            @if (in_array('SOSList', $user->userAccess)) checked @endif>
                                                        درخواست های امداد
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="listOfLoadsByOperator"
                                                            @if (in_array('listOfLoadsByOperator', $user->userAccess)) checked @endif>
                                                        بارها به تفکیک اپراتور
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="reportcargofleets"
                                                            @if (in_array('reportcargofleets', $user->userAccess)) checked @endif>
                                                        گزارش بار ها به تفکیک ناوگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="driverActivityReport"
                                                            @if (in_array('driverActivityReport', $user->userAccess)) checked @endif>
                                                        گزارش فعالیت رانندگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="fleet"
                                                            @if (in_array('fleet', $user->userAccess)) checked @endif>
                                                        ناوگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="complaints"
                                                            @if (in_array('complaints', $user->userAccess)) checked @endif>
                                                        دسته شکایات و انتقادها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="accounting"
                                                            @if (in_array('accounting', $user->userAccess)) checked @endif>
                                                        حسابداری
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="messages"
                                                            @if (in_array('messages', $user->userAccess)) checked @endif>
                                                        پیام ها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="onlineUsers"
                                                            @if (in_array('onlineUsers', $user->userAccess)) checked @endif>
                                                        کاربران آنلاین
                                                    </label>
                                                </div>


                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="paymentReport"
                                                            @if (in_array('paymentReport', $user->userAccess)) checked @endif>
                                                        پرداخت ها
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="unSuccessPayment"
                                                            @if (in_array('unSuccessPayment', $user->userAccess)) checked @endif>
                                                        پرداخت ناموفق رانندگان
                                                    </label>
                                                </div>


                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="services"
                                                            @if (in_array('services', $user->userAccess)) checked @endif>
                                                        خدمات ها
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="freeSubscription"
                                                            @if (in_array('freeSubscription', $user->userAccess)) checked @endif>
                                                        اشتراک و تماس رایگان
                                                    </label>
                                                </div>
                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="loadOwner"
                                                            @if (in_array('loadOwner', $user->userAccess)) checked @endif>
                                                        بار های ثبت شده توسط صاحبین بار
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="summaryOfDaysReport"
                                                            @if (in_array('summaryOfDaysReport', $user->userAccess)) checked @endif>
                                                        خلاصه گزارش روز - گزارش فعالیت باربری ها - فعالیت صاحب بارها -
                                                        فعالیت اپراتورها - گزارش های ترکیبی - نصب رانندگان در 30 روز
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="driversContactCall"
                                                            @if (in_array('driversContactCall', $user->userAccess)) checked @endif>
                                                        گزارش بر اساس تماس
                                                    </label>
                                                </div>
                                                <ul id="myUL">
                                                    <li><span class="caret">دسترسی به رانندگان</span>
                                                        <ul class="nested">
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
                                                                        <input type="checkbox" name="driversPaymentReport"
                                                                            @if (in_array('driversPaymentReport', $user->userAccess)) checked @endif>
                                                                        لیست پرداختی و تمدید اعتبار
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
@stop
