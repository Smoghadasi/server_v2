@extends('layouts.dashboard')

@section('content')
<div class="card shadow-lg border-0">
    <div class="card-header">
        <h4><i class="fas fa-file-contract"></i> مشخصات قرارداد</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6 mb-3">
                <strong><i class="menu-icon tf-icons bx bx-calendar"></i> تاریخ آغاز به کار:</strong>
                <span class="badge bg-info text-dark px-3 py-2">{{ gregorianDateToPersian($contract->fromDate, '-', true) }}</span>
            </div>
            <div class="col-md-6 mb-3">
                <strong><i class="menu-icon tf-icons bx bx-calendar-check"></i> تاریخ پایان به کار:</strong>
                <span class="badge bg-warning text-dark px-3 py-2">{{ gregorianDateToPersian($contract->toDate, '-', true) }}</span>
            </div>
            <div class="col-md-6 mb-3">
                <strong><i class="fas fa-credit-card text-secondary"></i> شماره کارت:</strong>
                <div class="form-control bg-light border-0 shadow-sm">{{ $contract->cardNumber }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong><i class="fas fa-university text-success"></i> شماره شباء:</strong>
                <div class="form-control bg-light border-0 shadow-sm">{{ $contract->shabaNumber }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <strong><i class="fas fa-file-invoice-dollar text-danger"></i> سفته:</strong>
                <div class="form-control bg-light border-0 shadow-sm">{{ $contract->promissoryNote }}</div>
            </div>
        </div>
    </div>
    <div class="card-footer text-center">
        <a href="{{ route('contract.edit', $contract->id) }}" class="btn btn-outline-success"><i class="fas fa-edit"></i> ویرایش قرارداد</a>
        <a href="{{ route('contract.index', ['user_id' => $contract->user_id]) }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left"></i> بازگشت</a>
    </div>
</div>
@endsection
