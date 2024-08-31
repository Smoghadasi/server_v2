<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('icons/irt.png') }}" width="50" height="50" />
            </span>
            <span class="app-brand-text demo menu-text fw-bolder ms-2">ایران ترابر</span>

        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
            <i class="bx bx-chevron-left bx-sm align-middle"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        <!-- Dashboard -->
        @if (in_array('dashboard', auth()->user()->userAccess))
            <li class="menu-item">
                <a href="{{ url('/dashboard') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-home-circle"></i>
                    <div data-i18n="Analytics">داشبورد</div>
                </a>
            </li>
        @endif

        @if (in_array('finalApprovalAndStoreCargo', auth()->user()->userAccess))
            <li class="menu-item">
                <a class="menu-link" href="{{ url('admin/finalApprovalAndStoreCargo') }}">
                    <i class="menu-icon tf-icons bx bx-box"></i>
                    <div data-i18n="Without menu">تایید و ثبت دسته ای بار</div>
                </a>
            </li>
        @endif
        @if (in_array('unSuccessPayment', auth()->user()->userAccess))
            <li class="menu-item">
                <a class="menu-link" href="{{ route('unSuccessPeyment.driver') }}">
                    <i class="menu-icon tf-icons bx bx-credit-card"></i>
                    <div data-i18n="Without menu">پرداخت ناموفق</div>
                </a>
            </li>
        @endif

        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-user-check"></i>
                <div data-i18n="pais">احراز هویت
                    @if (
                        \App\Http\Controllers\DriverController::getNumOfAuthDriver() +
                            \App\Http\Controllers\OwnerController::getNumOfAuthOwner() >
                            0)
                        <span class="badge badge-center rounded-pill bg-label-warning">!</span>
                    @endif
                </div>
            </a>
            <ul class="menu-sub">
                @if (in_array('driversAuthentication', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('driver.auth.operator') }}">
                            <div data-i18n="Without menu">رانندگان
                                <span class="badge badge-center rounded-pill bg-danger">
                                    <td>{{ \App\Http\Controllers\DriverController::getNumOfAuthDriver() }}</td>
                                </span>
                            </div>
                        </a>
                    </li>
                @endif
                @if (in_array('ownersAuthentication', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('ownerAuth.index') }}">
                            <div data-i18n="Without menu">صاحبان بار
                                <span class="badge badge-center rounded-pill bg-danger">
                                    <td>{{ \App\Http\Controllers\OwnerController::getNumOfAuthOwner() }}</td>
                                </span>
                            </div>
                        </a>
                    </li>
                @endif

            </ul>
        </li>



        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-category-alt"></i>
                <div data-i18n="pais">بار ها</div>
            </a>

            <ul class="menu-sub">
                @if (in_array('rejectedCargoFromCargoList', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/rejectedCargoFromCargoList') }}">
                            <div data-i18n="Without menu"> بارهای رد شده</div>
                        </a>
                    </li>
                @endif
                @if (in_array('ownersNissan', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('ownersNissan') }}">
                            <div data-i18n="Without menu"> بارهای رد شده</div>
                        </a>
                    </li>
                @endif
                @if (in_array('loadOwner', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('admin.loadBackup') }}">
                            <div data-i18n="Without menu">بارهای ثبت شده توسط صاحب بار</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('admin.loadBackupTransportation') }}">
                            <div data-i18n="Without menu">بارهای ثبت شده توسط باربری</div>
                        </a>
                    </li>
                @endif

                @if (in_array('loads', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/loads') }}">
                            <div data-i18n="Without menu"> گزارش بار ها</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/storeCargoConvertForm') }}">
                            <div data-i18n="Without menu">ثبت بار</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('accept.cargo.index') }}">
                            <div data-i18n="Without menu">تایید بار ها</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('equivalent.index') }}">
                            <div data-i18n="Without menu">کلمات معادل در ثبت بار</div>
                        </a>
                    </li>
                @endif
                @if (in_array('listOfLoadsByOperator', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/listOfLoadsByOperator') }}">
                            <span>بارها به تفکیک اپراتور</span>
                        </a>
                    </li>
                @endif
            </ul>
        </li>

        <li
            class="menu-item {{ request()->is('admin/usersByCity*') ? 'active open' : '' }} {{ request()->is('admin/usersByProvince*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-bar-chart"></i>
                <div data-i18n="Layouts">گزارش ها</div>
            </a>

            <ul class="menu-sub">
                @if (in_array('reportcargofleets', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('report.cargo.fleets') }}">
                            <span>بار ها به تفکیک ناوگان</span>
                        </a>
                    </li>
                @endif
                @if (in_array('usersByCity', auth()->user()->userAccess))
                    <li
                        class="menu-item {{ request()->is('admin/usersByCity*') ? 'active open' : '' }} {{ request()->is('admin/usersByProvince*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            {{-- <i class="menu-icon tf-icons bx bx-bar-chart"></i> --}}
                            <div data-i18n="Layouts">استفاده کنندگان</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('reporting.usersByCity') }}">
                                    <span>تفکیک شهرستان</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('reporting.usersByProvince') }}">
                                    <span>تفکیک استان</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
                @if (in_array('freeSubscription', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('freeSubscription.index') }}">
                            <span>اشتراک و تماس رایگان</span>
                        </a>
                    </li>
                @endif
                @if (in_array('driversInMonth', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('report.driversInMonth') }}">
                            <span>فعالیت رانندگان غیر تکراری</span>
                        </a>
                    </li>
                @endif
                @if (in_array('driverActivityReport', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/driverActivityReport') }}">
                            <div data-i18n="Without menu">فعالیت رانندگان</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/driversPaymentReport') }}">
                            <div data-i18n="Without menu">پرداخت رانندگان</div>
                        </a>
                    </li>
                @endif
                @if (in_array('driversContactCall', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a href="{{ route('report.driversContactCall') }}" class="menu-link">
                            <div data-i18n="Without menu">ناوگان بر اساس تماس</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('report.driversCountCall') }}" class="menu-link">
                            <div data-i18n="Without menu">رانندگان بر اساس بیشترین تماس</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a href="{{ route('report.driversActivitiesCallDate') }}" class="menu-link">
                            <div data-i18n="Without menu">فعالیت رانندگان بر اساس زمان (امروز)</div>
                        </a>
                    </li>
                @endif
                @if (in_array('summaryOfDaysReport', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a href="{{ url('admin/summaryOfDaysReport') }}" class="menu-link">
                            <div data-i18n="Without menu">خلاصه گزارش روز</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/transportationCompaniesActivityReport') }}">
                            <div data-i18n="Without menu">فعالیت باربری ها</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/cargoOwnersActivityReport') }}">
                            <div data-i18n="Without menu"> فعالیت صاحب بارها</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/operatorsActivityReport') }}">
                            <div data-i18n="Without menu"> فعالیت اپراتورها</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/combinedReports') }}">
                            <div data-i18n="Without menu"> گزارش های ترکیبی</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/driverInstallationInLast30Days') }}">
                            <div data-i18n="Without menu"> نصب رانندگان در 30 روز</div>
                        </a>
                    </li>
                @endif

                @if (in_array('fleetRatioToDriverActivityReport', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/fleetRatioToDriverActivityReport') }}">
                            <div data-i18n="Without menu">نسبت راننده به بار</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>

        @if (in_array('paymentReport', auth()->user()->userAccess))
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-credit-card"></i>
                    <div data-i18n="pais">پرداخت ها</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/paymentReport') }}/{{ ROLE_DRIVER }}/100">
                            <div data-i18n="Without menu">راننده ها</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/mostPaidDriversReport') }}">
                            <div data-i18n="Without menu">بیشترین پرداخت رانندگان</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/paymentByFleetReport') }}">
                            <div data-i18n="Without menu">پرداخت براساس ناوگان</div>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        <li
            class="menu-item {{ request()->is('admin/loadOwner*') ? 'active open' : '' }} {{ request()->is('admin/loadOperators*') ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-check-shield"></i>
                <div data-i18n="pais">صاحبان بار</div>
            </a>

            <ul class="menu-sub">
                {{-- @if (in_array('operatorsWorkingHoursActivityReport', auth()->user()->userAccess)) --}}
                @if (auth()->user()->role == 'admin' || auth()->user()->id == 29)
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('owner.index') }}">
                            <div data-i18n="Without menu">لیست صاحبان بار</div>
                        </a>
                    </li>
                @endif
                <li
                    class="menu-item {{ request()->is('admin/loadOwner*') ? 'active open' : '' }} {{ request()->is('admin/loadOperators*') ? 'active open' : '' }}">
                    <a href="javascript:void(0);" class="menu-link menu-toggle">
                        {{-- <i class="menu-icon tf-icons bx bx-bar-chart"></i> --}}
                        <div data-i18n="Layouts">بار های ثبت شده</div>
                    </a>
                    <ul class="menu-sub">
                        <li class="menu-item">
                            <a class="menu-link" href="{{ route('admin.load.operator') }}">
                                <span>اپراتور</span>
                            </a>
                        </li>
                        <li class="menu-item">
                            <a class="menu-link" href="{{ route('admin.load.owner') }}">
                                <span>صاحب بار</span>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- @endif --}}
            </ul>
        </li>
        @if (in_array('complaints', auth()->user()->userAccess))
            <li class="menu-item {{ request()->is('admin/report*') ? 'active open' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bx-printer"></i>
                    <div data-i18n="pais">شکایات و انتقادات</div>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/complaintsDriversList') }}">
                            <div data-i18n="Without menu"> رانندگان</div>
                        </a>
                    </li>
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('complaint.owner.list') }}">
                            <div data-i18n="Without menu">صاحبان بار</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('blockedPhoneNumber.index') }}">
                            <span>مسدودی ها</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/blockedIps') }}">
                            <span>IP های های مسدود</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/messages') }}">
                            <span>پیام ها</span>
                        </a>
                    </li>

                    {{-- <li class="menu-item">
                        <a class="menu-link" href="{{ route('report.index', ['type' => 'owner']) }}">
                            <span>گزارش تخلف</span>
                        </a>
                    </li> --}}
                    <li class="menu-item {{ request()->is('admin/report*') ? 'active open' : '' }}">
                        <a href="javascript:void(0);" class="menu-link menu-toggle">
                            {{-- <i class="menu-icon tf-icons bx bx-bar-chart"></i> --}}
                            <div data-i18n="Layouts">گزارش تخلف</div>
                        </a>
                        <ul class="menu-sub">
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('report.index', ['type' => 'owner']) }}">
                                    <span>صاحب بار</span>
                                </a>
                            </li>
                            <li class="menu-item">
                                <a class="menu-link" href="{{ route('report.index', ['type' => 'driver']) }}">
                                    <span>راننده</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                    {{-- <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/complaintsTransportationCompanyList') }}">
                            <div data-i18n="Without menu"> باربری ها(نسخه قدیمی)</div>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/complaintsCustomerList') }}">
                            <div data-i18n="Without menu">صاحب بار(نسخه قدیمی)</div>
                        </a>
                    </li> --}}
                </ul>
            </li>
        @endif
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bxs-car"></i>
                <div data-i18n="Layouts">ناوگان</div>
            </a>

            <ul class="menu-sub">
                @if (in_array('fleet', auth()->user()->userAccess) || auth()->user()->role == 'admin')
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('fleet.index') }}">
                            <span>ناوگان ها</span>
                        </a>
                    </li>
                @endif

                <li class="menu-item">
                    <a class="menu-link" href="{{ url('admin/operatorFleets') }}">
                        <span>ناوگان من</span>
                    </a>
                </li>
            </ul>
        </li>
        <li class="menu-item">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons bx bx-check-shield"></i>
                <div data-i18n="pais">امکانات</div>
            </a>

            <ul class="menu-sub">
                @if (in_array('operatorsWorkingHoursActivityReport', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/operatorsWorkingHoursActivityReport') }}">
                            <div data-i18n="Without menu">میزان فعالیت اپراتورها</div>
                        </a>
                    </li>
                @endif

                @if (in_array('searchLoads', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/searchLoadsForm') }}">
                            <span>جستحوی بارها</span>
                        </a>
                    </li>
                @endif

                @if (in_array('operators', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/operators') }}">
                            <span>اپراتورها</span>
                        </a>
                    </li>
                @endif
                @if (in_array('drivers', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('drivers') }}">
                            <span>رانندگان</span>
                        </a>
                    </li>
                @endif


                @if (in_array('contactReportWithCargoOwners', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/contactReportWithCargoOwners') }}">
                            <span>تماس با صاحب بار و باربری</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/contactingWithDrivers') }}">
                            <span>تماس با رانندگان</span>
                        </a>
                    </li>
                @endif


                @if (in_array('appVersions', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/appVersions') }}">
                            <span>ورژن اپلیکیشن ها</span>
                        </a>
                    </li>
                @endif
                @if (in_array('provincesAndCities', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('provinceCity.index') }}">
                            <span>استان ها و شهرها</span>
                        </a>
                    </li>
                @endif

                {{-- @if (in_array('SOSList', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/SOSList/0') }}">
                            <div data-i18n="Without menu">درخواست های امداد</div>
                        </a>
                    </li>
                @endif --}}


                @if (in_array('services', auth()->user()->userAccess))
                    <li class="menu-item">
                        <a class="menu-link" href="{{ url('admin/services') }}">
                            <div data-i18n="Without menu">خدمات</div>
                        </a>
                    </li>
                @endif
            </ul>
        </li>
        @if (in_array('radios', auth()->user()->userAccess))
            <li class="menu-item">
                <a href="javascript:void(0);" class="menu-link menu-toggle">
                    <i class="menu-icon tf-icons bx bxs-radio"></i>
                    <div data-i18n="Layouts">رسانه</div>
                </a>
                <ul class="menu-sub">
                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('slider.index') }}">
                            <span>اسلایدر</span>
                        </a>
                    </li>

                    <li class="menu-item">
                        <a class="menu-link" href="{{ route('radio.index') }}">
                            <span>رادیو</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if (in_array('setting', auth()->user()->userAccess))
            <li class="menu-item">
                <a href="{{ route('setting.index') }}" class="menu-link">
                    <i class="menu-icon tf-icons bx bx-cog"></i>
                    <div data-i18n="Analytics">تنظیمات</div>
                </a>
            </li>
        @endif
    </ul>
</aside>
