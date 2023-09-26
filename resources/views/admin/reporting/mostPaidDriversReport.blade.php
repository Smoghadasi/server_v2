@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش پرداخت رانندگان
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>نام پرداخت کننده</th>
                        <th>شماره تلفن</th>
                        <th>تعداد پرداخت</th>
                        <th>جمع کل مبلغ</th>
                        <th>تاریخ آخرین پرداخت</th>
                        <th>لیست پرداخت ها</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transactions as $key => $transaction)
                        <tr>
                            <td>
                                {{ ($transactions->currentPage() - 1) * $transactions->perPage() + ($key + 1) }}
                            </td>
                            <td>
                                {{ $transaction->payerName }}
                                <span class="alert alert-info p-1 m-1">{{ $transaction->driverFleetName }}</span>
                            </td>
                            <td>{{ $transaction->payerMobileNumber }}</td>
                            <td>{{ $transaction->total }}</td>
                            <td>{{ number_format($transaction->totalAmount) }}</td>
                            <td>
                                @if (isset($transaction->paymentDates[0]))
                                    <span class="alert alert-info p-1 m-1">{{ $transaction->paymentDates[0] }}</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('drivers-payment-report') }}" method="post">
                                    @csrf
                                    <input type="hidden" class="form-control" name="mobileNumber" placeholder="شماره تلفن"
                                        @if (isset($transaction->payerMobileNumber  )) value="{{ $transaction->payerMobileNumber   }}" @endif>
                                    <button class="btn btn-success btn-sm mb-1" type="submit">لیست پرداختی ها</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $transactions }}
            </div>

        </div>
    </div>



@stop
