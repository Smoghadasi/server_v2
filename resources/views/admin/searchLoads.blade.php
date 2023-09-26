@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارها
        </h5>
        <div class="card-body">

            <div class="text-right">
                <form method="post" action="{{ url('admin/searchLoads') }}" class="mt-3 mb-3 card card-body">
                    <h5>جستجو :</h5>
                    @csrf


                    <div class="form-group">
                        <div class="col-md-12 row">
                            <div class="col-md-3">
                                <select class="form-control" name="origin_city_id" id="origin_city_id">
                                    <option value="0">شهر مبدا</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}">
                                            <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?>
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control col-md-3" name="destination_city_id" id="destination_city_id">
                                    <option value="0">شهر مقصد</option>
                                    @foreach ($cities as $city)
                                        <option value="{{ $city->id }}">
                                            <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?>
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control col-md-4" name="fleet_id" id="fleet_id">
                                    <option value="0">نوع ناوگان</option>
                                    @foreach ($fleets as $fleet)
                                        <option value="{{ $fleet->id }}">
                                            {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                            -
                                            {{ $fleet->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select class="form-control col-md-4" name="operator_id" id="operator_id">
                                    <option value="0">اپراتور</option>
                                    @foreach ($operators as $operator)
                                        <option value="{{ $operator->id }}">
                                            {{ $operator->name }} {{ $operator->lastName }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mt-3">
                                <input type="text" placeholder="شماره تلفن صاحب بار" class="form-control col-md-4"
                                    name="mobileNumber" id="mobileNumber" />
                            </div>

                        </div>
                        <button class="btn btn-primary m-2">جستجو</button>
                    </div>
                    @if (isset($message))
                        <div class="alert alert-info text-right">{{ $message }}</div>
                    @endif
                </form>


                <form class="table-responsive" action="{{ url('admin/removeLoad') }}" method="post">
                    @csrf

                    <table class="table small">
                        <thead>
                            <tr>
                                <th>انتخاب</th>
                                <th>#</th>
                                <th>عنوان بار</th>
                                <th>شماره تماس</th>
                                <th>ناوگان</th>
                                <th>مبدا</th>
                                <th>مقصد</th>
                                <th>اپراتور</th>
                                <th>تاریخ</th>
                                <th>نمایش</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 0; ?>
                            @foreach ($loads as $key => $load)
                                <tr>
                                    <td><input type="checkbox" name="load_id[]" id="load_id[]" value="{{ $load->id }}">
                                    </td>
                                    <td>
                                        {{ $key + 1 }}
                                    </td>
                                    <td>{{ $load->title }}</td>
                                    <td>{{ $load->mobileNumberForCoordination }}</td>
                                    <td>
                                        @php
                                            $fleets = json_decode($load->fleets, true);
                                            for ($i = 0; $i < count($fleets); $i++) {
                                                echo '<span class="alert alert-info m-1 p-1">' . $fleets[0]['title'] . '</span>';
                                            }
                                        @endphp

                                    </td>
                                    <td>{{ $load->fromCity }}</td>
                                    <td>{{ $load->toCity }}</td>
                                    <td>
                                        @foreach ($operators as $operator)
                                            @if ($operator->id == $load->operator_id)
                                                {{ $operator->name }} {{ $operator->lastName }}
                                            @endif
                                        @endforeach
                                    </td>
                                    <td>{{ $load->loadingDate }}</td>
                                    <td>
                                        <a class="btn btn-sm btn-info"
                                            href="{{ url('admin/loadInfo') }}/{{ $load->id }}">
                                            جزئیات</a>
                                        <a class="btn btn-sm btn-success"
                                            href="{{ url('admin/acceptLoadFromLoadList') }}/{{ $load->id }}">تایید
                                            بار</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-danger mb-2">حذف دسته ای بارهای انتخاب شده</button>
                </form>
            </div>

        </div>
    </div>

@stop
