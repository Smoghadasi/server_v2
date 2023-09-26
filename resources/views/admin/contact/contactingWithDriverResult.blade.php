@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">

        </h5>
        <div class="card-body">
            <div class="container text-right">

                <table class="table">
                    <thead>
                    <tr>
                        <th>نام راننده</th>
                        <th>ناوگان</th>
                        <th>شماره تلفن</th>
                        <th>نسخه اپلیکیشن</th>
                        <th>تعداد پرداخت(موفق و ناموفق)</th>
                        <th>تعداد کل تماس ها</th>
                        <th>تاریخ ثبت نام</th>
                    </tr>
                    </thead>
                    <tbody>

                    <tr>
                        <td>{{ $driver->name }} {{ $driver->lastName }}</td>
                        <td>{{ $driver->fleetTitle }}</td>
                        <td>{{ $driver->mobileNumber }}</td>
                        <td>{{ $driver->version }}</td>
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
                    </tr>

                    </tbody>
                </table>

                <button type="button" class="btn btn-primary btn-sm mt-3"
                        data-bs-toggle="modal"
                        data-bs-target="#storeResult">
                    ثبت نتیجه
                </button>
                <div id="storeResult" class="modal fade" role="dialog">
                    <div class="modal-dialog  modal-lg">

                        <!-- Modal content-->
                        <form
                            action="{{ url('admin/storeContactReportWithDriver') }}"
                            method="post"
                            class="modal-content">
                            @csrf
                            <input type="hidden" value="{{ $driver->id }}"
                                   name="driver_id">
                            <div class="modal-header">
                                <h4 class="modal-title">ثبت نتیجه</h4>
                            </div>
                            <div class="modal-body">
                                <label class="col-lg-12 text-right">نتیجه : </label>
                                <textarea class="form-control" rows="6" placeholder="نتیجه" name="result"></textarea>
                            </div>
                            <div class="modal-footer text-left">
                                <button class="btn btn-primary" type="submit">
                                    ثبت نتیجه
                                </button>
                                <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">
                                    انصراف
                                </button>
                            </div>
                        </form>

                    </div>
                </div>

                <div class="mt-3">
                    <div class="h4 mb-2">نتایج :</div>

                    @foreach($driver->resultOfContacting as $result)
                        <div class="col-lg-8 text-dark border-primary border rounded m-2 p-2" style="background: #f1f1f1">
                            <div class="small">
                        <span>
                        اپراتور : {{ $result->operator->name }} {{ $result->operator->lastName }}
                        </span>
                                <span class="mr-5">
                            تاریخ و ساعت
                            {{ convertEnNumberToFa($result->persianDate) }}
                        </span>
                            </div>

                            <div class="mt-3">
                                {{ $result->result }}
                            </div>
                        </div>


                    @endforeach

                </div>

            </div>
        </div>
    </div>

@stop
