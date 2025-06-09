@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">لیست صاحبان بار</h5>
        <div class="card-body">
            <div class="my-3">
                <div class="row justify-content-between">
                    <div class="col">
                        <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                وضعیت صاحبان بار
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('ownerAuth.index') }}">در حال بررسی: {{ $ownerPenddingCounts }}</a></li>
                                <li><a class="dropdown-item text-danger" href="{{ route('owner.reject') }}">تایید نشده: {{ $ownerRejectCounts }}</a></li>
                                <li><a class="dropdown-item text-success" href="{{ route('owner.accept') }}">تایید شده: {{ $ownerAcceptCounts }}</a></li>
                                <li><a class="dropdown-item text-dark" href="{{ route('owner.ownerRejected') }}">رد شده: {{ $ownerRejectedCounts }}</a></li>
                                <li><a class="dropdown-item text-primary" href="{{ route('bookmark.index', ['type' => 'owner']) }}">علامت گذاری شده ها: {{ $ownerBookmarkCount }}</a></li>
                                <li><a class="dropdown-item text-primary" href="{{ route('owner.index', ['isLimitLoad' => 1]) }}">صاحبان بار محدود شده برای بار: {{ $ownerLimitLoadCount }}</a></li>
                            </ul>
                        </div>
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
                        <select class="form-select" name="isAccepted">
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
