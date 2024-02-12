@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بار های ثبت شده توسط صاحبان بار ({{ $loadsCount }})
        </h5>
        <div class="card-body">
            <div class="col-lg-12 m-2 mb-3 text-right">
                <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-success">تعداد بار های ثبت شده امروز:
                    {{ $loadsToday }}</a>
            </div>
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
                            @php
                                $pieces = explode(' ', $load->created_at);
                            @endphp
                            <td>{{ $load->loadingDate }} <br /> {{ $pieces[1] }}</td>
                            <td>
                                <a class="btn btn-info btn-sm"
                                    href="{{ route('loadInfo', $load->id) }}">جزئیات</a>
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
