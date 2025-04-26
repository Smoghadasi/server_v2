@extends('layouts.dashboard')

@section('content')
    <div class="card shadow-lg border-0">
        <h5 class="card-header bg-primary text-white">
            ویرایش قرارداد
        </h5>
        <div class="card-body">
            @include('partials.error')

            <form method="POST" action="{{ route('collaboration.update', $collaboration) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="mb-3 col-md-6">
                        <label for="contractNumber" class="form-label"><i class="fas fa-hashtag"></i> شماره قرارداد</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="contractNumber" id="contractNumber" value="{{ old('contractNumber', $collaboration->contractNumber) }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="fromDate" class="form-label"><i class="bx bx-calendar-alt"></i> تاریخ آغاز به کار</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="fromDate" id="fromDate" autocomplete="off" value="{{ old('fromDate', gregorianDateToPersian($collaboration->fromDate, '-', true)) }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="toDate" class="form-label"><i class="bx bx-calendar"></i> تاریخ پایان به کار</label>
                        <input class="form-control border-0 shadow-sm" required type="text" name="toDate" id="toDate" autocomplete="off" value="{{ old('toDate', gregorianDateToPersian($collaboration->toDate, '-', true)) }}" />
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="contractType" class="form-label"><i class="bx bx-briefcase"></i> نوع قرارداد</label>
                        <select class="form-control form-select border-0 shadow-sm" name="contractType" id="contractType">
                            <option value="full-time" {{ old('contractType', $collaboration->contractType) == 'full-time' ? 'selected' : '' }}>تمام وقت</option>
                            <option value="part-time" {{ old('contractType', $collaboration->contractType) == 'part-time' ? 'selected' : '' }}>پاره وقت</option>
                            <option value="freelance" {{ old('contractType', $collaboration->contractType) == 'freelance' ? 'selected' : '' }}>فریلنسری</option>
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="isInsurance" class="form-label"><i class="bx bx-shield"></i> بیمه</label>
                        <select class="form-control form-select border-0 shadow-sm" name="isInsurance" id="isInsurance">
                            <option value="1" {{ old('isInsurance', $collaboration->isInsurance) ? 'selected' : '' }}>دارد</option>
                            <option value="0" {{ !old('isInsurance', $collaboration->isInsurance) ? 'selected' : '' }}>ندارد</option>
                        </select>
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="contract_file" class="form-label"><i class="bx bx-file"></i> فایل قرارداد</label>
                        <input type="file" class="form-control border-0 shadow-sm" name="contract_file" id="contract_file" accept="image/*">
                        @if($collaboration->contract_file)
                            <div class="mt-2">
                                <img src="{{ asset($collaboration->contract_file) }}" width="200" class="img-fluid rounded shadow-sm" alt="تصویر قرارداد">
                            </div>
                        @endif
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg mt-3">
                    <i class="bx bx-save"></i> ذخیره تغییرات
                </button>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>
    <script>
        $("#fromDate").persianDatepicker();
        $("#toDate").persianDatepicker();
    </script>
@endsection
