@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            باربری
        </h5>
        <div class="card-body">

            <div class="mb-3">
                @if(isTransportationCompanyAutoActive())
                    تایید باربری ها بصورت خودکار
                    <a class="btn btn-danger" href="{{ url('admin/changeSiteOption/transportationCompanyAutoActive') }}">
                        تغییر به غیر خودکار
                    </a>
                @else
                    تایید باربری ها بصورت غیر خودکار
                    <a class="btn btn-primary" href="{{ url('admin/changeSiteOption/transportationCompanyAutoActive') }}">
                        تغییر به خودکار
                    </a>
                @endif
            </div>


            <p><a class="btn btn-primary" href="{{ url('admin/addNewBearingForm') }}"> + افزودن باربری جدید</a></p>
            <form method="post" action="{{ url('admin/bearing') }}" class="mt-3 mb-3 card card-body">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">
                        <label class="radio-inline">
                            <input type="radio" name="searchMethod" value="title"
                                   @if(isset($searchMethod) && $searchMethod=="title")
                                   checked
                                   @elseif(!isset($searchMethod))
                                   checked
                                   @endif
                                   onclick="showOrHideElementInBearingSearch('searchWord');">
                            نام باربری
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="searchMethod" value="operatorName"
                                   @if(isset($searchMethod) && $searchMethod=="operatorName")
                                   checked
                                   @endif
                                   onclick="showOrHideElementInBearingSearch('searchWord');">
                            نام متصدی
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="searchMethod" value="mobileNumber"
                                   @if(isset($searchMethod) && $searchMethod=="mobileNumber")
                                   checked
                                   @endif
                                   onclick="showOrHideElementInBearingSearch('searchWord');">
                            شماره تلفن همراه
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="searchMethod" value="phoneNumber"
                                   @if(isset($searchMethod) && $searchMethod=="phoneNumber")
                                   checked
                                   @endif
                                   onclick="showOrHideElementInBearingSearch('searchWord');">
                            شماره تلفن ثابت
                        </label>
                        <label class="radio-inline">
                            <input type="radio" name="searchMethod" value="city"
                                   @if(isset($searchMethod) && $searchMethod=="city")
                                   checked
                                   @endif
                                   onclick="showOrHideElementInBearingSearch('searchCity');">
                            شهر
                        </label>
                        <label class="radio-inline"><input type="radio" name="searchMethod" value="state"
                                                           @if(isset($searchMethod) && $searchMethod=="state")
                                                           checked
                                                           @endif
                                                           onclick="showOrHideElementInBearingSearch('searchState');">
                            استان</label>
                        <label class="radio-inline text-info">
                            <input type="radio" name="searchMethod" value="status_active"
                                   @if(isset($searchMethod) && $searchMethod=="status_active")
                                   checked
                                @endif>
                            باربری های فعال</label>
                        <label class="radio-inline text-danger">
                            <input type="radio" name="searchMethod" value="status_deactive"
                                   @if(isset($searchMethod) && $searchMethod=="status_deactive")
                                   checked
                                @endif>
                            باربری های غیرفعال</label>
                    </div>

                    <div class="mt-3 col-md-3">
                        <input class="form-control col-md-4" name="word" id="searchWord" placeholder="جستجو">
                    </div>

                    <div id="searchCity" class="col-md-4">
                        <select class="form-control col-md-12" name="city_id" id="city_id">
                            <option value="0">انتخاب شهر</option>
                            @foreach($cities as $city)
                                <option value="{{ $city->id }}">
                                    {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div id="searchState" class="col-md-4">
                        <select class="form-control col-md-12" name="state_id" id="state_id">
                            <option value="0">انتخاب استان</option>
                            @foreach($states as $state)
                                <option value="{{ $state->id }}">
                                    {{ str_replace('ك', 'ک', str_replace('ي', 'ی', $state->name)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mt-3 col-md-2">
                    <button class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
                @if(isset($message))
                    <div class="alert alert-info text-right">{{ $message }}</div>
                @endif
            </form>

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان باربری</th>
                    <th>نام متصدی</th>
                    <th> شهر</th>
                    <th>شماره تلفن</th>
                    <th>روز مانده به پایان اشتراک</th>
                    <th>تعداد تماس رایگان</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="small">
                <?php $i = 0;?>
                @foreach($bearings as $key => $bearing)
                    <tr>
                        <td>
                            {{ (($bearings->currentPage()-1) * $bearings->perPage()) + ($key + 1 ) }}
                        </td>
                        <td class="text-wra" style="width: 150px">{{ $bearing->title }}
                            @if($bearing->status==0)
                                <span class="alert alert-danger p-1">غیرفعال</span>
                            @else
                                <span class="alert alert-success p-1">فعال</span>
                            @endif
                        </td>
                        <td>
                            {{ $bearing->operatorName }}
                        </td>
                        <td>
                            {{ \App\Http\Controllers\AddressController::geStateName($bearing->state_id) }}
                            - {{ \App\Http\Controllers\AddressController::geCityName($bearing->city_id)  }}
                        </td>
                        <td>{{ $bearing->phoneNumber }} - {{ $bearing->mobileNumber }}</td>

                        <td>{{ $bearing->theDaysBeforeEndOfTheSubscription }} روز</td>
                        <td>{{ $bearing->countOfLoadsAfterValidityDate }}</td>

                        <td>

                            <a class="btn btn-sm btn-primary mb-1"
                               href="{{ url('admin/bearingLoads') }}/{{ $bearing->id }}">نمایش لیست بارها</a>

                            <a class="btn btn-sm btn-info mb-1"
                               href="{{ url('admin/editBearingInfoForm') }}/{{ $bearing->id }}">ویرایش</a>

                            @if($bearing->status==0)
                                <a class="btn btn-primary btn-sm mb-1"
                                   href="{{ url('admin/changeBearingStatus') }}/{{ $bearing->id }}">تغییر به
                                    فعال</a>
                            @else
                                <a class="btn btn-warning btn-sm mb-1"
                                   href="{{ url('admin/changeBearingStatus') }}/{{ $bearing->id }}">تغییر به غیر
                                    فعال</a>
                            @endif

                            @if(auth()->user()->role == 'admin')
                                <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                        data-bs-target="#removeTransportationCompany_{{ $bearing->id }}">حذف
                                </button>

                                <div id="removeTransportationCompany_{{ $bearing->id }}" class="modal fade"
                                     role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">حذف باربری</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>آیا مایل به حذف باربری
                                                    <span class="text-primary"> {{ $bearing->title }}</span>
                                                    هستید؟</p>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <a class="btn btn-primary"
                                                   href="{{ url('admin/removeTransportationCompany') }}/{{ $bearing->id }}">حذف
                                                    باربری</a>
                                                <button type="button" class="btn btn-danger"
                                                        data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            @endif

                            @if (auth()->user()->role == 'admin' && (strlen($bearing->ip) > 0 || $bearing->ip != null))
                            @if ($bearing->blockedIp == false)
                                <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#blockUserIp_{{ $bearing->id }}">مسدود کردن IP
                                </button>

                                <div id="blockUserIp_{{ $bearing->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">مسدود کردن IP</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>آیا مایل به مسدود کردن IP
                                                    <span class="text-primary"> {{ $bearing->name }}
                                                        {{ $bearing->lastName }}</span>
                                                    هستید؟
                                                </p>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <a class="btn btn-primary"
                                                    href="{{ url('admin/blockUserIp') }}/{{ $bearing->id }}/{{ ROLE_TRANSPORTATION_COMPANY }}/{{ $bearing->ip }}">
                                                    بله مسدود شود
                                                </a>
                                                <button type="button" class="btn btn-danger"
                                                    data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <button type="button" class="btn btn-success btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#unBlockUserIp_{{ $bearing->id }}">
                                    حذف از لیست Ipهای مسدود
                                </button>

                                <div id="unBlockUserIp_{{ $bearing->id }}" class="modal fade"
                                    role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">حذف از لیست Ipهای مسدود</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>آیا مایل به حذف کردن IP
                                                    <span class="text-primary">{{ $bearing->title }}</span>
                                                    از لیست مسدودها هستید؟
                                                </p>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <a class="btn btn-primary"
                                                    href="{{ url('admin/unBlockUserIp') }}/{{ $bearing->id }}/{{ ROLE_TRANSPORTATION_COMPANY }}">
                                                    بله از لیست حذف شود
                                                </a>
                                                <button type="button" class="btn btn-danger"
                                                    data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $bearings }}
            </div>


        </div>
    </div>



@stop
