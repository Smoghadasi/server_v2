@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات صاحب بار ها
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>صاحب بار</th>
                    <th>عنوان</th>
                    <th>شکایت یا انتقاد از</th>
                    <th>شماره تلفن مورد نظر</th>
                    <th>کد پیگیری</th>
                    <th>متن پیام</th>
                    <th>پاسخ ادمین</th>
                    <th></th>
                </tr>
                </thead>
                <tbody>
                @foreach($complaintsCustomer as $key => $complaintCustomer)
                    <tr>
                        <td>{{ (($complaintsCustomer->currentPage()-1) * $complaintsCustomer->perPage()) + ($key + 1) }}</td>
                        <td>{{ $complaintCustomer->customer }}</td>
                        <td>{{ $complaintCustomer->title }}</td>
                        <td>{{ $complaintCustomer->complaint }}</td>
                        <td>{{ $complaintCustomer->phoneNumber }}</td>
                        <td>{{ $complaintCustomer->trackingCode }}</td>
                        <td>{{ $complaintCustomer->message }}</td>
                        <td>{{ $complaintCustomer->adminMessage }}</td>
                        <td>
                            <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3" data-bs-toggle="modal"
                                    data-bs-target="#adminMessageForm_{{ $complaintCustomer->id }}">
                                پاسخ به صاحب بار
                            </button>

                            <div id="adminMessageForm_{{ $complaintCustomer->id }}" class="modal fade"
                                 role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <form
                                        action="{{ url('admin/storeComplaintCustomerAdminMessage') }}/{{ $complaintCustomer->id }}"
                                        method="post"
                                        class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">پاسخ به صاحب بار</h4>
                                        </div>
                                        <div class="modal-body text-right">

                                            <div>
                                                عنوان پیام :
                                                {{ $complaintCustomer->title }}
                                            </div>

                                            <div class="form-group">
                                                <label>متن پاسخ ادمین :</label>
                                                <textarea class="form-control" name="adminMessage" id="adminMessage"
                                                          placeholder="پاسخ ادمین"></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer text-left">
                                            <button type="submit" class="btn btn-primary mr-1">ثبت پاسخ</button>
                                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                انصراف
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>

            </table>

            <div class="mt-3">
                {{ $complaintsCustomer }}
            </div>

        </div>
    </div>

@stop

