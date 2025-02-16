@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            لیست بارها ({{ $countLoads }})
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

                            <div class="col-md-3">
                                <input type="text" placeholder="شماره تلفن صاحب بار" class="form-control col-md-4"
                                    name="mobileNumber" id="mobileNumber" />
                            </div>

                        </div>
                        <button class="btn btn-primary m-2">جستجو</button>
                    </div>
                    @if (isset($message))
                        <div class="alert alert-info text-right">{{ $message }}</div>
                    @endif
                    @if (isset($firstDateLoad))
                        <div class="row alert alert-info text-right">
                            <div class="col-2">
                                تاریخ اولین بار
                            </div>
                            <div class="col-2" style="text-align: right">
                                {{ $firstDateLoad->loadingDate }}
                            </div>
                            <div class="col " style="text-align: right">

                                @if ($loads[0]?->ownerAuthenticated == true)
                                    صاحب بار تایید شده
                                    <i class="menu-icon tf-icons bx bx-check-shield text-success"></i>
                                @endif
                            </div>

                        </div>

                    @endif
                </form>
                <form action="{{ route('load.delete.all') }}" method="post" class="form-inline">
                    @csrf
                    @method('delete')

                    <div class="table-responsive">
                        <table class="table small">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>ردیف</th>
                                    <th>عنوان بار</th>
                                    <th>شماره تماس</th>
                                    <th>ناوگان</th>
                                    <th>مبدا</th>
                                    <th>مقصد</th>
                                    <th>تعداد</th>
                                    <th>اپراتور</th>
                                    <th>تاریخ</th>
                                    <th>نمایش</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 0; ?>
                                @foreach ($loads as $key => $load)
                                    <tr>
                                        <td><input type="checkbox" name="loads[]" id="loads[]"
                                                value="{{ $load->id }}">
                                        </td>
                                        <td>
                                            {{ $key + 1 }}
                                        </td>
                                        <td>
                                            @if ($load->deleted_at != null)
                                                <i class="menu-icon tf-icons bx bx-trash text-danger"></i>
                                            @endif
                                            @if ($load->isBot == 1 && $load->userType == 'owner')
                                                <i class="menu-icon tf-icons bx bx-bot text-primary"></i>
                                            @endif
                                            {{ $load->title }}
                                        </td>
                                        <td>{{ $load->mobileNumberForCoordination }}</td>
                                        <td>
                                            @php
                                                $fleets = json_decode($load->fleets, true);
                                                $maxItems = 3;
                                                for ($i = 0; $i < min(count($fleets), $maxItems); $i++) {
                                                    echo '<span class="alert alert-info m-1 p-1">' .
                                                        $fleets[$i]['title'] .
                                                        '</span>';
                                                }
                                                if (count($fleets) > $maxItems) {
                                                    echo '<span class="alert alert-info m-1 p-1">...</span>';
                                                }

                                            @endphp

                                        </td>
                                        <td>{{ $load->fromCity }}</td>
                                        <td>{{ $load->toCity }}</td>
                                        <td>
                                            <span class="badge bg-primary">بازدید : {{ $load->driverVisitCount }}</span>
                                            <span>
                                                <a class="badge bg-success"
                                                    href="{{ route('load.searchLoadDriverCall', $load->id) }}">
                                                    تماس: {{ $load->numOfDriverCalls }}
                                                </a>
                                            </span>
                                        </td>
                                        <td>
                                            @foreach ($operators as $operator)
                                                @if ($operator->id == $load->operator_id)
                                                    صاحب بار / {{ $operator->name }} {{ $operator->lastName }}
                                                @endif
                                            @endforeach
                                        </td>
                                        @php
                                            $pieces = explode(' ', $load->created_at);
                                        @endphp
                                        <td>{{ $load->loadingDate . ' ' . $pieces[1] }}</td>
                                        <td>
                                            <div class="dropdown">
                                                <div class="btn-group dropstart">
                                                    <button type="button" class="btn btn-primary dropdown-toggle"
                                                        data-bs-toggle="dropdown" aria-expanded="false">
                                                        عملیات
                                                    </button>
                                                    <ul class="dropdown-menu">
                                                        <li>
                                                            <a class="dropdown-item"
                                                                href="{{ url('admin/loadInfo') }}/{{ $load->id }}">
                                                                جزئیات
                                                            </a>
                                                        </li>

                                                        <li>
                                                            <button type="button" class="dropdown-item"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#removeLoad_{{ $load->id }}">حذف
                                                            </button>
                                                        </li>
                                                    </ul>
                                                    <div id="removeLoad_{{ $load->id }}" class="modal fade"
                                                        role="dialog">
                                                        <div class="modal-dialog">

                                                            <!-- Modal content-->
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h4 class="modal-title">حذف بار</h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <p>آیا مایل به حذف بار
                                                                        <span class="text-primary">
                                                                            {{ $load->title }}</span>
                                                                        هستید؟
                                                                    </p>
                                                                </div>
                                                                <div class="modal-footer text-left">
                                                                    <form action="{{ route('remove.load', $load) }}"
                                                                        method="POST">
                                                                        @csrf
                                                                        @method('DELETE')
                                                                        <button class="btn btn-primary"
                                                                            type="submit">حذف</button>
                                                                    </form>
                                                                    <button type="button" class="btn btn-danger"
                                                                        data-bs-dismiss="modal">
                                                                        انصراف
                                                                    </button>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>


                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group mt-2">
                        <input type="submit" name="submit" class="btn btn-danger" value="حذف دسته جمعی">
                    </div>
                </form>
            </div>

        </div>
    </div>

@stop
