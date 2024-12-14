@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            پیام ها
        </h5>
        <div class="card-body">
            <div class="table-responsive mt-4">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان پیام</th>
                            <th>متن پیام</th>
                            <th>نام نام خانوادگی</th>
                            <th>نوع ناوگان</th>
                            <th>شماره تلفن همراه</th>
                            <th>نوع کاربر</th>
                            <th>تاریخ</th>
                            <th>نتیجه</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>
                        @foreach ($messages as $key => $message)
                            <tr @if ($message->status == true) style="background: #f1f1f1" @endif>
                                <td>{{ ($messages->currentPage() - 1) * $messages->perPage() + ($key + 1) }}</td>
                                <td>
                                    @if ($message->status == true)
                                        <a href="{{ route('messages.show', $message->id) }}">{{ $message->title }}</a>
                                    @else
                                        {{ $message->title }}
                                    @endif
                                </td>
                                <td>{{ $message->message }}</td>
                                <td>
                                    {{ $message->nameAndLastName }}
                                    @if ($message->role == ROLE_DRIVER)
                                        <a class="btn btn-primary btn-sm"
                                            href="{{ url('admin/editDriver/') }}/{{ $message->userId }}">
                                            پروفایل راننده
                                        </a>
                                    @endif
                                </td>
                                <td>{{ $message->fleetName }} </td>
                                <td>{{ $message->mobileNumber }}</td>
                                <td>
                                    <?php
                                    switch ($message->role) {
                                        case 'bearing':
                                            echo 'باربری';
                                            break;
                                        case 'customer':
                                            echo 'مشتری';
                                            break;
                                        case 'org':
                                            echo 'سازمان';
                                            break;
                                        case 'driver':
                                            echo 'راننده';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td>{{ $message->messageDateAndTime }}</td>
                                <td>
                                    @if ($message->status == false)
                                        <button type="button" class="btn btn-primary btn-sm text-nowrap"
                                            data-bs-toggle="modal" data-bs-target="#changeMessageStatus_{{ $message->id }}">
                                            ثبت نتیجه
                                        </button>

                                        <!-- Modal -->
                                        <div id="changeMessageStatus_{{ $message->id }}" class="modal fade"
                                            role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <form method="post"
                                                    action="{{ url('admin/changeMessageStatus') }}/{{ $message->id }}"
                                                    class="modal-content">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">ثبت نتیجه</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label> نتیجه تماس با کاربر را ثبت کنید: </label>
                                                            <textarea class="form-control" placeholder="نتیجه ..." name="result"></textarea>
                                                        </div>
                                                        <div class="form-check mt-2">
                                                            <input class="form-check-input" name="notification"
                                                                type="checkbox" id="gridCheck">
                                                            <label class="form-check-label" for="gridCheck">
                                                                ارسال اعلان
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <button type="submit" class="btn btn-primary">
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
                                    @else
                                        {{ $message->result }}
                                    @endif


                                    <button type="button" class="btn btn-danger btn-sm text-nowrap" data-bs-toggle="modal"
                                        data-bs-target="#remove_{{ $message->id }}">
                                        حذف پیام
                                    </button>

                                    <!-- Modal -->
                                    <div id="remove_{{ $message->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                @csrf
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف پیام</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        آیا مایل به حذف این پیام هستید؟
                                                    </div>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <a href="{{ url('admin/removeMessage') }}/{{ $message->id }}"
                                                        class="btn btn-primary">
                                                        بله حذف شود
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
            </div>

            <div class="mt-3">
                {{ $messages }}
            </div>

        </div>
    </div>

@stop
