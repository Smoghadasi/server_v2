@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارهای
            {{ \App\Http\Controllers\BearingController::getBearingTitle($bearing_id) }}
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
                    <th>نمایش</th>
                </tr>
                </thead>
                <tbody>
                <?php $i = 0;?>
                @foreach($loads as $load)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $load->title }}</td>
                        <td>{{ $load->mobileNumberForCoordination }}</td>
                        <td>{{ $load->fromCity }}</td>
                        <td>{{ $load->toCity }}</td>
                        <td>{{ $load->priceBased }}</td>
                        <td>
                            @php
                                $fleets = json_decode($load->fleets, true);
                            @endphp
                            @foreach ($fleets as $fleet)
                                <span class="alert alert-primary p-1 m-1 small" style="line-height: 2rem">{{ $fleet['title'] }}</span>
                            @endforeach
                        </td>
                        <td><a href="{{ url('admin/loadInfo') }}/{{ $load->id }}">نمایش جزئیات</a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

@stop
