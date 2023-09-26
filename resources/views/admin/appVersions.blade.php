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
                        <input type="number" class="form-control" name="driverVersion" value="{{ $appVersion->driverVersion }}">
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
                </tr>
                @foreach($driverVersions as $key => $driverVersion)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $driverVersion->version }}</td>
                        <td>{{ $driverVersion->total }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>



@stop
