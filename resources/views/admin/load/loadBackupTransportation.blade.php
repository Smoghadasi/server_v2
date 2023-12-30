@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بار های ثبت شده توسط باربری
        </h5>
        <div class="card-body">
            <form action="{{ route('search.loadback.Transportation') }}" method="post">
                @csrf
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    {{-- <h6>جستجو : </h6> --}}
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>شماره تلفن :</label>
                                    <input type="text" name="mobileNumber" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>
                </div>
            </form>
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان بار</th>
                    <th>شماره موبایل</th>
                    <th>مشخصات باربری</th>
{{--                    <th>باربری یا صاحب بار</th>--}}
                    <th>ناوگان</th>
                    <th>تاریخ</th>
                    <th>نمایش</th>
                </tr>
                </thead>
                <tbody>
                @foreach($loads as $load)
                    <tr>
                        <td>{{ ($loads ->currentpage()-1) * $loads ->perpage() + $loop->index + 1 }}</td>
                        <td>{{ $load->title }}</td>
                        <td>{{ $load->senderMobileNumber }}</td>
                        <td>
                            {{ $load->bearing->operatorName }} ({{ $load->bearing->mobileNumber }})
                        </td>
                        <td>
                            @php
                                $fleets = json_decode($load->fleets, true);
                            @endphp
                            @foreach ($fleets as $fleet)
                                <span class="alert alert-primary p-1 m-1 small" style="line-height: 2rem">{{ $fleet['title'] }}</span>
                            @endforeach
                        </td>
                        @php
                            $pieces = explode(" ", $load->created_at);
                        @endphp
                        <td>{{ $load->loadingDate }} <br/> {{$pieces[1]}}</td>
                        <td>
                            <a class="btn btn-info btn-sm" href="{{ url('admin/loadInfo') }}/{{ $load->id }}">نمایش جزئیات</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('bearing.loads', $load->bearing->id) }}">لیست بار ها</a>
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
