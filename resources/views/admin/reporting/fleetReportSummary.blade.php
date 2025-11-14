@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            خلاصه گزارش رانندگان بر اساس ناوگان
        </h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered text-center">
                    <thead class="table-light">
                        <tr>
                            <th>نوع ناوگان</th>
                            <th>ناوگان</th>
                            <th>تعداد ثبت نامی ها
                                کل / روز گذشته
                            </th>
                            <th>تعداد فعال 30 روز گذشته</th>
                            <th>تعداد فعالیت روز گذشته</th>
                            <th>رکورد فعالیت با روز گذشته</th>
                            {{-- <th>بر اساس تماس</th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($fleets as $fleet)
                            <tr>
                                <td>{{ $fleet->title }}</td>
                                <td>{{ $fleet->parent->title }}</td>
                                <td>
                                    کل :
                                    {{ $fleet->drivers_count }}
                                    <br />
                                    روز گذشته:
                                    {{ $fleet->new_drivers_yesterday }}
                                </td>
                                <td>
                                    کل :
                                    <a href="{{ route('fleet.drivers.list', ['fleet' => $fleet->id, 'type' => 'all']) }}">
                                        {{ $fleet->activityAll_total }}
                                    </a>
                                    <br />

                                    اشتراک دارند :
                                    <a href="#">
                                        {{ $fleet->activity_active }}
                                    </a>
                                    <br />

                                    اشتراک ندارند :
                                    <a href="#">
                                        {{ $fleet->activity_notActive }}
                                    </a>
                                </td>

                                <td>{{ $fleet->activity_yesterday }}</td>
                                <td @class([
                                    'text-danger' => $fleet->activity_growth_percent < 0,
                                    'text-success' => $fleet->activity_growth_percent > 0,
                                ])>{{ $fleet->activity_growth_percent }} %</td>
                                {{-- <td>کل : {{ $fleet->call_total }}
                                    <br />
                                    اشتراک دارند : {{ $fleet->call_active }}
                                    <br />
                                    اشتراک ندارند : {{ $fleet->call_notActive }}
                                </td> --}}
                            </tr>
                        @endforeach
                        <tr>
                            <td colspan="2" class="fw-bold">مجموع</td>
                            <td>کل:
                                {{ $fleets->sum('drivers_count') }}
                                <br />
                                روز گذشته:
                                {{ $fleets->sum('new_drivers_yesterday') }}
                            </td>
                            <td>کل : {{ $fleets->sum('activityAll_total') }}
                                <br />
                                اشتراک دارند : {{ $fleets->sum('activity_active') }}
                                <br />
                                اشتراک ندارند : {{ $fleets->sum('activityAll_total') - $fleets->sum('activity_active') }}
                            </td>

                            <td>{{ $fleets->sum('activity_yesterday') }}</td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
