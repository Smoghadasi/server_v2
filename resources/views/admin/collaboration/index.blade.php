@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            لیست قرارداد ها </a>
        </h5>
        <div class="card-body">
            <a class="btn btn-primary" href="{{ route('collaboration.create', ['contract_id' => $contract->id]) }}"> + افزودن</a>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره قرارداد</th>
                            <th>تاریخ آغاز به کار</th>
                            <th>تاریخ پایان به کار</th>
                            <th>نوع قرارداد</th>
                            <th>بیمه</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @forelse ($collaborations as $collaboration)
                            <tr>
                                <td>{{ ($collaborations->currentPage() - 1) * $collaborations->perPage() + ++$i }}</td>
                                <td>{{ $collaboration->contractNumber }}</td>
                                <td>{{ gregorianDateToPersian($collaboration->fromDate, '-', true) }}</td>
                                <td>{{ gregorianDateToPersian($collaboration->toDate, '-', true) }}</td>
                                <td>
                                    @switch($collaboration->contractType)
                                        @case('full-time')
                                            تمام وقت
                                            @break
                                        @case('freelance')
                                            فریلنسری
                                            @break
                                        @case('part-time')
                                            پاره وقت
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $collaboration->isInsurance ? 'دارد' : 'ندارد' }}</td>

                                <td class="d-flex gap-2">
                                    <a href="{{ route('collaboration.show', $collaboration) }}"
                                        class="btn btn-outline-primary">نمایش</a>
                                    {{-- <form action="{{ route('collaboration.destroy', $collaboration) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger">حذف</button>
                                    </form> --}}
                                </td>

                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">دیتا مورد نظر یافت نشد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                {{ $collaborations }}
            </div>

        </div>
    </div>
@endsection
