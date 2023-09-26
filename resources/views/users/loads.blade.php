<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth('bearing')->check())

@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif

@extends($dashboard)

@section('content')


    <div class="card">
        <h5 class="card-header">
            @if(isset($pageTitle))
                {{ $pageTitle }}
            @else
                بارهای ثبت شده من
            @endif
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان بار</th>
                    <th>وزن</th>
                    <th>مبدا</th>
                    <th>مقصد</th>
                    <th>قیمت شما</th>
                    <th>تاریخ</th>
                    {{--<th>ناوگان</th>--}}
                    <th>نمایش</th>
                </tr>
                </thead>
                <tbody id="newLoadsRows">
                <?php $i = 0;?>
                @foreach($loads as $load)

                    <?php $suggestionPrice = \App\Http\Controllers\TenderController::requestBearingPriceInTender($load->id, auth('bearing')->id()); ?>

                    <tr
                        @if($suggestionPrice>0)
                        style="background: #d7f3e3"
                        @endif
                    >
                        <td>{{ ++$i }}</td>
                        <td>{{ $load->title }}</td>
                        <td>{{ $load->weight }} تن</td>
                        <td>{{ \App\Http\Controllers\AddressController::geCityName($load->origin_city_id) }}</td>
                        <td>{{ \App\Http\Controllers\AddressController::geCityName($load->destination_city_id) }}</td>
                        <td>
                            @if($suggestionPrice>0)
                                {{ $suggestionPrice }} تومان
                            @else
                                ثبت نشده
                            @endif
                        </td>
                        <td>{{ $load->loadingDate }}</td>
                        {{--<td>--}}
                        {{--<img src="{{ url($load->fleetPic) }}" class="img-thumbnail ml-2" width="32" height="32">--}}
                        {{--                                        {{ $load->fleetTitle }}</td>--}}
                        <td><a href="{{ url('user/loadInfo') }}/{{ $load->id }}">نمایش جزئیات </a></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>



    @if(auth('bearing')->check() && isset($loadListType) && $loadListType=='newLoad')
        <script>

            var bearing_id = "{{ auth('bearing')->id() }}";
            var timer = setInterval(requestNewLoads, 60000);

            function requestNewLoads() {
                window.reload();
                // $.ajax({
                //     url: "/api/v1/bearing/requestNewLoads/" + bearing_id,
                //     success: function (result) {
                //         if (result.result == 1) {
                //             $("#newLoadsRows").remove();
                //             var len = (result.loads).length;
                //             for (var index = 0; index < len; index++) {
                //                 var load=result.loads[index];
                //
                //             }
                //         }
                //     },
                //     error: function () {
                //         // alert("error");
                //     }
                // });
            }
        </script>
    @endif
@stop
