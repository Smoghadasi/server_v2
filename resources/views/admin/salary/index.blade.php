@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            حقوق دریافتی : <a href="{{ route('operators.show', $user) }}">{{ $user->name }} {{ $user->lastName }}</a>
        </h5>
        <div class="card-body">
            <a class="btn btn-primary" href="{{ route('salary.create', ['user_id' => $user->id]) }}"> + افزودن</a>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>تاریخ</th>
                            <th>مبلغ</th>
                            <th>حقوق پایه</th>
                            <th>اضافه کار / عیدی</th>
                            <th>توضیحات</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @forelse ($salaries as $salary)
                            <tr>
                                <td>{{ ($salaries->currentPage() - 1) * $salaries->perPage() + ++$i }}</td>
                                <td>{{ gregorianDateToPersian($salary->date, '-', true) }}</td>
                                <td>{{ number_format($salary->price) }}</td>
                                <td>{{ number_format($salary->salary) }}</td>
                                <td>{{ number_format($salary->salary_increase) }}</td>
                                <td>{{ $salary->description }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('salary.edit', $salary) }}" class="btn btn-outline-primary">ویرایش</a>
                                    <form action="{{ route('salary.destroy', $salary) }}" method="POST">
                                        @csrf
                                        @method('delete')
                                        <button type="submit" class="btn btn-outline-danger">حذف</button>
                                    </form>
                                </td>

                            </tr>
                        @empty
                            <tr class="text-center">
                                <td colspan="10">دیتا مورد نظر یافت نشد</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>
@endsection
