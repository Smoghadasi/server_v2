@extends('layouts.dashboard')

@section('content')

    <!-- Breadcrumbs-->
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            {{-- <a href="#">داشبورد</a> --}}
            داشبورد
        </li>
        {{-- <li class="breadcrumb-item active">Overview</li> --}}
    </ol>

    @if (in_array('onlineUsers', auth()->user()->userAccess))
        <div class="card card-body  mb-3">
            <div class="col-lg-12">
                <div class="col-lg-12">کاربران :</div>
                @foreach ($users as $user)
                    <span class="table-bordered border-info rounded bg-white p-1 m-1">
                        {{ $user->name }} {{ $user->lastName }}
                        @if (Cache::has('user-is-online-' . $user->id))
                            @if (Cache::has('user-is-active-' . $user->id))
                                <span class="text-primary">فعال</span>
                            @else
                                <span class="text-success">آنلاین</span>
                            @endif
                        @else
                            <span class="text-secondary">آفلاین</span>
                        @endif

                    </span>
                @endforeach
                @if (auth()->user()->id == 21 || auth()->user()->id == 40)
                    <div class="mt-3">
                        <a class="btn btn-danger btn-sm" href="{{ route('driver.zeroData') }}">
                            <i class="fas fa-angle-right"></i>
                            صفر کردن بار ها
                        </a>
                    </div>
                @endif

            </div>
        </div>
    @endif


    <!-- Icon Cards-->
    <div class="row">
        @if (in_array('dashboardAllCargo', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">

                <div class="card text-white bg-primary o-hidden h-100">

                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-truck-loading"></i>
                        </div>
                        <h1 class="text-center  text-white">{{ $countOfLoads }} </h1>
                        <h2 class="text-center text-white">کل بارها</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ url('admin/loads') }}">
                        <span class="float-left">لیست بارها</span>
                        <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                        </span>
                        <span class="badge bg-secondary">{{ $cargoAcceptsCount }}</span>
                    </a>
                </div>
            </div>
        @endif
        @if (in_array('dashboardAllOwner', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-success o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-users"></i>
                        </div>
                        <h1 class="text-center text-white">{{ $countOfOwners }} </h1>
                        <h2 class="text-center text-white">کل صاحبان بار</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ route('owner.index') }}">
                        <span class="float-left">لیست صاحبان بار</span>
                        <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        @endif
        @if (in_array('dashboardAllBearing', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-warning o-hidden h-100">

                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-building"></i>
                        </div>
                        <h1 class="text-center text-white">{{ $countOfBearings }} </h1>
                        <h2 class="text-center text-white">کل باربریها</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ url('admin/bearing') }}">
                        <span class="">لیست باربریها</span>
                    </a>
                </div>
            </div>
        @endif
        @if (in_array('dashboardAllCustomers', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-success o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-users"></i>
                        </div>
                        <h1 class="text-center text-white">{{ $countOfCustomers }} </h1>
                        <h2 class="text-center text-white">کل صاحب بارها</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ url('admin/customers') }}">
                        <span class="float-left">لیست صاحب بارها</span>
                        <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        @endif
        @if (in_array('dashboardAllMessage', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-danger o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-comments"></i>
                        </div>
                        <h1 class="text-center text-white">{{ $countOfContactUses }} </h1>
                        <h2 class="text-center text-white">کل پیام ها</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ url('admin/messages') }}">
                        <span class="float-left">لیست پیام ها</span>
                        <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        @endif

        @if (Auth::user()->role == 'admin')
            @if (in_array('dashboardAllDriver', auth()->user()->userAccess))
                <div class="col-xl-3 col-sm-6 mb-3">
                    <div class="card text-white bg-success o-hidden h-100">
                        <div class="card-body">
                            <div class="card-body-icon">
                                <i class="fas fa-fw fa-truck"></i>
                            </div>
                            <h1 class="text-center text-white">{{ $countOfDrivers }} </h1>
                            <h2 class="text-center text-white">رانندگان</h2>
                        </div>
                        <a class="card-footer text-white clearfix small z-1 btn btn-light" href="{{ route('adminDrivers') }}">
                            <span class="float-left">لیست رانندگان</span>
                            <span class="float-right">
                                <i class="fas fa-angle-right"></i>
                            </span>
                        </a>
                    </div>
                </div>
            @endif
        @endif


        @if (in_array('dashboardAllDriverActivity', auth()->user()->userAccess))
            <div class="col-xl-3 col-sm-6 mb-3">
                <div class="card text-white bg-primary o-hidden h-100">
                    <div class="card-body">
                        <div class="card-body-icon">
                            <i class="fas fa-fw fa-mobile"></i>
                        </div>
                        <h1 class="text-center text-white"></h1>
                        <h2 class="text-center text-white">گزارش فعالیت رانندگان</h2>
                    </div>
                    <a class="card-footer text-white clearfix small z-1 btn btn-light"
                        href="{{ url('admin/driversActivities') }}">
                        <span class="float-left">گزارش فعالیت رانندگان</span>
                        <span class="float-right">
                            <i class="fas fa-angle-right"></i>
                        </span>
                    </a>
                </div>
            </div>
        @endif

    </div>

@stop
