<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>نام و نام خانوادگی</th>
                <th>شماره همراه</th>
                <th>ناوگان</th>
                <th>تاریخ واریزی</th>
                <th>تاریخ تماس</th>
                <th>پاسخ ادمین</th>
            </tr>
        </thead>
        <tbody class="small text-right">
            @forelse ($transactionManuals->where('type', $type) as $transactionManual)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        <a href="{{ route('transaction-manual.show', $transactionManual->driver_id) }}">
                            {{ $transactionManual->driver->name }}
                            {{ $transactionManual->driver->lastName }}
                            {{ '(' . $transactionManual->total . ')' }}
                        </a>
                    </td>
                    <td>{{ $transactionManual->driver->mobileNumber }}</td>
                    <td>{{ \App\Http\Controllers\FleetController::getFleetName($transactionManual->driver->fleet_id) }}</td>
                    <td>{{ $transactionManual->lastPaymentDate }}</td>
                    <td>{{ $transactionManual->firstPaymentDate }}</td>
                    <td>{{ $transactionManual->lastAdminMessage ?? '-' }}</td>
                </tr>
            @empty
                <tr class="text-center">
                    <td colspan="10">دیتا مورد نظر یافت نشد</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
