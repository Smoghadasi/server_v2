@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">لیست صاحبان بار</h5>
        <div class="card-body">
            <div class="my-3">
                <div class="row justify-content-between">
                    <div class="col">
                        <a href="{{ route('ownerAuth.index') }}" class="alert p-1 alert-secondary">در حال بررسی :
                            {{ $ownerPenddingCounts }}</a>
                        <a href="{{ route('owner.reject') }}" class="alert p-1 alert-danger">تایید نشده :
                            {{ $ownerRejectCounts }}</a>
                        <a href="{{ route('owner.accept') }}" class="alert p-1 alert-success">تایید شده :
                            {{ $ownerAcceptCounts }}</a>
                        <a href="{{ route('owner.ownerRejected') }}" class="alert p-1 alert-dark">رد شده :
                            {{ $ownerRejectedCounts }}</a>

                    </div>
                    <div class="col" style="text-align: left;">
                        <a href="{{ route('loadToday.owner') }}" class="alert p-1 alert-primary">تعداد بار های ثبت شده
                            امروز
                            (کل): {{ $loadsToday }}</a>
                        <a href="{{ route('admin.load.owner') }}" class="alert p-1 alert-dark">تعداد بار های ثبت شده
                            امروز
                            (صاحب بار): {{ $loadsTodayOwner }}</a>
                    </div>
                </div>
            </div>
            <form method="get" action="{{ route('owner.search') }}">
                <div class="form-group row">
                    <div class="col-md-4 mt-3">
                        <input class="form-control" name="searchWord" id="searchWord"
                            placeholder="شماره موبایل ، کدملی و...">
                    </div>
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="fleet_id">
                            <option disabled selected>انتخاب ناوگان</option>
                            @foreach ($fleets as $fleet)
                                <option value="{{ $fleet->id }}">{{ $fleet->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mt-3">
                        <select class="form-select" name="status">
                            <option disabled selected>وضعیت</option>
                            <option value="1">تایید شده</option>
                            <option value="0">تایید نشده</option>
                        </select>
                    </div>
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
        </div>


    @stop
