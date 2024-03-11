@extends('layouts.dashboard')
@section('title', 'گزارش اشتراک رایگان')
@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    گزارش اشتراک رایگان
                </div>
                <div class="col" style="text-align: left;">
                    <a href="#" class="alert p-1 alert-primary">مجموع تماس های رایگان امروز: {{ $freeCallCount }}</a>
                </div>
            </div>
        </h5>
        <div class="card-body">
            <form method="post" action="{{ route('search.free.subscription') }}">
                @csrf
                <div class="form-group row">
                    <div class="col-md-4 my-3">
                        <input class="form-control" name="mobileNumber" id="mobileNumber" placeholder="شماره موبایل">
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>نام و نام خانوادگی</th>
                            {{-- <th>مجموع تماس های رایگان</th> --}}
                            <th>نوع</th>
                            <th>اپراتور</th>
                            <th>تعداد</th>
                            <th>تاریخ</th>

                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($freeSubscriptions as $freeSubscription)
                            <tr>
                                <td>{{ $loop->iteration }}</td>

                                <td>{{ $freeSubscription->driver->name . ' ' . $freeSubscription->driver->lastName }}
                                    ({{ $freeSubscription->driver->mobileNumber }})
                                </td>
                                {{-- <td>{{ $freeSubscription->driver->freeCallTotal }}</td> --}}
                                <td>
                                    @switch($freeSubscription->type)
                                        @case(AUTH_CALLS)
                                            <span class="badge bg-label-success"> تماس رایگان</span>
                                        @break

                                        @case(AUTH_VALIDITY)
                                            <span class="badge bg-label-warning"> اعتبار رایگان</span>
                                        @break

                                        @case(AUTH_CARGO)
                                            <span class="badge bg-label-primary"> بار رایگان</span>
                                        @break
                                    @endswitch
                                </td>
                                <td>{{ $freeSubscription->value }}</td>
                                <td>
                                    @if ($freeSubscription->operator_id != null)
                                        {{ $freeSubscription->operator->name }} {{ $freeSubscription->operator->lastName }}
                                    @else
                                        -
                                    @endif
                                </td>
                                @php
                                    $pieces = explode(' ', $freeSubscription->created_at);
                                @endphp
                                <td>{{ gregorianDateToPersian($freeSubscription->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                                </td>

                            </tr>
                            @empty
                                <tr class="text-center">
                                    <td colspan="10">فیلد مورد خالی است</td>
                                </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
