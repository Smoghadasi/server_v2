@extends('layouts.dashboard')
@section('css')
    <style>
        .modal-backdrop {
            display: none !important;
        }
    </style>
@endsection
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
                                                <input type="text" name="amount" id="amount"
                                                    value="{{ MONTHLY }}" class="form-control" required
                                                    placeholder="مبلغ" />
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-0">
                                                <label for="type" class="form-label">نوع</label>
                                                <select class="form-control form-select" name="type" required>
                                                    <option value="cardToCard">کارت به کارت</option>
                                                    <option value="online">آنلاین</option>
                                                    <option id="gift" value="gift">هدیه</option>
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
                                            <div class="col-md-6 col-sm-12 mb-0" id="reagent">
                                                <label for="reagent" class="form-label">تلفن تماس معرف</label>
                                                <input name="reagent" class="form-control" type="text">
                                            </div>
                                            <div class="col-md-12">
                                                <label for="description" class="form-label">توضیحات</label>
                                                <textarea class="form-control" name="description"></textarea>
                                            </div>
                                            <div class="col-md-6 col-sm-12 mb-3">
                                                <label for="mobileNumber" class="form-label">تماس رایگان</label>
                                                <input type="number" value="0" max="3" min="0"
                                                    id="freeCalls" name="freeCalls" class="form-control"
                                                    placeholder="تماس رایگان" />
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
            <div class="nav-align-top mb-4">
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item">
                        <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-home" aria-controls="navs-top-home" aria-selected="true">
                            کارت به کارت
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-profile" aria-controls="navs-top-profile" aria-selected="false">
                            آنلاین
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                            data-bs-target="#navs-top-messages" aria-controls="navs-top-messages" aria-selected="false">
                            هدیه
                        </button>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane fade show active" id="navs-top-home" role="tabpanel">
                        @include('admin.transactionManual.partials.transaction-table', [
                            'transactionManuals' => $transactionManuals,
                            'type' => 'کارت به کارت',
                        ])
                    </div>

                    <div class="tab-pane fade" id="navs-top-profile" role="tabpanel">
                        @include('admin.transactionManual.partials.transaction-table', [
                            'transactionManuals' => $transactionManuals,
                            'type' => 'آنلاین',
                        ])
                    </div>

                    <div class="tab-pane fade" id="navs-top-messages" role="tabpanel">
                        @include('admin.transactionManual.partials.transaction-table', [
                            'transactionManuals' => $transactionManuals,
                            'type' => 'gift',
                        ])
                    </div>

                </div>
            </div>


        </div>
    </div>
    @if (Auth::user()->role == 'admin')
        <div class="card mt-4">
            <h5 class="card-header">
                <div class="row justify-content-between">
                    <div class="col-6">
                        واریزی های انجام شده
                    </div>
                </div>
            </h5>
            <div class="card-body">
                <div class="nav-align-top mb-4">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="nav-item">
                            <button type="button" class="nav-link active" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-home-complete" aria-controls="navs-top-home-complete"
                                aria-selected="true">
                                کارت به کارت
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-profile-complete" aria-controls="navs-top-profile-complete"
                                aria-selected="false">
                                آنلاین
                            </button>
                        </li>
                        <li class="nav-item">
                            <button type="button" class="nav-link" role="tab" data-bs-toggle="tab"
                                data-bs-target="#navs-top-gift-complete" aria-controls="navs-top-gift-complete"
                                aria-selected="false">
                                هدیه
                            </button>
                        </li>
                    </ul>
                    <div class="tab-content">
                        @php
                            $anotherTransactions = $oldtransactionManuals
                                ->merge($oldtransactionNonDrivers)
                                ->sortByDesc('miladiDate');
                        @endphp
                        <div class="tab-pane fade show active" id="navs-top-home-complete" role="tabpanel">
                            @include('admin.transactionManual.partials.transactionComplete-table', [
                                'anotherTransactions' => $anotherTransactions,
                                'type' => 'کارت به کارت',
                            ])
                        </div>

                        <div class="tab-pane fade" id="navs-top-profile-complete" role="tabpanel">
                            @include('admin.transactionManual.partials.transactionComplete-table', [
                                '$anotherTransactions' => $anotherTransactions,
                                'type' => 'آنلاین',
                            ])
                        </div>

                        <div class="tab-pane fade" id="navs-top-gift-complete" role="tabpanel">
                            @include('admin.transactionManual.partials.transactionComplete-table', [
                                '$anotherTransactions' => $anotherTransactions,
                                'type' => 'gift',
                            ])
                        </div>

                    </div>
                </div>
                <div class="h5 mt-2 mb-2">
                    جمع کل :
                    {{ number_format($anotherTransactions->sum('amount')) }} تومان
                </div>

            </div>
        </div>
    @endif
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

        $(document).ready(function() {
            $('select[name="type"]').change(function() {
                var selectedValue = $(this).val();
                if (selectedValue === 'gift') {
                    $('#reagent').show();
                    // $('input[name="reagent"]').attr('required', true);
                } else {
                    $('#reagent').hide();
                    // $('input[name="reagent"]').removeAttr('required');
                }
            });

            // Initially hide the div
            $('#reagent').hide();

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
