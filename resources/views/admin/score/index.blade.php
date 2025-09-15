@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            امتیاز نظرات </a>
        </h5>
        <div class="card-body">
            <form method="get" action="{{ route('score.index') }}">
                <div class="row mb-2">
                    <div class="col-md-4">
                        <div class="form-group">
                            <select class="form-control form-select" name="type" id="">
                                <option value="">همه</option>
                                <option @if(request('type') == 'Owner') selected @endif value="Owner">صاحب بار</option>
                                <option @if(request('type') == 'Driver') selected @endif value="Driver">رانندگان</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-primary">جستجو</button>
                    </div>
                </div>

            </form>
            <div class="table-responsive">
                <table class="table">
                    <tr>
                        <th>#</th>
                        <th>اپلیکیشن</th>
                        <th>راننده</th>
                        <th>صاحب بار</th>
                        <th>امتیاز</th>
                        <th>توضیحات</th>
                        <th>تاریخ</th>
                    </tr>
                    <?php $i = 0; ?>

                    @foreach ($scores as $score)
                        <tr>
                            <td>{{ ($scores->currentPage() - 1) * $scores->perPage() + ++$i }}</td>
                            <td>{{ $score->type === 'Owner' ? 'صاحبان بار' : 'رانندگان' }}</td>
                            <td>
                                <a href="{{ route('driver.detail', $score->driver_id) }}">
                                    {{ $score->driver ? $score->driver->name . ' ' . $score->driver->lastName : '-' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('owner.show', $score->owner_id) }}">
                                    {{ $score->owner ? $score->owner->name . ' ' . $score->owner->lastName : '-' }}
                                </a>
                            </td>

                            <td>{{ $score->value }}</td>
                            <td>{{ $score->description ?? '-' }}</td>

                            @php
                                $pieces = explode(' ', $score->created_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($score->created_at, '-', true) . ' | ' . $pieces[1] }}</td>
                        </tr>
                    @endforeach
                </table>
                <div class="mt-2">
                    {{ $scores->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
