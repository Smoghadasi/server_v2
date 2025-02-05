@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    صورت مغایرت بانکی
                </div>
                <div class="col text-end">
                    <a href="{{ route('discrepancy.create') }}" class="btn btn-primary">جدید</a>
                </div>
        </h5>
        <div class="card-body">
            <form method="GET" action="{{ route('discrepancy.index') }}" class="form-group">
                <div class="col-md-12 row">
                    <div class="col-md-3">
                        <input class="form-control" type="text" id="fromDate" name="fromDate"
                            placeholder="از تاریخ" autocomplete="off" />
                        {{-- <span id="span1"></span> --}}
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" type="text" name="toDate" id="fromDate"
                            placeholder="تا تاریخ"  autocomplete="off"/>
                        {{-- <span id="span2"></span> --}}
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary m-2">جستجو</button>

                    </div>
                </div>
            </div>
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>واریزی صورت گرفته</th>
                        <th>واریزی سامانه</th>
                        <th>مجموع</th>
                        <th>تاریخ ثبت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @forelse ($discrepancies as $discrepancy)
                        <tr>
                            <td>{{ ($discrepancies->currentPage() - 1) * $discrepancies->perPage() + ++$i }}</td>
                            <td>{{ $discrepancy->total_card }}</td>
                            <td>{{ $discrepancy->total_site }}</td>
                            <td>{{ $discrepancy->total_all }}</td>
                            @php
                                $pieces = explode(' ', $discrepancy->created_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($discrepancy->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">هیچ دیتایی وجود ندارد</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-2 mb-2">
            {{ $discrepancies }}
        </div>
    </div>

@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
       $("#fromDate, #span1").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
       $("#toDate").persianDatepicker();

    </script>
@endsection
