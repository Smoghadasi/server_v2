@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            علامت گذاری شده ها (Bookmark)
        </h5>
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>کاربر</th>
                        <th>نوع</th>
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php $i = 1; ?>
                    @forelse ($bookmarks as $key => $bookmark)
                        <tr class="">
                            <td>{{ ($bookmarks->currentPage() - 1) * $bookmarks->perPage() + ($key + 1) }}</td>
                            <td>
                                @php
                                    $route =
                                        $bookmark->type == 'owner'
                                            ? route('owner.show', $bookmark->userable_id)
                                            : route('driver.detail', $bookmark->userable_id);
                                    $name = $bookmark->userable->name ?? '-';
                                    $lastName = $bookmark->userable->lastName ?? '-';
                                    $mobileNumber = $bookmark->userable->mobileNumber ?? '-';
                                @endphp

                                <a href="{{ $route }}">
                                    {{ $name }} {{ $lastName }} ({{ $mobileNumber }})
                                </a>

                            </td>

                            <td>
                                {{ $bookmark->type == 'owner' ? 'صاحب بار' : 'راننده' }}
                            </td>
                            @php
                                $pieces = explode(' ', $bookmark->created_at);
                            @endphp
                            <td>
                                {{ gregorianDateToPersian($bookmark->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                            </td>


                            <td>
                                <form action="{{ route('bookmark.destroy', $bookmark) }}" method="post">
                                    @csrf
                                    @method('delete')
                                    <button class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr class="text-center">
                            <td colspan="10">
                                دیتا مورد نظر یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            @if (Auth::user()->role_id == 'admin')
                <div class="mt-3">
                    {{ $supports }}
                </div>
            @endif

        </div>
    </div>

@stop
