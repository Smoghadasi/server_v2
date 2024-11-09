@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            گزارش پرداخت کاربران

            <button class="btn btn-sm btn-primary" data-bs-toggle="modal"
                data-bs-target="#addService"> + گزارش گیری
            </button>
            <div id="addService" class="modal fade" role="dialog">
                <div class="modal-dialog">
                    <form method="post" enctype="multipart/form-data" action="{{ route('view-pdf') }}" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">گزارش گیری</h4>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label class="form-label">از :</label>
                                <input
                                  type="text"
                                  name="from"
                                  class="form-control"
                                  placeholder="YY / MM / DD"
                                />
                            </div>
                            <div class="form-group mt-2">
                                <label class="form-label">تا :</label>
                                <input
                                  type="text"
                                  name="to"
                                  class="form-control"
                                  placeholder="YY / MM / DD"
                                />
                            </div>

                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary">
                                PDF
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
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
                        <th>ناوگان</th>
                        <th>مبلغ پرداخت شده</th>
                        <th>نوع</th>
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
                            <td>{{ $transaction->userTypeTitle }} </td>
                            <td>{{ $transaction->driverFleetName }} </td>
                            <td>{{ number_format($transaction->amount) }}</td>
                            <td>{{ $transaction->payment_type == 'online' ? 'آنلاین' : 'کارت به کارت' }}</td>
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
                            <td>
                                @if($transaction->status == 0)
                                    <a class="btn btn-primary btn-sm" href="{{ route('driver.detail', $transaction->user_id) }}">
                                        تمدید اعتبار
                                    </a>
                                @elseif($transaction->status == 100 || $transaction->status == 101)
                                <span class="d-inline-block" tabindex="0" data-toggle="tooltip" title="دسترسی فقط پرداخت ناموفق">
                                    <button class="btn btn-primary btn-sm" style="pointer-events: none;" type="button" disabled>تمدید اعتبار</button>
                                  </span>

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
