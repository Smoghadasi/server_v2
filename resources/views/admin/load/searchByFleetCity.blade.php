@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    بار ها به تفکیک شهر و ناوگان ({{ $loads->total() }})
                </div>
            </div>

        </h5>
        <div class="card-body">
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
                                <span class="badge bg-primary">بازدید : {{ $load->driverVisitCount }}</span>
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
            {{ $loads }}
        </div>
    </div>

@stop
