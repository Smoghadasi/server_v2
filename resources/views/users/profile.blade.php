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
            پروفایل
        </li>
        {{--<li class="breadcrumb-item active">Overview</li>--}}
    </ol>

    @if(isset($message))
        <div class="alert alert-success text-right">{{ $message }}</div>
    @endif

    @if($userType=='customer')
        <div class="container text-right">
            <div class="table-responsive">
                <table class="table table-responsive">
                    <tbody>
                    <tr>
                        <td>نام :</td>
                        <td>{{ $customer->name }}</td>
                    </tr>
                    <tr>
                        <td>نام خانوادگی :</td>
                        <td>{{ $customer->lastName }}</td>
                    </tr>
                    @if(strlen($customer->nationalCode) && $customer->userType == REAL_PERSONALITY)
                        <tr>
                            <td>کد ملی :</td>
                            <td>
                                {{ $customer->nationalCode }}
                            </td>
                        </tr>
                    @endif
                    <tr>
                        <td>شماره همراه :</td>
                        <td>{{ $customer->mobileNumber }}</td>
                    </tr>
                    @if(strlen($customer->nationalCardPic) && $customer->userType == REAL_PERSONALITY)
                        <tr>
                            <td>تصویر کارت ملی :</td>
                            <td>
                                <img width="200" class="img-thumbnail"
                                     src="{{ url("/") }}/{{ $customer->nationalCardPic }}"/>
                            </td>
                        </tr>
                    @endif
                    @if($customer->userType == LEGAL_PERSONALITY)
                        <tr>
                            <td>نام شرکت :</td>
                            <td>
                                {{ $customer->legalPersonality->companyName }}
                            </td>
                        </tr>

                        @if(strlen($customer->legalPersonality->companyType))
                            <tr>
                                <td>نوع شرکت :</td>
                                <td>
                                    {{ $customer->legalPersonality->companyType }}
                                </td>
                            </tr>
                        @endif
                        @if(strlen($customer->legalPersonality->registrationNumber))
                            <tr>
                                <td>شماره ثبت :</td>
                                <td>
                                    {{ $customer->legalPersonality->registrationNumber }}
                                </td>
                            </tr>
                        @endif

                        @if(strlen($customer->legalPersonality->nationalID))
                            <tr>
                                <td>شناسه ملی :</td>
                                <td>
                                    {{ $customer->legalPersonality->nationalID }}
                                </td>
                            </tr>
                        @endif

                        @if(strlen($customer->legalPersonality->cityCode))
                            <tr>
                                <td>کد شهر :</td>
                                <td>
                                    {{ $customer->legalPersonality->cityCode }}
                                </td>
                            </tr>
                        @endif


                        @if(strlen($customer->legalPersonality->phoneNumber))
                            <tr>
                                <td>شماره تلفن ثابت :</td>
                                <td>
                                    {{ $customer->legalPersonality->phoneNumber }}
                                </td>
                            </tr>
                        @endif

                        @if(strlen($customer->legalPersonality->address))
                            <tr>
                                <td>آدرس :</td>
                                <td>
                                    {{ $customer->legalPersonality->address }}
                                </td>
                            </tr>
                        @endif

                        @if(strlen($customer->legalPersonality->email))
                            <tr>
                                <td>ایمیل :</td>
                                <td>
                                    {{ $customer->legalPersonality->email }}
                                </td>
                            </tr>
                        @endif

                    @endif

                    </tbody>
                </table>
            </div>
        </div>
    @elseif($userType=='bearing')
        <div class="container text-right">
            <div class="table-responsive">
                <table class="table table-responsive">
                    <tbody>
                    <tr>
                        <td>عنوان باربری :</td>
                        <td>{{ $bearing->title }}</td>
                    </tr>
                    <tr>
                        <td>نام اپراتور :</td>
                        <td>{{ $bearing->operatorName }}</td>
                    </tr>
                    <tr>
                        <td>شماره ثبت :</td>
                        <td>{{ $bearing->registrationNumber }}</td>
                    </tr>
                    <tr>
                        <td>شهر مبدا :</td>
                        <td>{{ \App\Http\Controllers\AddressController::geCityName($bearing->city_id) }}</td>
                    </tr>
                    <tr>
                        <td>شماره تلفن ثابت :</td>
                        <td>{{ $bearing->phoneNumber }}</td>
                    </tr>
                    <tr>
                        <td>شماره همراه :</td>
                        <td>{{ $bearing->mobileNumber }}</td>
                    </tr>
                    <tr>
                        <td>موجودی کیف پول :</td>
                        <td>{{ $bearing->wallet }} تومان</td>
                    </tr>
                    <tr>
                        <td>گرید باربری :</td>
                        <td>{{ $bearing->grade }}</td>
                    </tr>
                    <tr>
                        <td>امتیاز :</td>
                        <td>{{ $bearing->score }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    @endif


@stop
