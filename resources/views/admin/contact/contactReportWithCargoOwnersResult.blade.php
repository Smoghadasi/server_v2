@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            تاریخچه تماس با شماره :
            {{ $contactReportWithCargoOwner->mobileNumber }}

            @if(strlen($contactReportWithCargoOwner->nameAndLastName))
                - {{ $contactReportWithCargoOwner->nameAndLastName }}
            @endif


            <span class="text-primary small mr-2">{{ $contactReportWithCargoOwner->registerStatus }}</span>


            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#removeDriver">
                @if(strlen($contactReportWithCargoOwner->nameAndLastName))
                    ویرایش
                @else
                    ذخیره
                @endif
                نام و نام خانوادگی
            </button>
            <div id="removeDriver" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form
                        action="{{ url('admin/storeContactCargoOwnerNameAndLastname') }}/{{ $contactReportWithCargoOwner->id }}"
                        method="post"
                        class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">
                                @if(strlen($contactReportWithCargoOwner->nameAndLastName))
                                    ویرایش
                                @else
                                    ذخیره
                                @endif
                                نام و نام خانوادگی</h4>
                        </div>
                        <div class="modal-body">
                            <label>نام مورد نظر :</label>
                            <input type="tel"
                                   @if(strlen($contactReportWithCargoOwner->nameAndLastName))
                                   value="{{ $contactReportWithCargoOwner->nameAndLastName }}"
                                   @endif
                                   class="form-control" placeholder="نام و نام خانوادگی" name="nameAndLastName">
                        </div>
                        <div class="modal-footer text-left">
                            <button class="btn btn-primary" type="submit">
                                @if(strlen($contactReportWithCargoOwner->nameAndLastName))
                                    ویرایش
                                @else
                                    ذخیره
                                @endif
                            </button>
                            <button type="button" class="btn btn-danger"
                                    data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </h5>
        <div class="card-body">

            <button type="button" class="btn btn-primary btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#storeResult">
                ثبت نتیجه
            </button>
            <div id="storeResult" class="modal fade" role="dialog">
                <div class="modal-dialog  modal-lg">

                    <!-- Modal content-->
                    <form
                        action="{{ url('admin/storeContactReportWithCargoOwnerResult') }}"
                        method="post"
                        class="modal-content">
                        @csrf
                        <input type="hidden" value="{{ $contactReportWithCargoOwner->id }}"
                               name="contactReportWithCargoOwnerId">
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

                @foreach($contactReportWithCargoOwner->results as $result)
                    <div class="col-lg-8 text-dark border-primary border rounded m-2 p-2" style="background: #f1f1f1">
                        <div class="small">
                        <span>
                        اپراتور : {{ $result->operator->name }} {{ $result->operator->lastName }}
                        </span>
                            <span class="mr-5">
                            تاریخ و ساعت
                            {{ $result->persianDate }}
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


@stop
