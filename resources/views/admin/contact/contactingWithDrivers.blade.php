@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تماس با رانندگان
        </h5>
        <div class="card-body">
            <div>
                <form action="{{ url('admin/searchDriverCall') }}" method="post">
                    @csrf
                    <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                        <h6>جستجوی تماس : </h6>
                        <div class="container">
                            <div class="row row-cols-4">
                                <div class="col">
                                    <div class="form-group">
                                        <input type="text" name="mobileNumber" placeholder="شماره تلفن" class="form-control">
                                    </div>
                                </div>
                                <div class="col">
                                    <button class="btn btn-info" type="submit">جستجو</button>
                                </div>

                            </div>
                            <div class="form-group my-4">
                            </div>
                        </div>
                    </div>
                </form>
                تعداد تماس امروز من :
                {{ $resultOfContactingWithDriver }}
            </div>


                <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>نام راننده</th>
                    <th>ناوگان</th>
                    <th>شماره تلفن</th>
                    <th>نسخه اپلیکیشن</th>
                    <th>تعداد پرداخت(موفق و ناموفق)</th>
                    <th>تعداد کل تماس ها</th>
                    <th>تاریخ ثبت نام</th>
                    <th>آخرین پیام</th>
                    <th>لیست پیام ها</th>
                </tr>
                </thead>
                <tbody>
                @foreach($drivers as $key => $driver)
                    <tr>
                        <td>{{ (($drivers->currentPage()-1) * $drivers->perPage()) + ($key + 1 ) }}</td>
                        <td>{{ $driver->name }} {{ $driver->lastName }}</td>
                        <td>{{ $driver->fleetTitle }}</td>
                        <td>{{ $driver->mobileNumber }}</td>
                        <td>{{ $driver->version ?? '-' }}</td>
                        <td>
                            <span class="alert alert-success p-1 m-1 small">
                موفق :
                {{ $driver->countOfPais['isPaid'] }}
                </span>
                <span class="alert alert-danger p-1 m-1 small">
                            ناموفق :
                                {{ $driver->countOfPais['unPaid'] }}
                            </span>
                </td>
                <td>{{ $driver->countOfCalls }}</td>
                <td>{{ convertEnNumberToFa(gregorianDateToPersian($driver->created_at, '-', true)) }}</td>
                <td>
                    @if(isset($driver->resultOfContacting[0]))
                        <span class="alert alert-info m-1 p-1 d-block small">
                                    {{ $driver->resultOfContacting[0]->result }}
                                </span>
                    @else
                    -
                    @endif
                </td>
                <td>
                    <a class="btn btn-primary btn-sm"
                       href="{{ route('contactingWithDriverResult', $driver->id) }}">پیام ها</a>
                </td>
                </tr>
                @endforeach
                </tbody>
                </table>


            <div class="mt-3 mb-3 text-center">
                {{ $drivers }}
            </div>
        </div>
    </div>

@stop
