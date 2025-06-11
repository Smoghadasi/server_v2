@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            بار اولیه ها
        </h5>

        <div class="container">
            <form method="get" action="{{ route('firstLoad.index') }}" class="mt-3 mb-3 ">
                <div class="form-group">
                    <div class="col-md-12 row">
                        <div class="col-md-3">
                            <input type="text" placeholder="شماره تلفن" class="form-control col-md-4"
                                name="mobileNumber" id="mobileNumber" />
                        </div>

                    </div>
                    <button type="submit" class="btn btn-primary m-2">جستجو</button>
                </div>

            </form>
            <div class="mt-2">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره</th>
                            <th>وضعیت</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($firstLoads as $firstLoad)
                            <tr>
                                <td>{{ ($firstLoads->currentpage() - 1) * $firstLoads->perpage() + $loop->index + 1 }}
                                </td>
                                <td>{{ $firstLoad->mobileNumberForCoordination ?? '-' }}</td>
                                <td>
                                    @switch($firstLoad->status)
                                        @case(0)
                                            رد
                                            @break
                                        @case(1)
                                            تایید
                                            @break
                                        @default
                                            نامشخص
                                    @endswitch
                                </td>
                                </td>
                                <td style="display: flex;">
                                    <form action="{{ route('firstLoad.update', $firstLoad) }}" method="post">
                                        @method('put')
                                        @csrf
                                        <input type="hidden" value="accept" name="status">
                                        <button type="submit" class="btn btn-sm btn-success">تایید</button>
                                    </form>
                                    <form action="{{ route('firstLoad.update', $firstLoad) }}" method="post">
                                        @method('put')
                                        @csrf
                                        <input type="hidden" value="block" name="status">
                                        <button type="submit" class="btn btn-danger btn-sm">بلوک</button>
                                    </form>
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2 mb-2">
                {{ $firstLoads }}
            </div>
        </div>
    </div>
@endsection
