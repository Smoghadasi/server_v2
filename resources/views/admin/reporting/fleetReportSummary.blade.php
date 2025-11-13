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
                                <td>کل : {{ $fleet->activityAll_total }}
                                    <br />
                                    اشتراک دارند : {{ $fleet->activity_active }}
                                    <br />
                                    اشتراک ندارند : {{ $fleet->activityAll_total - $fleet->activity_active }}
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
                        <td>کل : {{ $fleets->sum('activityAll_total') }}
                            <br />
                            <span data-bs-toggle="tooltip" data-bs-html="true"
                                title="
                                    آنلاین: <b>{{ $transactionCount['online'] }}</b><br>
                                    کارت به کارت: <b>{{ $transactionCount['cardToCard'] }}</b><br>
                                    گیفت: <b>{{ $transactionCount['gift'] }}</b>
                                ">
                                اشتراک دارند : {{ $fleets->sum('activity_active') }}
                            </span>
                            <br />
                            اشتراک ندارند : {{ $fleets->sum('activityAll_total') - $fleets->sum('activity_active') }}
                        </td>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
