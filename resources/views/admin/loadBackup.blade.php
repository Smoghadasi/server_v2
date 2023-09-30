@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بار های ثبت شده توسط صاحبین بار
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان بار</th>
                    <th>شماره موبایل</th>
                    <th>باربری یا صاحب بار</th>
                    <th>ناوگان</th>
                    <th>تاریخ</th>
                    <th>نمایش</th>
                </tr>
                </thead>
                <tbody>
                @foreach($loads as $load)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $load->title }}</td>
                        <td>{{ $load->senderMobileNumber }}</td>
                        <td>{{ $load->userType == ROLE_CUSTOMER ? 'صاحب بار' : 'باربری' }}</td>
                        <td>
                            @php
                                $fleets = json_decode($load->fleets, true);
                            @endphp
                    @foreach ($fleets as $fleet)
                                <span class="alert alert-primary p-1 m-1 small" style="line-height: 2rem">{{ $fleet['title'] }}</span>
                            @endforeach
                        </td>
                        <td>{{ $load->loadingDate }}</td>
                        <td><a href="{{ url('admin/loadInfo') }}/{{ $load->id }}">نمایش جزئیات</a></td>
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
