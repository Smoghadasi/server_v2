@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تایید بار ها
        </h5>
        @if(in_array('loads',auth()->user()->userAccess))
            <div class="alert alert-info text-right">
                @if(isNewLoadAutoAccept())
                تایید بار ها بصورت خودکار
                <a class="btn btn-danger" href="{{ url('admin/changeSiteOption/newLoadAutoAccept') }}">
                    تغییر به غیر خودکار
                </a>
                @else
                تایید بار ها بصورت غیر خودکار
                <a class="btn btn-primary" href="{{ url('admin/changeSiteOption/newLoadAutoAccept') }}">
                    تغییر به خودکار
                </a>
                @endif
            </div>
        @endif

        <div class="card-body">

            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان</th>
                            <th>باربری یا صاحب بار</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>ناوگان</th>
                            <th>تلفن</th>
                            <th>ساعت و تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($cargoAccepts as $cargoAccept)
                            <tr>
                                <td>{{ ($cargoAccepts ->currentpage()-1) * $cargoAccepts ->perpage() + $loop->index + 1 }}</td>
                                <td>{{ $cargoAccept->title ?? '-' }}</td>
                                <td>{{ $cargoAccept->userType == ROLE_CUSTOMER ? 'صاحب بار' : 'باربری' }}</td>
                                <td>{{ $cargoAccept->toCity }}</td>
                                <td>{{ $cargoAccept->fromCity }}</td>
                                <td>
                                    @php
                                        $fleets = json_decode($cargoAccept->fleets, true);
                                    @endphp
                                    @foreach ($fleets as $fleet)
                                        <span class="alert alert-primary p-1 m-1 small" style="line-height: 2rem">{{ $fleet['title'] }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $cargoAccept->mobileNumberForCoordination }}</td>
                                @php
                                    $pieces = explode(" ", $cargoAccept->created_at);
                                @endphp
                                <td>{{ gregorianDateToPersian($cargoAccept->created_at, '-', true) . ' ' . $pieces[1] }}</td>
                                <td style="display: flex;">
                                    <form action="{{ route('accept.cargo.store', ['load' => $cargoAccept->id]) }}"
                                        method="POST">
                                        @csrf
                                        <div class="container">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="hidden" name="accept" value="1">
                                                    <button type="submit" class="btn btn-success btn-sm">قبول</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    <form action="{{ route('accept.cargo.store', ['load' => $cargoAccept->id]) }}"
                                        method="POST">
                                        @csrf
                                        <div class="container">
                                            <div class="row">
                                                <div class="col">
                                                    <input type="hidden" name="accept" value="0">
                                                    <button type="submit" class="btn btn-danger btn-sm">رد</button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                    {{-- <a class="btn btn-danger btn-sm"
                                        href="{{ route('accept.cargo.store', $cargoAccept) }}">رد</a> --}}
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $cargoAccepts }}
            </div>
        </div>
    </div>
@endsection
