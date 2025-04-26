@extends('layouts.dashboard')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-center mb-4">
            <h2 class="fw-bold">مشاهده قرارداد</h2>
        </div>

        <div class="card shadow-sm border-0 rounded">
            <div class="card-header">
                <h5 class="mb-0">شماره قرارداد: {{ $collaboration->contractNumber }}</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <p><strong>تاریخ آغاز به کار:</strong>
                            {{ implode('-', array_reverse(explode('-', gregorianDateToPersian($collaboration->fromDate, '-', true)))) }}
                        </p>                    </div>
                    <div class="col-md-6">
                        <p><strong>تاریخ پایان به کار:</strong>
                            {{ implode('-', array_reverse(explode('-', gregorianDateToPersian($collaboration->toDate, '-', true)))) }}
                        </p>
                                            </div>
                    <div class="col-md-6">
                        <p><strong>نوع قرارداد:</strong>
                            @switch($collaboration->contractType)
                                @case('full-time')
                                    <span class="badge bg-success">تمام وقت</span>
                                    @break
                                @case('freelance')
                                    <span class="badge bg-warning text-dark">فریلنسری</span>
                                    @break
                                @case('part-time')
                                    <span class="badge bg-info text-dark">پاره وقت</span>
                                    @break
                            @endswitch
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>بیمه:</strong>
                            <span class="badge {{ $collaboration->isInsurance ? 'bg-success' : 'bg-danger' }}">
                                {{ $collaboration->isInsurance ? 'دارد' : 'ندارد' }}
                            </span>
                        </p>
                    </div>
                    <div class="col-md-12 text-center mt-3">
                        @if($collaboration->contract_file)
                            <img src="{{ asset($collaboration->contract_file) }}" width="200" class="img-fluid rounded shadow-sm" alt="تصویر قرارداد">
                        @else
                            <p class="text-muted">تصویری برای این قرارداد موجود نیست.</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-footer bg-light d-flex justify-content-end gap-3">
                <a href="{{ route('collaboration.edit', $collaboration) }}" class="btn btn-outline-primary">ویرایش</a>
                <form action="{{ route('collaboration.destroy', $collaboration) }}" method="POST">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-outline-danger">حذف</button>
                </form>
            </div>
        </div>
    </div>
@endsection
