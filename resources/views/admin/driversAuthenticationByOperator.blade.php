@extends('layouts.dashboard')

@section('content')



    <div class="card">
        <h5 class="card-header">احراز هویت رانندگان ({{ $driverCount }})</h5>
        <div class="table-responsive">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام و نام خانوادگی</th>
                    <th>وضعیت احراز هویت</th>
                    <th>کد ملی</th>
                    <th>نوع ناوگان</th>
                    <th>شماره تلفن همراه</th>
                    <th></th>
                </tr>
                </thead>
                <tbody class="small">
                <?php $i = 0;?>
                @forelse($drivers as $driver)
                    <tr>
                        <td>{{ (($drivers->currentPage()-1) * $drivers->perPage()) + (++$i) }}</td>
                        <td>
                            {{ $driver->name }} {{ $driver->lastName }}

                            @if($driver->status==0)
                                <span class="alert alert-danger p-1">غیرفعال</span>
                            @else
                                <span class="alert alert-success p-1">فعال</span>
                            @endif
                        </td>
                        <td>
                            @if ($driver->authLevel == 1)
                                <span class="badge bg-label-secondary"><span class="badge bg-label-secondary">سطح نقره ای : </span> در حال بررسی</span>
                            @elseif ($driver->authLevel == 3)
                                <span class="badge bg-label-warning"><span class="badge bg-label-warning">سطح طلایی ای: </span> در حال بررسی</span>
                            @endif
                        </td>
                        <td>{{ $driver->nationalCode }}</td>
                        <td>{{ \App\Http\Controllers\FleetController::getFleetName($driver->fleet_id) }}</td>
                        <td>{{ $driver->mobileNumber }}</td>
                        <td>
                            <a class="btn btn-sm btn-primary"
                               href="{{ url('admin/editDriver') }}/{{ $driver->id }}">بررسی اطلاعات راننده</a>
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
                @if(!isset($showSearchResult) || !$showSearchResult)
                    {{ $drivers }}
                @endif
            </div>
        </div>
    </div>

@stop

