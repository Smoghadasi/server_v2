@extends('layouts.dashboard')

@section('content')
<div class="card">
    <h5 class="card-header">رانندگان</h5>
    <div class="card-body">

        <div class="col-lg-12 alert alert-info">
            تعداد یافته ها : {{ $drivers->count() }} راننده
        </div>

        <div class="nav-align-top mb-4">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#all">کل</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#one">یک ماهه</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#three">سه ماهه</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#six">شش ماهه</button></li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="all">
                    @include('admin.driver.component._drivers_table', ['drivers' => $drivers])
                </div>
                <div class="tab-pane fade" id="one">
                    @include('admin.driver.component._drivers_table', ['drivers' => $oneMonthDrivers])
                </div>
                <div class="tab-pane fade" id="three">
                    @include('admin.driver.component._drivers_table', ['drivers' => $threeMonthDrivers])
                </div>
                <div class="tab-pane fade" id="six">
                    @include('admin.driver.component._drivers_table', ['drivers' => $sixMonthDrivers])
                </div>
            </div>
        </div>

    </div>
</div>
@stop
