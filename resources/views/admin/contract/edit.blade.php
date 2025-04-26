@extends('layouts.dashboard')

@section('content')
    <div class="card">

        <div class="card-body">
            @include('partials.error')
            <form method="POST" action="{{ route('contract.update', $contract->id) }}">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="fromDate" class="form-label">تاریخ آغاز به کار</label>
                        <input class="form-control" type="text" value="{{ old('fromDate', gregorianDateToPersian($contract->fromDate, '-', true)) }}"
                            id="fromDate" name="fromDate" autocomplete="off" />
                        <span id="span1"></span>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="toDate" class="form-label">تاریخ پایان به کار</label>
                        <input class="form-control" type="text" value="{{ old('toDate', gregorianDateToPersian($contract->toDate, '-', true)) }}"
                            id="toDate" name="toDate" autocomplete="off" />
                        <span id="span2"></span>
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="cardNumber" class="form-label">شماره کارت</label>
                        <input class="form-control" type="text" value="{{ old('cardNumber', $contract->cardNumber) }}"
                            name="cardNumber" id="cardNumber" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="shabaNumber" class="form-label">شماره شباء</label>
                        <input class="form-control" type="text" value="{{ old('shabaNumber', $contract->shabaNumber) }}"
                            name="shabaNumber" id="shabaNumber" />
                    </div>
                    <div class="mb-3 col-md-6">
                        <label for="promissoryNote" class="form-label">سفته</label>
                        <input class="form-control" type="text"
                            value="{{ old('promissoryNote', $contract->promissoryNote) }}" id="promissoryNote"
                            name="promissoryNote" />
                    </div>
                    <input type="hidden" value="{{ $contract->user_id }}" name="user_id">
                </div>

                <button type="submit" class="btn btn-primary">ویرایش</button>
            </form>

        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #span1").persianDatepicker();
        $("#toDate, #span2").persianDatepicker();
    </script>
@endsection
