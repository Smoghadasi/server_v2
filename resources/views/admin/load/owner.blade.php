@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    لیست بار صاحبان بار ({{ $loadsCount }})
                </div>
                <div class="col-4" style="text-align: left">
                    @if (Auth::user()->role == 'admin')
                        @if (isSendBotLoadOwner())
                            ارسال بار ربات به بار های ثبت شده
                            <a class="btn btn-danger btn-sm" href="{{ url('admin/changeSiteOption/sendBotLoadOwner') }}">
                                غیر فعال
                            </a>
                        @else
                            ارسال بار ربات به بار های ثبت شده
                            <a class="btn btn-primary btn-sm" href="{{ url('admin/changeSiteOption/sendBotLoadOwner') }}">
                                فعال
                            </a>
                        @endif
                    @endif
                </div>
            </div>

        </h5>
        <div class="card-body">
            <div class="col-lg-12 m-2 mb-3 text-right">
                <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-success">تعداد بار های ثبت شده امروز:
                    {{ $loadsToday }}</a>
            </div>
            <form method="get" action="{{ route('admin.load.owner') }}">
                <div class="form-group row">
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="loadBy">
                            <option value="0">همه</option>
                            <option value="1">فعال</option>
                            <option value="2">بایگانی</option>
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="fleet_id">
                            <option disabled selected>انتخاب ناوگان</option>
                            @foreach ($fleets as $fleet)
                                <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان بار</th>
                            <th>شماره موبایل</th>
                            <th>صاحب بار</th>
                            <th>ناوگان</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>تعداد</th>
                            <th>تاریخ ثبت</th>
                            {{-- <th>تاریخ حذف</th> --}}
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($loads as $load)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>
                                    @php
                                        $pieces = explode(' ', $load->deleted_at);
                                    @endphp
                                    @if ($load->deleted_at != null)
                                        <i class="menu-icon tf-icons bx bx-trash text-danger" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="{{ $load->deleted_at ? gregorianDateToPersian($load->deleted_at, '-', true) . ' ' . $pieces[1] : '-' }}"></i>
                                    @endif
                                    @if ($load->isBot != null)
                                        <i class="menu-icon tf-icons bx bx-check text-success"></i>
                                    @endif

                                    {{ $load->title }}
                                </td>
                                <td>{{ $load->senderMobileNumber }}</td>
                                <td class="{{ $load->owner->isAccepted == 1 ? 'text-success' : '' }}">
                                    <a class="{{ $load->owner->isAccepted == 1 ? 'text-success' : '' }}"
                                        href="{{ route('owner.show', $load->owner->id) }}">
                                        {{ $load->owner->name }} {{ $load->owner->lastName }}
                                    </a>
                                </td>
                                <td>
                                    @php
                                        $fleets = json_decode($load->fleets, true);
                                    @endphp
                                    @foreach ($fleets as $fleet)
                                        <span class="alert alert-primary p-1 m-1 small"
                                            style="line-height: 2rem">{{ $fleet['title'] }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $load->fromCity }}</td>
                                <td>{{ $load->toCity }}</td>
                                <td>
                                    <span class="badge bg-primary">بازدید : {{ $load->driverVisitCount }}</span>
                                    <span>
                                        <a class="badge bg-danger" href="{{ route('load.searchLoadInquiry', $load->id) }}">
                                            درخواست: {{ $load->numOfInquiryDrivers }}
                                        </a>

                                    </span>
                                    <span>
                                        <a class="badge bg-success"
                                            href="{{ route('load.searchLoadDriverCall', $load->id) }}">
                                            تماس: {{ $load->numOfDriverCalls }}
                                        </a>
                                    </span>
                                    <span>
                                        <a class="badge bg-black" href="{{ route('admin.nearLoadDrivers', $load->id) }}">
                                            رانندگان نزدیک: {{ $load->numOfNearDriver }}
                                        </a>
                                    </span>
                                </td>

                                <td>{{ $load->date }} {{ $load->dateTime }}</td>
                                {{-- <td dir="ltr" class="text-center">
                                    {{ $load->deleted_at ? gregorianDateToPersian($load->deleted_at, '-', true) . ' ' . $pieces[1] : '-' }}
                                    {{ gregorianDateToPersian($load->deleted_at, '-', true) . ' ' . $pieces[1] }}
                                </td> --}}
                                <td>
                                    <a class="btn btn-info btn-sm" href="{{ route('loadInfo', $load->id) }}">جزئیات</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2 mb-2">
            @if ($loads->hasPages())
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        {{-- Previous Page Link --}}
                        @if ($loads->onFirstPage())
                            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
                        @else
                            <li class="page-item"><a class="page-link"
                                    href="{{ $loads->previousPageUrl() }}&fleet_id={{ request('fleet_id') }}&loadBy={{ request('loadBy') }}"
                                    rel="prev">&laquo;</a></li>
                        @endif

                        {{-- Pagination Elements --}}
                        @foreach ($loads->getUrlRange(1, $loads->lastPage()) as $page => $url)
                            @if (
                                $page == 1 ||
                                    $page == $loads->lastPage() ||
                                    ($page >= $loads->currentPage() - 2 && $page <= $loads->currentPage() + 2))
                                @if ($page == $loads->currentPage())
                                    <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link"
                                            href="{{ $url }}&fleet_id={{ request('fleet_id') }}&loadBy={{ request('loadBy') }}">{{ $page }}</a>
                                    </li>
                                @endif
                            @elseif ($page == $loads->currentPage() - 3 || $page == $loads->currentPage() + 3)
                                <li class="page-item disabled"><span class="page-link">...</span></li>
                            @endif
                        @endforeach

                        {{-- Next Page Link --}}
                        @if ($loads->hasMorePages())
                            <li class="page-item"><a class="page-link"
                                    href="{{ $loads->nextPageUrl() }}&fleet_id={{ request('fleet_id') }}&loadBy={{ request('loadBy') }}"
                                    rel="next">&raquo;</a></li>
                        @else
                            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
                        @endif
                    </ul>
                </nav>
            @endif

        </div>
    </div>

@endsection
