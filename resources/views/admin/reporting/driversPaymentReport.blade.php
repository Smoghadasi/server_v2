@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش پرداختی رانندگان
        </h5>
        <div class="card-body">

            <div>
                <form action="{{ route('drivers-payment-report') }}" method="post" class="row mb-1">
                    @csrf
                    <div class="col-lg-3">
                        <input type="tel" class="form-control" name="mobileNumber" placeholder="شماره تلفن"
                            @if (isset($driver->mobileNumber)) value="{{ $driver->mobileNumber }}" @endif>
                    </div>
                    <button class="btn btn-primary col-lg-2">جستجو</button>
                </form>
            </div>

            @if (isset($driver->id))

                <div class="col-lg-12 mt-3 pt-3 border-top">
                    نام و نام خانوادگی راننده :
                    {{ $driver->name }}
                    {{ $driver->lastName }}
                    - شماره تلفن :
                    {{ $driver->mobileNumber }}
                </div>

                <table class="table mt-2">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>مبلغ</th>
                            <th>وضعیت</th>
                            <th>نوع</th>
                            <th>بانک</th>
                            <th>تاریخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transactions as $key => $transaction)
                            <tr>
                                <td>{{ $key + 1 }}</td>
                                <td>{{ $transaction->amount }}</td>
                                <td>
                                    @if ($transaction->status > 2)
                                        <span class="alert alert-success m-1 p-1">
                                            پرداخت شده
                                        </span>
                                    @elseif ($transaction->status == 2)
                                        <span class="alert alert-warning m-1 p-1">
                                            در انتظار پرداخت
                                        </span>
                                    @else
                                        <span class="alert alert-danger m-1 p-1">
                                            پرداخت نشده
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $transaction->bank_name }}</td>
                                <td>
                                    @switch($transaction->payment_type)
                                        @case('online')
                                            آنلاین
                                        @break

                                        @case('cardToCard')
                                            کارت به کارت
                                        @break

                                        @default
                                            هدیه
                                    @endswitch

                                </td>
                                @php
                                    $pieces = explode(' ', $transaction->created_at);
                                @endphp
                                <td>{{ gregorianDateToPersian(str_replace('-', '/', $transaction->created_at), '/', true) }}
                                    {{ $pieces[1] }}
                                </td>
                            </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="10">فیلد مورد نظر خالی می باشد</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    @if ($transactions != [])
                        {{ $transactions }}
                    @endif
                @endif
            </div>
        </div>

    @stop
