@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">لیست بار ها</div>
                <div class="col-6" style="text-align: left">
                    <a href="{{ route('owner.reject') }}" class="alert p-1 alert-danger">بار های فعال : {{ $loadsCount }}</a>
                    <a href="{{ route('owner.accept') }}" class="alert p-1 alert-success">بایگانی : {{ $loadsTrashedCount }}</a>

                </div>
            </div>
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان بار</th>
                        <th>شماره تلفن</th>
                        <th>مبدا</th>
                        <th>مقصد</th>
                        <th>قیمت</th>
                        <th>ناوگان</th>
                        <th>تاریخ</th>
                        <th>نمایش</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @forelse($loads as $key => $load)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>
                                @if ($load->deleted_at != null)
                                    <i class="menu-icon tf-icons bx bx-trash text-danger"></i>
                                @endif
                                {{ $load->title }}
                            </td>
                            <td>{{ $load->mobileNumberForCoordination }}</td>
                            <td>{{ $load->fromCity }}</td>
                            <td>{{ $load->toCity }}</td>
                            <td>{{ $load->priceBased }}</td>
                            <td>
                                @php
                                    $fleets = json_decode($load->fleets, true);
                                @endphp
                                @foreach ($fleets as $fleet)
                                    <span class="alert alert-primary p-1 m-1 small"
                                        style="line-height: 2rem">{{ $fleet['title'] }}</span>
                                @endforeach
                            </td>
                            @php
                                $pieces = explode(' ', $load->created_at);
                            @endphp
                            <td dir="ltr">
                                {{ gregorianDateToPersian($load->created_at, '-', true) . ' ' . $pieces[1] }}
                            </td>
                            <td><a href="{{ url('admin/loadInfo') }}/{{ $load->id }}">نمایش جزئیات</a></td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">فیلد مورد خالی است</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@stop
