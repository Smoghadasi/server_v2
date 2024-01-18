@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شکایات و انتقادات صاحبان بار
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>عنوان</th>
                        <th>شماره تلفن</th>
                        <th>کد پیگیری</th>
                        <th>متن پیام</th>
                        <th>پاسخ ادمین</th>
                        <th>تاریخ</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($complaintsOwnerLists as $key => $complaintsOwner)
                        <tr>
                            <td>{{ ($complaintsOwnerLists->currentPage() - 1) * $complaintsOwnerLists->perPage() + ($key + 1) }}
                            </td>
                            <td>{{ $complaintsOwner->title }}</td>
                            <td>{{ $complaintsOwner->phoneNumber }}</td>
                            <td>{{ $complaintsOwner->trackingCode }}</td>
                            <td>{{ $complaintsOwner->message ?? '-' }}</td>
                            <td>{{ $complaintsOwner->adminMessage ?? '-' }}</td>

                            @php
                                $pieces = explode(' ', $complaintsOwner->created_at);
                            @endphp
                            <td>{{ gregorianDateToPersian($complaintsOwner->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm text-nowrap mb-3" data-bs-toggle="modal"
                                    data-bs-target="#adminMessageForm_{{ $complaintsOwner->id }}">
                                    پاسخ به صاحب بار
                                </button>

                                <div id="adminMessageForm_{{ $complaintsOwner->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form
                                            action="{{ route('admin.message.complaintOwner', $complaintsOwner) }}"
                                            method="post" class="modal-content">
                                            @csrf
                                            <div class="modal-header">
                                                <h4 class="modal-title">پاسخ به صاحب بار</h4>
                                            </div>
                                            <div class="modal-body text-right">

                                                <div>
                                                    عنوان پیام :
                                                    {{ $complaintsOwner->title }}
                                                </div>

                                                <div class="form-group">
                                                    <label>متن پاسخ ادمین :</label>
                                                    <textarea class="form-control" name="adminMessage" id="adminMessage" placeholder="پاسخ ادمین"></textarea>
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
                {{ $complaintsOwnerLists }}
            </div>

        </div>
    </div>

@stop
