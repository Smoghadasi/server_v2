@extends('layouts.dashboard')

@section('content')
    <div class="card shadow-lg border-0">
        <h5 class="card-header">
            افزودن قرارداد
        </h5>
        <div class="card-body">
            @include('partials.error')

            <form method="POST" action="{{ route('collaboration.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="contractNumber" class="form-label"><i class="fas fa-hashtag"></i> شماره قرارداد</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="contractNumber" id="contractNumber" value="{{ old('contractNumber') }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="fromDate" class="form-label"><i class="menu-icon tf-icons bx bx-calendar-alt"></i> تاریخ آغاز به کار</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="fromDate" id="fromDate" autocomplete="off" value="{{ old('fromDate') }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="toDate" class="form-label"><i class="menu-icon tf-icons bx bx-calendar"></i> تاریخ پایان به کار</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="toDate" id="toDate" autocomplete="off" value="{{ old('toDate') }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="contractType" class="form-label"><i class="text-secondary"></i> نوع قرارداد</label>
                        <select class="form-control form-select border-0 shadow-sm" name="contractType" id="contractType">
                            <option value="full-time">تمام وقت</option>
                            <option value="part-time">پاره وقت</option>
                            <option value="freelance">فریلنسری</option>
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="isInsurance" class="form-label"><i class="menu-icon tf-icons bx bx-shield"></i> بیمه</label>
                        <select class="form-control form-select border-0 shadow-sm" name="isInsurance" id="isInsurance">
                            <option value="yes">دارد</option>
                            <option value="no">ندارد</option>
                        </select>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="contractType" class="form-label"><i class="menu-icon tf-icons bx bx-file"></i> فایل قرارداد</label>
                        <input type="file" class="form-control" name="contract_file" id="contract_file" accept="image/*" required>
                    </div>

                    <input type="hidden" name="contract_id" value="{{ $contract->id }}">
                </div>

                <button type="submit" class="btn btn-outline-primary btn-lg">
                    <i class="fas fa-save"></i> ذخیره قرارداد
                </button>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>
    <script type="text/javascript">
        $("#fromDate").persianDatepicker();
        $("#toDate").persianDatepicker();
    </script>
@endsection
