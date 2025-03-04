@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-6">
                    اشتراک های دستی: {{ $driver->name ?? '-' }} {{ $driver->lastName ?? '-' }}
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
                            <th>مبلغ</th>
                            <th>نوع</th>
                            <th>تاریخ واریزی</th>
                            <th>توضیحات</th>
                            <th>جواب ادمین</th>
                            {{-- <th>تاریخ آخرین تماس</th> --}}
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small text-right">
                        <?php $i = 1; ?>
                        @forelse ($transactionManuals as $key => $transactionManual)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>
                                    {{ $transactionManual->driver->name }}
                                    {{ $transactionManual->driver->lastName }}
                                </td>

                                <td>
                                    {{ $transactionManual->driver->mobileNumber }}
                                </td>

                                <td>
                                    {{ \App\Http\Controllers\FleetController::getFleetName($transactionManual->driver->fleet_id) }}
                                </td>

                                <td>
                                    {{ number_format($transactionManual->amount) }}
                                </td>
                                <td>{{ $transactionManual->type }}</td>

                                <td>{{ $transactionManual->date }}</td>

                                <td>{{ $transactionManual->description ? Str::limit($transactionManual->description, 20, '...') : '-' }}
                                </td>
                                <td>{{ $transactionManual->result ? Str::limit($transactionManual->result, 20, '...') : '-' }}
                                </td>

                                <td class="text-center">
                                    @if ($transactionManual->status == 1)
                                        <i class="menu-icon tf-icons bx bx-check text-success"></i>
                                    @elseif ($transactionManual->status == 0)
                                        <i class="menu-icon tf-icons bx bx-x text-danger"></i>
                                    @else
                                        @if (Auth::user()->role == 'admin')
                                            <button type="button" class="btn btn-success btn-sm text-nowrap"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTransactionManual_{{ $transactionManual->id }}">
                                                تغییر وضعیت
                                            </button>
                                            <!-- Active -->
                                            <div class="modal fade" id="editTransactionManual_{{ $transactionManual->id }}"
                                                tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalCenterTitle">فعال سازی اشتراک
                                                                های
                                                                دستی
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <form
                                                            action="{{ route('transactionManual.change.status', $transactionManual) }}"
                                                            method="POST">
                                                            @csrf
                                                            @method('put')
                                                            <div class="modal-body" style="text-align: right">
                                                                <div class="row g-2">
                                                                    <div class="col-md-6 col-sm-12 mb-3">
                                                                        <label for="mobileNumber" class="form-label">شماره
                                                                            موبایل</label>
                                                                        <input type="text" id="mobileNumber"
                                                                            value="{{ $transactionManual->driver->mobileNumber }}"
                                                                            name="mobileNumber" class="form-control"
                                                                            required placeholder="شماره موبایل" />
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <label for="description"
                                                                            class="form-label">وضعیت</label>
                                                                        <select name="status" class="form-select"
                                                                            id="">
                                                                            <option selected value="1">فعال</option>
                                                                            <option value="0">غیر فعال</option>
                                                                        </select>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-outline-secondary"
                                                                    data-bs-dismiss="modal">
                                                                    انصرف
                                                                </button>
                                                                <button type="submit" class="btn btn-primary">ثبت</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm text-nowrap" data-bs-toggle="modal"
                                        data-bs-target="#adminMessageForm_{{ $transactionManual->id }}">
                                        ویرایش
                                    </button>
                                    @if (Auth::user()->role == 'admin')
                                        <button type="button" class="btn btn-danger btn-sm text-nowrap"
                                            data-bs-toggle="modal"
                                            data-bs-target="#removetransactionManual_{{ $transactionManual->id }}">حذف
                                        </button>
                                    @endif

                                    <!-- Remove -->
                                    <div id="removetransactionManual_{{ $transactionManual->id }}" class="modal fade"
                                        role="dialog">
                                        <div class="modal-dialog modal-dialog-centered">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف اشتراک دستی</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <strong>آیا مایل به حذف
                                                        هستید؟
                                                    </strong>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <form
                                                        action="{{ route('transaction-manual.destroy', $transactionManual) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('delete')
                                                        <button type="submit" class="btn btn-danger">حذف</button>
                                                    </form>
                                                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                    <!-- Edit -->
                                    <div class="modal fade" id="adminMessageForm_{{ $transactionManual->id }}"
                                        tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalCenterTitle">ویرایش اشتراک های دستی
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                        aria-label="Close"></button>
                                                </div>
                                                <form action="{{ route('transaction-manual.update', $transactionManual) }}"
                                                    method="POST">
                                                    @csrf
                                                    @method('put')
                                                    <div class="modal-body" style="text-align: right">
                                                        <div class="row g-2">
                                                            <div class="col-md-6 col-sm-12 mb-3">
                                                                <label for="mobileNumber" class="form-label">شماره
                                                                    موبایل</label>
                                                                <input type="text" id="mobileNumber"
                                                                    value="{{ $transactionManual->driver->mobileNumber }}"
                                                                    name="mobileNumber" class="form-control" required
                                                                    placeholder="شماره موبایل" />
                                                            </div>
                                                            <div class="col-md-6 col-sm-12 mb-0">
                                                                <label for="amount" class="form-label">مبلغ
                                                                    (تومان)
                                                                </label>
                                                                <input type="text" name="amount" id="amount"
                                                                    value="{{ $transactionManual->amount }}"
                                                                    class="form-control" required placeholder="مبلغ" />
                                                            </div>
                                                            <div class="col-md-6 col-sm-12 mb-0">
                                                                <label for="type" class="form-label">نوع</label>
                                                                <select class="form-control form-select"
                                                                    name="type"
                                                                    id="type-{{ $key }}" required>
                                                                    <option
                                                                        @if ($transactionManual->type == 'cardToCard') selected @endif
                                                                        value="cardToCard">کارت به کارت</option>
                                                                    <option
                                                                        @if ($transactionManual->type == 'online') selected @endif
                                                                        value="online">آنلاین</option>
                                                                    <option id="gift-{{ $key }}"
                                                                        @if ($transactionManual->type == 'gift') selected @endif
                                                                        value="gift">هدیه</option>
                                                                </select>
                                                            </div>
                                                            @php
                                                                $pieces = explode(' ', $transactionManual->date);
                                                            @endphp

                                                            <div class="col-md-3 col-sm-12 mb-0">
                                                                <label for="type" class="form-label">تاریخ</label>
                                                                <input class="form-control" type="text" id="fromDate"
                                                                    name="date" required placeholder="تاریخ"
                                                                    value="{{ $pieces[0] }}" />
                                                                <span id="span1"></span>
                                                            </div>

                                                            <div class="col-md-3 col-sm-12 mb-0">
                                                                <label for="type" class="form-label">ساعت</label>
                                                                <input value="{{ $pieces[1] }}" class="form-control"
                                                                    type="time" id="time" name="time" required
                                                                    placeholder="ساعت" autocomplete="off" />
                                                            </div>
                                                            <div class="col-md-6 col-sm-12 mb-0"
                                                                id="reagent-{{ $key }}">
                                                                <label for="reagent-{{ $key }}"
                                                                    class="form-label">تلفن تماس معرف</label>
                                                                <input name="reagent-{{ $key }}"
                                                                    class="form-control" value="{{ $transactionManual->reagent }}" type="text">
                                                            </div>
                                                            <div class="col-md-12">
                                                                <label for="description"
                                                                    class="form-label">توضیحات</label>
                                                                <textarea class="form-control" name="description">{{ $transactionManual->description }}</textarea>
                                                            </div>
                                                            @if (Auth::user()->role == 'admin')
                                                                <div class="col-md-12">
                                                                    <label for="result" class="form-label">جواب
                                                                        ادمین</label>
                                                                    <textarea class="form-control" name="result">{{ $transactionManual->result }}</textarea>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary"
                                                            data-bs-dismiss="modal">
                                                            انصرف
                                                        </button>
                                                        <button type="submit" class="btn btn-primary">ثبت</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

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
        $("#fromDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
        });


        $(document).ready(function() {
            // Function to handle the visibility of the div based on the select value
            function handleVisibility(id) {
                var selectedValue = $('#type-' + id).val();
                if (selectedValue === 'gift') {
                    $('#reagent-' + id).show();
                } else {
                    $('#reagent-' + id).hide();
                }
            }

            // Initial setup for each transaction
            @php
                $transactionCount = count($transactionManuals);
            @endphp

            for (let i = 0; i < {{ $transactionCount }}; i++) {
                handleVisibility(i);
            }

            // Event listener for change event
            $('select[name^="type-"]').change(function() {
                var id = $(this).attr('id').split('-')[1];
                handleVisibility(id);
            });
        });
    </script>
@endsection
