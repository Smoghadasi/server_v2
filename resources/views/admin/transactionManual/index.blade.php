@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    اشتراک های دستی (امروز)
                </div>
                <div class="col-6 text-end">
                    <!-- Button trigger modal -->
                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalCenter">
                        اشتراک جدید
                    </button>

                    <!-- Modal -->
                    <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="modalCenterTitle">اشتراک های دستی جدید</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <form action="{{ route('transaction-manual.store') }}" method="POST">
                                    @csrf
                                    <div class="modal-body" style="text-align: right">
                                        <div class="row g-2">
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="mobileNumber" class="form-label">شماره موبایل</label>
                                                <input type="text" id="mobileNumber" name="mobileNumber"
                                                    class="form-control" required placeholder="شماره موبایل" />
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-0">
                                                <label for="amount" class="form-label">مبلغ (تومان)</label>
                                                <input type="text" name="amount" id="amount" value="79000"
                                                    class="form-control" required placeholder="مبلغ" />
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-0">
                                                <label for="type" class="form-label">نوع</label>
                                                <select class="form-control form-select" name="type" required>
                                                    <option value="cardToCard">کارت به کارت</option>
                                                    <option value="online">آنلاین</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3 col-sm-12 mb-0">
                                                <label for="type" class="form-label">تاریخ</label>
                                                <input class="form-control" type="text" id="new" name="date"
                                                    required placeholder="تاریخ" autocomplete="off" />
                                            </div>
                                            <div class="col-md-3 col-sm-12 mb-0">
                                                <label for="type" class="form-label">ساعت</label>
                                                <input value="{{ now() }}" class="form-control" type="time"
                                                    id="time" name="time" required placeholder="ساعت"
                                                    autocomplete="off" />
                                            </div>
                                            <div class="col-md-12">
                                                <label for="description" class="form-label">توضیحات</label>
                                                <textarea class="form-control" name="description"></textarea>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            انصرف
                                        </button>
                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </h5>
        <div class="card-body">
            <form method="get" action="{{ route('transaction-manual.search') }}">
                <div class="form-group row mb-4">
                    <div class="col-md-2">
                        <lable class="form-label">شماره موبایل :</label>
                        <input class="form-control" name="mobileNumber" id="mobileNumber">
                    </div>
                    <div class="col-md-2">
                        <lable class="form-label">از تاریخ :</label>
                        <input class="form-control" type="text" id="fromDate" name="fromDate"
                            autocomplete="off" />
                        <span id="span1"></span>
                    </div>
                    <div class="col-md-2">
                        <lable class="form-label">تا تاریخ :</label>
                        <input class="form-control" type="text" name="toDate" id="toDate"
                            autocomplete="off" />
                        <span id="span2"></span>
                    </div>
                    <div class="col-md-2">
                        <lable class="form-label">وضعیت :</label>
                        <select class="form-control form-select" name="status">
                            <option></option>
                            <option value="1">فعال</option>
                            <option value="0">غیر فعال</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <button type="submit" class="btn btn-danger">جستجو</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>شماره همراه</th>
                            <th>ناوگان</th>
                            {{-- <th>مبلغ</th> --}}
                            {{-- <th>نوع</th> --}}
                            <th>تاریخ اولین واریز</th>
                            <th>تاریخ آخرین تماس</th>
                            <th>تاریخ آخرین اشتراک</th>
                            {{-- <th>وضعیت</th> --}}
                            {{-- <th>عملیات</th> --}}
                        </tr>
                    </thead>
                    <tbody class="small text-right">
                        <?php $i = 1; ?>
                        @forelse ($transactionManuals as $key => $transactionManual)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <a href="{{ route('transaction-manual.show', $transactionManual->driver_id) }}">
                                        {{ $transactionManual->driver->name }}
                                        {{ $transactionManual->driver->lastName }}
                                        {{ '(' . $transactionManual->total . ')' }}
                                    </a>
                                </td>

                                <td>{{ $transactionManual->driver->mobileNumber }}</td>

                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($transactionManual->driver->fleet_id) }}</td>

                                <td>
                                    {{ $transactionManual->lastPaymentDate }}
                                </td>
                                <td>
                                    {{ $transactionManual->firstPaymentDate }}
                                </td>
                                <td>
                                    {{ $transactionManual->lastActiveDate }}
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">
                                    دیتا مورد نظر یافت نشد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="card mt-4">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    اشتراک های کارت به کارت (روز های گذشته)
                </div>
            </div>
        </h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>شماره همراه</th>
                            <th>ناوگان</th>
                            <th>تاریخ اولین واریز</th>
                            <th>تاریخ آخرین تماس</th>
                            <th>تاریخ آخرین اشتراک</th>
                        </tr>
                    </thead>
                    <tbody class="small text-right">
                        <?php $i = 1; ?>
                        @forelse ($oldtransactionManuals as $key => $transactionManual)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    <a href="{{ route('transaction-manual.show', $transactionManual->driver_id) }}">
                                        {{ $transactionManual->driver->name }}
                                        {{ $transactionManual->driver->lastName }}
                                        {{ '(' . $transactionManual->total . ')' }}
                                    </a>
                                </td>

                                <td>{{ $transactionManual->driver->mobileNumber }}</td>

                                <td>{{ \App\Http\Controllers\FleetController::getFleetName($transactionManual->driver->fleet_id) }}</td>

                                <td>
                                    {{ $transactionManual->lastPaymentDate }}
                                </td>
                                <td>
                                    {{ $transactionManual->firstPaymentDate }}
                                </td>
                                <td>
                                    {{ $transactionManual->lastActiveDate }}
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">
                                    دیتا مورد نظر یافت نشد
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var now = new Date();
            var hours = now.getHours().toString().padStart(2, '0');
            var minutes = now.getMinutes().toString().padStart(2, '0');
            var currentTime = hours + ':' + minutes;

            document.getElementById('time').value = currentTime;
        });
        $("#fromDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });
        $("#toDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });
        $("#new").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
    </script>
@endsection
