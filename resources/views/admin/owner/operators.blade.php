@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">لیست صاحبان بار</h5>
        <div class="card-body">

            <form method="post" action="{{ route('owner.search') }}">
                @csrf
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
                    <div class="col-md-4 mt-3">
                        <button type="submit" class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
            </form>
        </div>


    @stop
