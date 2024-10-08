@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            ورژن اپلیکیشن ها
        </h5>
        <div class="card-body">
            <div class="card-body text-right">
                <form method="post" action="{{ url('admin/storeAppVersions') }}">
                    @csrf
                    <div class="form-group col-lg-4">
                        <label>ورژن راننده : </label>
                        <input type="number" class="form-control" name="driverVersion"
                            value="{{ $appVersion->driverVersion }}">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>ورژن باربری : </label>
                        <input type="number" class="form-control" name="transportationCompanyVersion"
                            value="{{ $appVersion->transportationCompanyVersion }}">
                    </div>
                    <div class="form-group col-lg-4">
                        <label>ورژن صاحب بار : </label>
                        <input type="number" class="form-control" name="cargoOwnerVersion"
                            value="{{ $appVersion->cargoOwnerVersion }}">
                    </div>
                    <button type="submit" class="btn btn-primary">دخیره</button>
                </form>
            </div>

            <table class="table mt-3">
                <tr>
                    <th>#</th>
                    <th>نسخه رانندگان</th>
                    <th>تعداد نصب فعال</th>
                    <th>عملیات</th>
                </tr>
                @foreach ($driverVersions as $key => $driverVersion)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $driverVersion->version }}</td>
                        <td>{{ $driverVersion->total }}</td>
                        <td>
                            <a class="btn btn-primary btn-sm"
                                href="{{ route('driver.activity.version', $driverVersion->version ?? '1') }}">گزارش فعالیت
                                رانندگان</a>
                        </td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>



@stop
