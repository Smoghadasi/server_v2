@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            ناوگان من
        </h5>
        <div class="card-body">
            <form method="post" action="{{ url('admin/updateOperatorFleets') }}">
                @csrf
                @foreach($fleets as $fleet)
                    <div class="col-lg-12">
                        <label class="form-check-label">
                            <input
                                {{--                            @if(in_array($fleet->id, $myFleets))--}}
                                {{--                            checked--}}
                                {{--                            @endif--}}

                                @foreach($myFleets as $myFleet)
                                @if($myFleet == $fleet->id)
                                checked
                                @endif
                                @endforeach

                                type="checkbox" name="fleets[]" value="{{ $fleet->id }}">
                            {{ $fleet->title }}
                        </label>
                    </div>
                @endforeach
                <button class="btn btn-primary" type="submit">ثبت ناوگان</button>
            </form>
        </div>
    </div>

@stop

