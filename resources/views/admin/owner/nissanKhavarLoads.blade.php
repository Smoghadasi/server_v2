@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">لیست صاحبان بار: نیسان و خاور</h5>
        <div class="card-body">
            {{-- @if (auth()->user()->role == 'admin') --}}
            {{-- <div class="my-3">
                <div class="row justify-content-between">
                    <div class="col">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                وضعیت صاحبان بار
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('ownerAuth.index') }}">در حال بررسی: {{ $ownerPenddingCounts }}</a></li>
                                <li><a class="dropdown-item text-danger" href="{{ route('owner.reject') }}">تایید نشده: {{ $ownerRejectCounts }}</a></li>
                                <li><a class="dropdown-item text-success" href="{{ route('owner.accept') }}">تایید شده: {{ $ownerAcceptCounts }}</a></li>
                                <li><a class="dropdown-item text-dark" href="{{ route('owner.ownerRejected') }}">رد شده: {{ $ownerRejectedCounts }}</a></li>
                                <li><a class="dropdown-item text-primary" href="{{ route('bookmark.index', ['type' => 'owner']) }}">علامت گذاری شده ها: {{ $ownerBookmarkCount }}</a></li>
                                <li><a class="dropdown-item text-primary" href="{{ route('owner.index', ['isLimitLoad' => 1]) }}">صاحبان بار محدود شده برای بار: {{ $ownerLimitLoadCount }}</a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="col" style="text-align: left;">
                        <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-primary">تعداد بار های ثبت شده
                            امروز
                            (کل): {{ $loadsToday }}</a>
                        <a href="{{ route('admin.load.owner') }}" class="alert p-1 alert-dark">تعداد بار های ثبت شده
                            امروز
                            (صاحب بار): {{ $loadsTodayOwner }}</a>
                    </div>
                </div>
            </div> --}}
            <form method="get" action="{{ route('ownersNissan') }}">
                <div class="form-group row">
                    <div class="col-md-3 mt-3">
                        <select class="form-control form-select" name="filter_sort">
                            <option value="most" {{ request('filter_sort') == 'most' ? 'selected' : '' }}>بیشترین بار</option>
                            <option value="least" {{ request('filter_sort') == 'least' ? 'selected' : '' }}>کمترین بار</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <button type="submit" class="btn btn-primary">اعمال فیلتر</button>
                    </div>
                </div>
            </form>

            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            <th>احراز هویت</th>
                            <th>نوع</th>
                            <th>کد ملی</th>
                            <th>شماره موبایل</th>
                            <th>بار ها</th>
                            <th>ورژن</th>
                            <th class="text-center">تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        <?php $i = 0; ?>
                        @forelse($owners as $owner)
                            <tr>
                                <td>{{ ($owners->currentPage() - 1) * $owners->perPage() + ++$i }}</td>
                                <td>
                                    @if ($owner->bookmark)
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $owner->id }}" name="user_id">
                                            <input type="hidden" value="owner" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bxs-bookmark-star'></i>
                                            </button>
                                        </form>
                                    @else
                                        <form style="display: contents" action="{{ route('bookmark.store') }}"
                                            method="post">
                                            @csrf
                                            <input type="hidden" value="{{ $owner->id }}" name="user_id">
                                            <input type="hidden" value="owner" name="type">
                                            <button class="btn btn-link" type="submit">
                                                <i class='bx bx-bookmark'></i>
                                            </button>
                                        </form>
                                    @endif
                                    {{ $owner->name }} {{ $owner->lastName }}
                                    @if ($owner->status == 1)
                                        <span class="badge bg-success">فعال</span>
                                    @else
                                        <span class="badge bg-danger">غیر فعال</span>
                                    @endif
                                    @if ($owner->moreDayLoad >= 3)
                                        <span class="badge bg-primary">3+</span>
                                    @endif
                                    @if ($owner->isAccepted == 1)
                                        <i class="menu-icon tf-icons bx bx-check-shield text-success"></i>
                                    @endif
                                </td>
                                <td>
                                    @switch($owner->isAuth)
                                        @case(0)
                                            <span class="badge bg-danger">انجام نشده</span>
                                        @break

                                        @case(1)
                                            <span class="badge bg-success">انجام شده</span>
                                        @break

                                        @case(2)
                                            <span class="badge bg-secondary">در حال بررسی</span>
                                        @break
                                    @endswitch
                                </td>
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
                                    <a href="{{ route('owner.loads', $owner->id) }}">{{ $owner->numOfLoads }}</a>
                                </td>
                                <td>
                                    {{ $owner->version ?? 1 }}
                                </td>
                                @php
                                    $pieces = explode(' ', $owner->created_at);
                                @endphp
                                <td dir="ltr">
                                    {{ gregorianDateToPersian($owner->created_at, '-', true) . ' ' . $pieces[1] }}
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
                        @if ($owners->hasPages())
                            <nav aria-label="Page navigation">
                                <ul class="pagination">
                                    {{-- Previous Page Link --}}
                                    @if ($owners->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                                    @else
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $owners->previousPageUrl() }}&fleet_id={{ request('fleet_id') }}&isAccepted={{ request('isAccepted') }}&isLimitLoad={{ request('isLimitLoad') }}"
                                                rel="prev">&laquo;</a></li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($owners->getUrlRange(1, $owners->lastPage()) as $page => $url)
                                        @if (
                                            $page == 1 ||
                                                $page == $owners->lastPage() ||
                                                ($page >= $owners->currentPage() - 2 && $page <= $owners->currentPage() + 2))
                                            @if ($page == $owners->currentPage())
                                                <li class="page-item active"><span class="page-link">{{ $page }}</span>
                                                </li>
                                            @else
                                                <li class="page-item"><a class="page-link"
                                                        href="{{ $url }}&fleet_id={{ request('fleet_id') }}&isAccepted={{ request('isAccepted') }}&isLimitLoad={{ request('isLimitLoad') }}">{{ $page }}</a>
                                                </li>
                                            @endif
                                        @elseif ($page == $owners->currentPage() - 3 || $page == $owners->currentPage() + 3)
                                            <li class="page-item disabled"><span class="page-link">...</span></li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($owners->hasMorePages())
                                        <li class="page-item"><a class="page-link"
                                                href="{{ $owners->nextPageUrl() }}&fleet_id={{ request('fleet_id') }}&isAccepted={{ request('isAccepted') }}&isLimitLoad={{ request('isLimitLoad') }}"
                                                rel="next">&raquo;</a></li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                                    @endif
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>


    @endsection
