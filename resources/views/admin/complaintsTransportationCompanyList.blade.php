@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات باربری ها
        </h5>
        <div class="card-body">
            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>باربری</th>
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
                @foreach($complaintsTransportationCompany as $key => $complaintsTransportationCompany)
                    <tr>
                        {{--                        <td>{{ (($complaintsTransportationCompany->currentPage()-1) * $complaintsTransportationCompany->perPage()) + ($key + 1) }}</td>--}}
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $complaintsTransportationCompany->transportationCompany }}</td>
                        <td>{{ $complaintsTransportationCompany->title }}</td>
                        <td>{{ $complaintsTransportationCompany->complaint }}</td>
                        <td>{{ $complaintsTransportationCompany->phoneNumber }}</td>
                        <td>{{ $complaintsTransportationCompany->trackingCode }}</td>
                        <td>{{ $complaintsTransportationCompany->message }}</td>
                        <td>{{ $complaintsTransportationCompany->adminMessage }}</td>
                        <td>
                            <button type="button" class="btn btn-primary mb-3 btn-sm text-nowrap" data-bs-toggle="modal"
                                    data-bs-target="#adminMessageForm_{{ $complaintsTransportationCompany->id }}">
                                پاسخ به باربری
                            </button>

                            <div id="adminMessageForm_{{ $complaintsTransportationCompany->id }}" class="modal fade"
                                 role="dialog">
                                <div class="modal-dialog">

                                    <!-- Modal content-->
                                    <form
                                        action="{{ url('admin/storeComplaintTransportationCompanyAdminMessage') }}/{{ $complaintsTransportationCompany->id }}"
                                        method="post"
                                        class="modal-content">
                                        @csrf
                                        <div class="modal-header">
                                            <h4 class="modal-title">پاسخ به باربری</h4>
                                        </div>
                                        <div class="modal-body text-right">

                                            <div>
                                                عنوان پیام :
                                                {{ $complaintsTransportationCompany->title }}
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

            {{--            <div class="mt-3">  {{ $complaintsTransportationCompany }}</div>--}}

        </div>
    </div>





@stop

