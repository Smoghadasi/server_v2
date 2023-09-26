@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات رانندگان
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>راننده</th>
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
                @foreach($complaintsDrivers as $key => $complaintDriver)
                    <tr>
                        <td>{{ (($complaintsDrivers->currentPage()-1) * $complaintsDrivers->perPage()) + ($key + 1) }}</td>
                        <td>{{ $complaintDriver->driver }}</td>
                        <td>{{ $complaintDriver->title }}</td>
                        <td>{{ $complaintDriver->complaint }}</td>
                        <td>{{ $complaintDriver->phoneNumber }}</td>
                        <td>{{ $complaintDriver->trackingCode }}</td>
                        <td>{{ $complaintDriver->message }}</td>
                        <td>{{ $complaintDriver->adminMessage }}</td>
                        <td>
                            <button type="button" class="btn btn-primary mb-3 btn-sm text-nowrap"

                                    data-bs-toggle="modal"
                                    data-bs-target="#adminMessageForm_{{ $complaintDriver->id }}">
                                پاسخ به راننده
                            </button>

                            <div id="adminMessageForm_{{ $complaintDriver->id }}" class="modal fade" role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <form
                                        action="{{ url('admin/storeComplaintDriverAdminMessage') }}/{{ $complaintDriver->id }}"
                                        method="post"
                                        class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">پاسخ به راننده</h4>
                                        </div>
                                        <div class="modal-body text-right">

                                            <div>
                                                عنوان پیام :
                                                {{ $complaintDriver->title }}
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
                {{ $complaintsDrivers }}
            </div>

        </div>
    </div>

@stop

