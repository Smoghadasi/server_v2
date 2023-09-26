@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش پرداخت کاربران
        </h5>
        <div class="card-body">

            <div class="col-lg-12 m-2 mb-3 text-right">
                <a href="{{ url('admin/paymentReport') }}/{{ ROLE_DRIVER }}/100" class="alert p-1 alert-success">تعداد پرداخت
                    موفق : {{ number_format($counter['success']) }}</a>
                <a href="{{ url('admin/paymentReport') }}/{{ ROLE_DRIVER }}/0" class="alert p-1 alert-danger">تعداد پرداخت نا
                    موفق : {{ number_format($counter['unsuccess']) }}</a>
            </div>

            <div class="container text-right">
                <table class="table">
                    <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام پرداخت کننده</th>
                        <th>تعداد پرداخت موفق</th>
                        <th>تعداد کل تلاش ها</th>
                        <th>تعداد امروز</th>
                        <th>شماره تلفن</th>
                        <th>نوع کاربر</th>
                        <th>مبلغ پرداخت شده</th>
                        <th>تاریخ پرداخت</th>
                        <th>وضعیت</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php
                        $amount=0;
                    @endphp

                    @foreach($transactions as $key=> $transaction)

                        @php
                            $amount += $transaction->amount;
                        @endphp

                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $transaction->payerName }}</td>
                            <td>{{ $transaction->countOfSuccess }}</td>
                            <td>{{ $transaction->countOfAllTries }}</td>
                            <td>{{ $transaction->total }}</td>
                            <td>{{ $transaction->payerMobileNumber }}</td>
                            <td>{{ $transaction->userTypeTitle }}</td>
                            <td>{{ number_format($transaction->amount) }}</td>
                            <td>{{ $transaction->paymentDate }}</td>
                            <td>
                                @if($transaction->status == 0)
                                    <span class="badge bg-label-danger text-nowrap">پرداخت ناموفق</span>
                                @elseif($transaction->status == 100 || $transaction->status == 101)
                                    <span class="badge bg-label-success text-nowrap">پرداخت شده</span>
                                @else
                                    <span class="badge bg-label-secondary text-nowrap">بدون وضعیت</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>

                <div class="h5 mt-2 mb-2">
                    جمع کل :
                    {{ number_format($amount) }}
                </div>

                {{ $transactions }}
            </div>

        </div>
    </div>


@stop
