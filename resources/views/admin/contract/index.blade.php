@extends('layouts.dashboard')

@section('content')
    <div class="card shadow-lg border-0">
        <h5 class="card-header">
            <i class="fas fa-file-contract"></i> قراردادها - <a href="{{ route('operators.show', $user) }}" class="text-dark">{{ $user->name }} {{ $user->lastName }}</a>
        </h5>
        <div class="card-body">
            <a class="btn btn-outline-primary btn-lg mb-3" href="{{ route('contract.create', ['user_id' => $user->id]) }}">
                <i class="fas fa-plus"></i> افزودن قرارداد
            </a>
            <div class="table-responsive">
                <table class="table table-hover table-striped">
                    <thead class="text-white">
                        <tr>
                            <th>#</th>
                            <th><i class="fas fa-calendar-alt"></i> تاریخ آغاز به کار</th>
                            <th><i class="fas fa-calendar-check"></i> تاریخ پایان به کار</th>
                            <th><i class="fas fa-folder"></i> قراردادها</th>
                            <th><i class="fas fa-cogs"></i> عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @forelse ($contracts as $contract)
                            <tr>
                                <td class="fw-bold">{{ ($contracts->currentPage() - 1) * $contracts->perPage() + ++$i }}</td>
                                <td>{{ gregorianDateToPersian($contract->fromDate, '-', true) }}</td>
                                <td>{{ gregorianDateToPersian($contract->toDate, '-', true) }}</td>
                                <td>
                                    <a href="{{ route('collaboration.index', ['contract_id' => $contract->id]) }}" class="btn btn-outline-info">
                                        <i class="fas fa-file-alt"></i> {{ $contract->contract_collaborations_count }} فایل
                                    </a>
                                </td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('contract.show', $contract) }}" class="btn btn-outline-success">
                                        <i class="fas fa-eye"></i> نمایش
                                    </a>
                                    <form action="{{ route('contract.destroy', $contract) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="5" class="text-danger fw-bold">دیتا مورد نظر یافت نشد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3 d-flex justify-content-center">
                    {{ $contracts }}
                </div>
            </div>
        </div>
    </div>
@endsection
