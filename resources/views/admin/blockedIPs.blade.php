@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            آی پی های مسدود
        </h5>
        <div class="card-body">

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>شماره</th>
                        <th>نام و نام خانوادگی/عنوان</th>
                        <th>نوع کاربر</th>
                        <th>حذف از لیست</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @foreach ($blockedips as $key => $blockedip)
                        <tr>
                            <td>{{ ($blockedips->currentPage() - 1) * $blockedips->perPage() + ($key + 1) }}</td>
                            <td>
                                {{ $blockedip->ip }}
                            </td>
                            <td>
                                {{ $blockedip->name }}
                            </td>
                            <td>
                                @if ($blockedip->userType == ROLE_TRANSPORTATION_COMPANY)
                                    باربری
                                @elseif($blockedip->userType == ROLE_CUSTOMER)
                                    صاحب بار
                                @elseif($blockedip->userType == ROLE_DRIVER)
                                    راننده
                                @endif
                            </td>
                            <td>

                                <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#unBlockUserIp_{{ $blockedip->id }}">
                                    حذف از لیست Ipهای مسدود
                                </button>

                                <div id="unBlockUserIp_{{ $blockedip->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h4 class="modal-title">حذف از لیست Ipهای مسدود</h4>
                                            </div>
                                            <div class="modal-body">
                                                <p>آیا مایل به حذف کردن IP
                                                    این کاربر
                                                    از لیست مسدودها هستید؟
                                                </p>
                                            </div>
                                            <div class="modal-footer text-left">
                                                <a class="btn btn-primary"
                                                    href="{{ url('admin/unBlockUserIp') }}/{{ $blockedip->user_id }}/{{ $blockedip->userType }}">
                                                    بله از لیست حذف شود
                                                </a>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $blockedips }}
            </div>

        </div>
    </div>

@stop
