@extends('layouts.dashboard')

@section('content')



    <div class="card">
        <h5 class="card-header">احراز هویت صاحبان بار(در حال بررسی)</h5>
        <div class="card-body">
            <div class="col-lg-12 m-2 mb-3 text-right">
                <a href="#" class="alert p-1 alert-secondary">در حال بررسی
                    : {{ $ownerPenddingCounts }}</a>
                <a href="{{ route('owner.reject') }}" class="alert p-1 alert-danger">تایید نشده : {{ $ownerRejectCounts }}</a>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>کد ملی</th>
                            <th>شماره موبایل</th>
                            <th class="text-center">تاریخ ثبت نام</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($owners as $owner)
                            <tr>
                                <td>{{ ($owners->currentPage() - 1) * $owners->perPage() + ++$i }}</td>
                                <td>{{ $owner->name }} {{ $owner->lastName }}
                                    {{ $owner->isOwner == 1 ? '(صاحب بار)' : '(باربری)' }}</td>
                                <td>{{ $owner->nationalCode }}</td>
                                <td>{{ $owner->mobileNumber }}</td>
                                @php
                                    $pieces = explode(' ', $owner->created_at);
                                @endphp
                                <td dir="ltr">
                                    {{ gregorianDateToPersian($owner->created_at, '-', true) . ' ' . $pieces[1] }}
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('ownerAuth.edit', $owner) }}">بررسی
                                        اطلاعات</a>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">فیلد مورد خالی است</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-2">
                    {{ $owners }}
                </div>
            </div>
        </div>

    </div>

@stop
