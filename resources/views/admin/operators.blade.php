@extends('layouts.dashboard')

@section('content')

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

                                @if ($user->status == 0)
                                    <a class="btn btn-sm btn-warning"
                                        href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                                        تغییر به فعال
                                    </a>
                                @else
                                    <a class="btn btn-sm btn-success"
                                        href="{{ url('admin/changeOperatorStatus') }}/{{ $user->id }}">
                                        تغییر به غیرفعال
                                    </a>
                                @endif


                                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#removeOperator_{{ $user->id }}">حذف
                                </button>

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

                                <button type="button" class="btn btn-primary btn-sm m-1" data-bs-toggle="modal"
                                    data-bs-target="#operatorAccess_{{ $user->id }}">
                                    دسترسی ها
                                </button>
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
                                                        <input type="checkbox" name="loads"
                                                            @if (in_array('loads', $user->userAccess)) checked @endif>
                                                        بارها
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
                                                        <input type="checkbox" name="contactReportWithCargoOwners"
                                                            @if (in_array('contactReportWithCargoOwners', $user->userAccess)) checked @endif>
                                                        تماس با صاحبان بار و باربری ها
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
                                                        <input type="checkbox" name="fleet"
                                                            @if (in_array('fleet', $user->userAccess)) checked @endif>
                                                        ناوگان
                                                    </label>
                                                </div>

                                                <div class="form-group">
                                                    <label>
                                                        <input type="checkbox" name="complaints"
                                                            @if (in_array('complaints', $user->userAccess)) checked @endif>
                                                        شکایات و انتقادها
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
                                                        <input type="checkbox" name="driversAuthentication"
                                                            @if (in_array('driversAuthentication', $user->userAccess)) checked @endif>
                                                        احراز هویت رانندگان
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
                                                        <input type="checkbox" name="services"
                                                            @if (in_array('services', $user->userAccess)) checked @endif>
                                                        خدمات ها
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


                                <button type="button" class="btn btn-info btn-sm m-1" data-bs-toggle="modal"
                                    data-bs-target="#operatorCargoListAccess_{{ $user->id }}">
                                    دسترسی ثبت بار
                                </button>
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


@stop
