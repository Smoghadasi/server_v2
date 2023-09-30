@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            گزارش اشتراک رایگان
        </h5>
        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>نام و نام خانوادگی</th>
                        <th>موبایل</th>
                        <th>نوع</th>
                        <th>تعداد</th>

                    </tr>
                    </thead>

                    <tbody>
                    @foreach ($freeSubscriptions as $freeSubscription)
                        <tr>
                            <td>{{ $loop->iteration }}</td>

                            <td>{{ $freeSubscription->driver->name . " " . $freeSubscription->driver->lastName }}</td>
                            <td>{{ $freeSubscription->driver->mobileNumber }}</td>
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

                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $freeSubscriptions }}
            </div>
        </div>
    </div>
@endsection
