@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    هشدار کلاه برداری!
                </div>
            </div>

        </h5>
        <div class="card-body">
            <div class="col-lg-12 m-2 mb-3 text-right">
                {{-- <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-success">تعداد بار های ثبت شده امروز:
                    {{ $loadsToday }}</a> --}}
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان بار</th>
                            <th>شماره موبایل</th>
                            <th>ناوگان</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>تماس</th>
                            <th>تاریخ ثبت</th>
                            {{-- <th>تاریخ حذف</th> --}}
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>

                        @foreach ($loads as $load)
                            <tr>
                                <td>{{ ($loads->currentPage() - 1) * $loads->perPage() + ++$i }}</td>
                                <td>
                                    @php
                                        $pieces = explode(' ', $load->deleted_at);
                                    @endphp
                                    @if ($load->deleted_at != null)
                                        <i class="menu-icon tf-icons bx bx-trash text-danger" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="{{ $load->deleted_at ? gregorianDateToPersian($load->deleted_at, '-', true) . ' ' . $pieces[1] : '-' }}"></i>
                                    @endif

                                    {{ $load->title }}
                                </td>
                                <td>{{ $load->senderMobileNumber }}</td>

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
                                    <a href="{{ route('load.searchLoadDriverCall', $load) }}">{{ $load->driver_calls_count }}</a>
                                </td>
                                <td>{{ $load->date }} {{ $load->dateTime }}</td>
                                <td><a class="btn btn-info btn-sm" href="{{ route('loadInfo', $load->id) }}">جزئیات</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-2 mb-2">
            {{ $loads }}

        </div>
    </div>
@endsection
