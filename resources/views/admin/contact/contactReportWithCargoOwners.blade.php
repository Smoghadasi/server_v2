@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تماس با صاحبان بار و باربری ها
        </h5>
        <div class="card-body">

            @if(isset($countOfCals))
                <div class="h5">
                    تعداد تماس های امروز من :
                    {{ $countOfCals }}
                </div>
            @endif

            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#removeDriver">
                جستجو یا ثبت شماره جدید
            </button>

            <div id="removeDriver" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form action="{{ url('admin/soreNewMobileNumberOfCargoOwner') }}" method="post"
                          class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">جستجو یا ثبت شماره جدید</h4>
                        </div>
                        <div class="modal-body">
                            <label class="col-lg-12 text-right">شماره مورد نظر :</label>
                            <input type="tel" class="form-control" placeholder="شماره مورد نظر" name="mobileNumber">
                        </div>
                        <div class="modal-footer text-left">
                            <button class="btn btn-primary" type="submit">
                                جستجو یا ثبت
                            </button>
                            <button type="button" class="btn btn-danger"
                                    data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>

                </div>
            </div>


            <div class="table pt-3">

                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>شماره تماس</th>
                        <th>نام و نام خانوادگی</th>
                        <th>تاریخ اولین تماس</th>
                        <th>تاریخ آخرین تماس</th>
                        <th>آخرین نتیجه ثبت شده</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($contactReportWithCargoOwners as $key => $contactReportWithCargoOwner)
                        <tr>
                            <td>
                                {{ (($contactReportWithCargoOwners->currentPage()-1) * $contactReportWithCargoOwners->perPage()) + ($key + 1 ) }}
                            </td>
                            <td>{{ $contactReportWithCargoOwner->mobileNumber }}</td>
                            <td>
                                {{ $contactReportWithCargoOwner->nameAndLastName }}
                                <span
                                    class="text-primary small mr-1">{{ $contactReportWithCargoOwner->registerStatus }}</span>
                            </td>
                            <td>{{ $contactReportWithCargoOwner->firstCal }}</td>
                            <td>{{ $contactReportWithCargoOwner->lastCal }}</td>
                            <td>
                                @if(isset($contactReportWithCargoOwner->results[0]))
                                    <div class="alert alert-info">
                                        {{ $contactReportWithCargoOwner->results[0]->result }}
                                    </div>
                                @endif
                            </td>
                            <td>
                                <a class="btn btn-primary btn-sm small m-1 p-2"
                                   href="{{ url('admin/contactReportWithCargoOwners') }}/{{ $contactReportWithCargoOwner->mobileNumber }}">تاریخچه
                                    تماس ها</a>

                                @if(auth()->user()->role==ROLE_ADMIN)
                                    <a class="btn btn-danger btn-sm small m-1 p-2"
                                       href="{{ url('admin/deleteContactReportWithCargoOwners') }}/{{ $contactReportWithCargoOwner->id }}">
                                        حذف
                                    </a>
                                @endif

                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            {{ $contactReportWithCargoOwners }}

        </div>
    </div>

@stop
