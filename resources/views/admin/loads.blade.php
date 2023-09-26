@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارها
        </h5>
        <div class="card-body">

            <div class="text-right">
                <form method="post" action="{{ url('admin/searchLoad') }}" class="mt-3 mb-3 card card-body">
                    <h5>جستجو :</h5>
                    @csrf

                    <input type="hidden" value="{{ $status }}" name="status">

                    <div class="form-group row">
                        <div class="col-md-12">
                            <select class="form-control col-md-3" name="origin_city_id" id="origin_city_id">
                                <option value="0">شهر مبدا</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">
                                        <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?>
                                    </option>
                                @endforeach
                            </select>
                            <select class="form-control col-md-3" name="destination_city_id" id="destination_city_id">
                                <option value="0">شهر مقصد</option>
                                @foreach($cities as $city)
                                    <option value="{{ $city->id }}">
                                        <?php echo str_replace('ك', 'ک', str_replace('ي', 'ی', $city->name)); ?>
                                    </option>
                                @endforeach
                            </select>
                            <select class="form-control col-md-4" name="fleet_id" id="fleet_id">
                                <option value="0">نوع ناوگان</option>
                                @foreach($fleets as $fleet)
                                    <option value="{{ $fleet->id }}">
                                        {{ \App\Http\Controllers\FleetController::getFleetName($fleet->parent_id) }}
                                        -
                                        {{ $fleet->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="btn btn-primary m-2">جستجو</button>
                    </div>
                    @if(isset($message))
                        <div class="alert alert-info text-right">{{ $message }}</div>
                    @endif
                </form>


                <p><a class="btn btn-primary" href="{{ url('admin/addNewLoadForm') }}"> + افزودن بار</a></p>
                <form class="table-responsive" action="{{ url('admin/removeLoad') }}" method="post">
                    @csrf

                    <input type="hidden" value="{{ $status }}" name="status">
                    <table class="table">
                        <thead>
                        <tr>
                            <th>انتخاب</th>
                            <th>#</th>
                            <th>عنوان بار</th>
                            <th>شماره تماس</th>
                            <th>عرض</th>
                            <th>طول</th>
                            <th>ارتفاع</th>
                            <th>وزن</th>
                            <th>تاریخ</th>
                            <th>نمایش</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i = 0;?>
                        @foreach($loads as $key => $load)
                            <tr>
                                <td><input type="checkbox" name="load_id[]" id="load_id[]" value="{{ $load->id }}"></td>
                                <td>
                                    {{ (($loads->currentPage()-1) * $loads->perPage()) + ($key + 1 ) }}
                                </td>
                                <td>{{ $load->title }}</td>
                                <td>{{ $load->mobileNumberForCoordination }}</td>
                                <td>{{ $load->width }}</td>
                                <td>{{ $load->lenght }}</td>
                                <td>{{ $load->height }}</td>
                                <td>{{ $load->weight }}</td>
                                <td>{{ $load->loadingDate }}</td>
                                <td>
                                    <a class="btn btn-sm btn-info" href="{{ url('admin/loadInfo') }}/{{ $load->id }}">
                                        جزئیات</a>
                                    <a class="btn btn-sm btn-success"
                                       href="{{ url('admin/acceptLoadFromLoadList') }}/{{ $load->id }}">تایید بار</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-danger mb-2">حذف دسته ای بارهای انتخاب شده</button>
                </form>
            </div>
            <div class="mt-3">
                {{ $loads  }}
            </div>
        </div>
    </div>

    <script>
        var t = setInterval(function () {
            location.reload();
        }, 60000)
    </script>
@stop
