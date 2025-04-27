@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تماس با صاحبان بار و باربری ها
        </h5>
        <div class="card-body">

            @if (isset($countOfCals))
                <div class="h5">
                    تعداد تماس های امروز من :
                    {{ $countOfCals }}
                </div>
            @endif

            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#searchDriver">
                جستجو یا ثبت شماره جدید
            </button>

            <div id="searchDriver" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form action="{{ url('admin/contactReportWithCargoOwners') }}" method="get" class="modal-content">
                        {{-- @csrf --}}
                        <div class="modal-header">
                            <h4 class="modal-title">جستجو یا ثبت شماره جدید</h4>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-lg-6">
                                    <label class="text-right">شماره مورد نظر :</label>
                                    <input type="tel" class="form-control" placeholder="شماره مورد نظر"
                                        name="mobileNumber">
                                </div>
                                <div class="col-lg-6">
                                    <label class="text-right">تاریخ</label>
                                    <input class="form-control" type="text" name="date" id="date"
                                        placeholder="تا تاریخ" autocomplete="off" />
                                    <span id="span2"></span>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button class="btn btn-primary" type="submit">
                                جستجو یا ثبت
                            </button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
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
                        @foreach ($contactReportWithCargoOwners as $key => $contactReportWithCargoOwner)
                            <tr>
                                <td>
                                    {{ ($contactReportWithCargoOwners->currentPage() - 1) * $contactReportWithCargoOwners->perPage() + ($key + 1) }}
                                </td>
                                <td>{{ $contactReportWithCargoOwner->mobileNumber }}</td>
                                <td>
                                    {{ $contactReportWithCargoOwner->nameAndLastName !== '' ? $contactReportWithCargoOwner->nameAndLastName : '-' }}
                                    <span
                                        class="text-primary small mr-1">{{ $contactReportWithCargoOwner->registerStatus }}</span>
                                </td>
                                <td>
                                    {{ $contactReportWithCargoOwner->firstCal !== '' ? $contactReportWithCargoOwner->firstCal : '-' }}
                                </td>
                                <td>
                                    {{ $contactReportWithCargoOwner->lastCal !== '' ? $contactReportWithCargoOwner->lastCal : '-' }}
                                </td>
                                <td>
                                    @if (isset($contactReportWithCargoOwner->results[0]))
                                        <div class="alert alert-info">
                                            {{ $contactReportWithCargoOwner->results[0]->result }}
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <a class="btn btn-primary btn-sm small m-1 p-2"
                                        href="{{ route('admin.contactReportWithCargoOwners', ['mobileNumber' => $contactReportWithCargoOwner->mobileNumber]) }}">تاریخچه
                                        تماس ها</a>

                                    @if (auth()->user()->role == ROLE_ADMIN)
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
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#date, #span1").persianDatepicker();
    </script>
@endsection
