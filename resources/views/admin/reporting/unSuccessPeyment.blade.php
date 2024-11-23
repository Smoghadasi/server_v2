@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش پرداخت کاربران
        </h5>
        <div class="card-body">

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
                        <th>بانک</th>
                        <th>تاریخ پرداخت</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
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
                            @php
                                $pieces = explode(' ', $transaction->updated_at);
                            @endphp
                            <td>{{ $transaction->bank_name }}</td>
                            <td dir="ltr">
                                {{ gregorianDateToPersian($transaction->updated_at, '-', true) . ' ' . $pieces[1] }}
                            </td>
                            <td>
                                @if($transaction->status == 0)
                                    <span class="badge bg-label-danger text-nowrap">پرداخت ناموفق</span>
                                @elseif($transaction->status == 100 || $transaction->status == 101)
                                    <span class="badge bg-label-success text-nowrap">پرداخت شده</span>
                                @else
                                    <span class="badge bg-label-secondary text-nowrap">بدون وضعیت</span>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm" href="{{ route('driver.detail', $transaction->user_id) }}">
                                    تمدید اعتبار
                                </a>
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
