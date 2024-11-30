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
                        @if(isset($driver->mobileNumber)) value="{{ $driver->mobileNumber }}" @endif>
                    </div>
                    <button class="btn btn-primary col-lg-2">جستجو</button>
                </form>
            </div>

            @if(isset($driver->id))

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
                        <th>تاریخ</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($transactions as $key => $transaction)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $transaction->amount }}</td>
                            <td>
                                @if($transaction->status > 0)
                                    <span class="alert alert-success m-1 p-1">
                                        پرداخت شده
                                    </span>
                                @else
                                    <span class="alert alert-danger m-1 p-1">
                                        پرداخت نشده
                                    </span>
                                @endif
                            </td>
                            <td>
                                {{ $transaction->payment_type == 'online' ? 'آنلاین' : 'کارت به کارت' }}
                            </td>
                            <td>{{ gregorianDateToPersian(str_replace('-','/',$transaction->created_at), '/',true) }}</td>
                        </tr>
                    @empty
                    <tr class="text-center">
                        <td colspan="10">فیلد مورد نظر خالی می باشد</td>
                    </tr>
                    @endforelse
                    </tbody>
                </table>

                @if($transactions!=[])
                    {{ $transactions }}
                @endif
            @endif
        </div>
    </div>

@stop



