@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    بار های صاحبان بار اپراتور ({{ $loadsCount }})
                </div>
                <div class="col-4" style="text-align: left">
                    @if (Auth::user()->role == 'admin')
                        @if (isSendBotLoadOwner())
                            ارسال بار ربات به بار های ثبت شده
                            <a class="btn btn-danger btn-sm" href="{{ url('admin/changeSiteOption/sendBotLoadOwner') }}">
                                غیر فعال
                            </a>
                        @else
                            ارسال بار ربات به بار های ثبت شده
                            <a class="btn btn-primary btn-sm" href="{{ url('admin/changeSiteOption/sendBotLoadOwner') }}">
                                فعال
                            </a>
                        @endif
                    @endif
                </div>
            </div>

        </h5>
        <div class="card-body">
            <div class="col-lg-12 m-2 mb-3 text-right">
                <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-success">تعداد بار های ثبت شده امروز:
                    {{ $loadsToday }}</a>
            </div>
            <form method="get" action="{{ route('admin.load.operator') }}">
                <div class="form-group row">
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="loadBy">
                            <option value="0">همه</option>
                            <option value="1">فعال</option>
                            <option value="2">بایگانی</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="fleet_id">
                            <option disabled selected>انتخاب ناوگان</option>
                            @foreach ($fleets as $fleet)
                                <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان بار</th>
                        <th>شماره موبایل</th>
                        <th>صاحب بار</th>
                        {{-- <th>باربری یا صاحب بار</th> --}}
                        <th>ناوگان</th>
                        <th>مبدا</th>
                        <th>مقصد</th>
                        <th>تعداد</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($loads as $load)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                @if ($load->deleted_at != null)
                                    <i class="menu-icon tf-icons bx bx-trash text-danger"></i>
                                @endif
                                {{ $load->title }}
                            </td>
                            <td>{{ $load->senderMobileNumber }}</td>
                            @if ($load->owner)
                                @if (Auth::user()->role == 'admin')
                                    <td>
                                        <a href="{{ route('owner.show', $load->owner->id) }}">
                                            {{ $load->owner->name }} {{ $load->owner->lastName }}
                                        </a>
                                    </td>
                                @else
                                    <td>
                                        {{ $load->owner->name }} {{ $load->owner->lastName }}
                                    </td>
                                @endif
                            @else
                                <td>
                                    @foreach ($operators as $operator)
                                        @if ($operator->id == $load->operator_id)
                                            صاحب بار / {{ $operator->name }} {{ $operator->lastName }}
                                        @endif
                                    @endforeach
                                </td>
                            @endif


                            {{--                        <td>{{ $load->userType == ROLE_CUSTOMER ? 'صاحب بار' : 'باربری' }}</td> --}}
                            <td>
                                @php
                                    $fleets = json_decode($load->fleets, true);
                                @endphp
                                @foreach ($fleets as $fleet)
                                    <span class="alert alert-primary p-1 m-1 small"
                                        style="line-height: 2rem">{{ $fleet['title'] }}</span>
                                @endforeach
                            </td>
                            <td>{{ $load->fromCity }}</td>
                            <td>{{ $load->toCity }}</td>
                            <td>
                                <a href="{{ route('admin.driverVisitLoads', $load) }}">
                                    <span class="badge bg-primary">بازدید : {{ $load->driverVisitLoadCount }}</span>
                                </a>
                                <span>
                                    <a class="badge bg-danger" href="{{ route('load.searchLoadInquiry', $load->id) }}">
                                        درخواست: {{ $load->numOfInquiryDrivers }}
                                    </a>

                                </span>
                                <span>
                                    <a class="badge bg-success" href="{{ route('load.searchLoadDriverCall', $load->id) }}">
                                        تماس: {{ $load->numOfDriverCalls }}
                                    </a>
                                </span>
                            </td>

                            <td>{{ $load->date }} {{ $load->dateTime }}</td>
                            <td>
                                <a class="btn btn-info btn-sm" href="{{ route('loadInfo', $load->id) }}">جزئیات</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-2 mb-2">
            {{ $loads->withQueryString()->links() }}
        </div>
    </div>

@stop
