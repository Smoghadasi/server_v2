@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    اشتراک های دستی
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
                                                <input class="form-control" type="text" id="fromDate" name="date"
                                                    required placeholder="تاریخ" autocomplete="off" />
                                                <span id="span1"></span>
                                            </div>
                                            <div class="col-md-3 col-sm-12 mb-0">
                                                <label for="type" class="form-label">ساعت</label>
                                                <input value="09:30" class="form-control" type="time" id="time"
                                                    name="time" required placeholder="ساعت" autocomplete="off" />
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
            <form method="get" action="{{ route('transaction-manual.index') }}">
                <div class="form-group row mb-4">
                    <div class="col-md-4">
                        <input class="form-control" name="mobileNumber" id="mobileNumber" placeholder="شماره موبایل">
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="text" id="fromDate" name="fromDate" placeholder="از تاریخ"
                            autocomplete="off" />
                        <span id="span1"></span>
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="text" name="toDate" id="fromDate" placeholder="تا تاریخ"
                            autocomplete="off" />
                        <span id="span2"></span>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-danger btn-sm mr-2">جستجو</button>
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
                            <th>مبلغ</th>
                            <th>نوع</th>
                            <th>تاریخ / ساعت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small text-right">
                        <?php $i = 1; ?>
                        @forelse ($transactionManuals as $key => $transactionManual)
                            @php
                                $totalItems = $transactionManuals->total();
                                $perPage = $transactionManuals->perPage();
                                $currentPage = $transactionManuals->currentPage();
                                $index = ($currentPage - 1) * $perPage + $key + 1;
                                $reverseIndex = $totalItems - $index + 1;
                            @endphp
                            <tr>
                                <td>{{ $reverseIndex }}
                                </td>
                                <td>
                                    {{ $transactionManual->driver->name }} {{ $transactionManual->driver->lastName }}
                                </td>

                                <td>
                                    {{ $transactionManual->driver->mobileNumber }}
                                </td>

                                <td>
                                    {{ \App\Http\Controllers\FleetController::getFleetName($transactionManual->driver->fleet_id) }}
                                </td>

                                <td>
                                    {{ $transactionManual->amount }}
                                </td>
                                <td>
                                    {{ $transactionManual->type }}
                                </td>
                                <td>
                                    {{ $transactionManual->date }}
                                </td>
                                <td class="text-center">
                                    @if ($transactionManual->status == 1)
                                        <i class="menu-icon tf-icons bx bx-check text-success"></i>
                                    @elseif ($transactionManual->status == 0)
                                        <i class="menu-icon tf-icons bx bx-x text-danger"></i>
                                    @else
                                        @if (Auth::user()->role == 'admin')
                                            <a class="btn btn-success btn-sm"
                                                href="{{ route('transactionManual.change.status', ['transactionManual' => $transactionManual, 'status' => 1]) }}">فعال
                                            </a>
                                            <a class="btn btn-danger btn-sm"
                                                href="{{ route('transactionManual.change.status', ['transactionManual' => $transactionManual, 'status' => 0]) }}">غیر
                                                فعال
                                            </a>
                                        @endif
                                    @endif
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

            <div class="mt-3">
                {{ $transactionManuals }}
            </div>

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #span1").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });
    </script>
@endsection
