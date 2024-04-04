@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    مرخصی روزانه
                </div>

            </div>
        </h5>
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>از تاریخ</th>
                            <th>تا تاریخ</th>
                            <th>علت</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($vacations as $vacation)
                            <tr>
                                <td>{{ ($vacations->currentPage() - 1) * $vacations->perPage() + ++$i }}</td>
                                <td>{{ $vacation->user->name }} {{ $vacation->user->lastName }}</td>

                                <td>{{ $vacation->fromDate }}</td>
                                <td>{{ $vacation->toDate }}</td>
                                <td>
                                    {{ $vacation->description ?? '-' }}
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
                    {{ $vacations }}
                </div>
            </div>
        </div>
    </div>
@endsection
