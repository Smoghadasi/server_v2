@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">لیست صاحبان بار</h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>نوع</th>
                            <th>کد ملی</th>
                            <th>شماره موبایل</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($owners as $owner)
                            <tr>
                                <td>{{ ($owners->currentPage() - 1) * $owners->perPage() + ++$i }}</td>
                                <td>{{ $owner->name }} {{ $owner->lastName }}</td>
                                <td>
                                    @switch($owner->isOwner)
                                        @case(1)
                                            صاحب بار
                                        @break

                                        @case(2)
                                            باربری
                                        @break

                                        @default
                                            تعیین نشده
                                    @endswitch
                                </td>
                                <td>{{ $owner->nationalCode }}</td>
                                <td>{{ $owner->mobileNumber }}</td>
                                <td>
                                    @if ($owner->isAuth == 1)
                                        <span class="badge bg-success">احراز هویت شده</span>
                                    @else
                                        <span class="badge bg-danger">احراز هویت نشده</span>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-sm btn-primary" href="{{ route('owner.show', $owner) }}">مشاهده</a>
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
