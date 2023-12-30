@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تایید بار ها
        </h5>
        {{-- @if (auth()->user()->role == 'admin')
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
        @endif --}}

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
                                        <a class="btn btn-sm btn-success"
                                           href="{{ route('acceptCustomer', $cargoAccept->user_id) }}">تایید بار</a>
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
