@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            ثبت بار
        </h5>
        <div class="card-body">
            <span class="badge rounded-pill bg-label-dark">تعداد بار در صف : {{ $countOfCargos }} بار</span>
            <form method="POST" action="{{ url('admin/storeCargoInformation') }}" enctype="multipart/form-data">
                @csrf

                <div class="form-group text-right small">
                    @csrf
                    <textarea class="form-control" placeholder="ورود لیست بارها" name="cargo" rows="20"></textarea>
                </div>

                <div class="row form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-primary">
                            ارسال برای بررسی
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

@stop
