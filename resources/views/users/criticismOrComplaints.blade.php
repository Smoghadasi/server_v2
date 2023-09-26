<?php $dashboard = 'layouts.bearingDashboard';?>
@if(auth('bearing')->check())

@elseif (auth('customer')->check())
    <?php $dashboard = 'layouts.customerDashboard';?>
@endif

@extends($dashboard)

@section('content')

    <ol class="breadcrumb" xmlns="http://www.w3.org/1999/html">
        <li class="breadcrumb-item">
            شکایات و انتقادات صاحب بار ها
        </li>
    </ol>

    <div class="col-lg-12 m-2 text-right">
        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                data-target="#newCriticismOrComplaintForm">ثبت انتقاد یا شکایت جدید
        </button>
    </div>

    <div id="newCriticismOrComplaintForm" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <form method="post"
                  @if(auth('bearing')->check())
                  action="{{ url('user/storeComplaintTransportationCompanyInWeb') }}"
                  @else
                  action="{{ url('user/storeComplaintCustomerInWeb') }}"
                  @endif
                  class="modal-content">
                @csrf
                <div class="modal-header">
                    <h4 class="modal-title">ثبت انتقاد یا شکایت جدید</h4>
                </div>
                <div class="modal-body text-right">
                    <div class="form-group">
                        <label class="col-lg-12">
                            <input type="radio" name="complaint" value="راننده" checked>
                            شکایت یا انتقاد از راننده
                        </label>
                        @if(auth('bearing')->check())
                            <label class="col-lg-12">
                                <input type="radio" name="complaint" value="باربری">
                                شکایت یا انتقاد از صاحب بار
                            </label>
                        @else
                            <label class="col-lg-12">
                                <input type="radio" name="complaint" value="باربری">
                                شکایت یا انتقاد از باربری
                            </label>
                        @endif
                    </div>

                    <div class="form-group">
                        <label>عنوان : </label>
                        <input type="text" class="form-control" name="title" placeholder="عنوان"/>
                    </div>
                    <div class="form-group">
                        <label>شماره تلفن مورد نظر : </label>
                        <input type="tel" name="phoneNumber" class="form-control" placeholder="شماره تلفن مورد نظر"/>
                    </div>
                    <div class="form-group">
                        <label>پیام شما : </label>
                        <textarea name="message" class="form-control" placeholder="پیام شما"></textarea>
                    </div>
                </div>
                <div class="modal-footer text-left">
                    <button type="submit" class="btn btn-primary">
                        ثبت
                    </button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        انصراف
                    </button>
                </div>
            </form>

        </div>
    </div>

    <div class="container">
        <div class="col-md-12">

            <table class="table table-bordered">
                <thead>
                <tr>
                    <th>#</th>
                    <th>عنوان</th>
                    <th>شکایت یا انتقاد از</th>
                    <th>شماره تلفن مورد نظر</th>
                    <th>کد پیگیری</th>
                    <th>متن پیام</th>
                    <th>پاسخ ادمین</th>
                </tr>
                </thead>
                <tbody>
                @foreach($complaints as $key => $complaint)
                    <tr>
                        <td>{{ (($complaints->currentPage()-1) * $complaints->perPage()) + ($key + 1) }}</td>
                        <td>{{ $complaint->title }}</td>
                        <td>{{ $complaint->complaint }}</td>
                        <td>{{ $complaint->phoneNumber }}</td>
                        <td>{{ $complaint->trackingCode }}</td>
                        <td>{{ $complaint->message }}</td>
                        <td>{{ $complaint->adminMessage }}</td>
                    </tr>
                @endforeach
                </tbody>

            </table>

            {{ $complaints }}

        </div>
    </div>
@stop
