@extends('layouts.dashboard')

@section('content')

    <div class="card">
        <h5 class="card-header">
            شماره تلفن های لیست ممنوعه
        </h5>
        <div class="card-body">

            <button type="button" class="btn btn-primary mb-3"
                    data-bs-toggle="modal"
                    data-bs-target="#blockPhoneNumberForm">
                وارد کردن شماره تلفن به لیست ممنوعه
            </button>
            <div id="blockPhoneNumberForm" class="modal fade"
                 role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form
                        action="{{ url('admin/blockPhoneNumber') }}"
                        method="post"
                        class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">وارد کردن شماره تلفن به لیست ممنوعه</h4>
                        </div>
                        <div class="modal-body text-right">

                            <div class="form-group col-lg-12">
                                <input class="m-1 form-control" name="phoneNumber" type="tel"
                                       placeholder="شماره مورد نظر را وارد نمایید">
                            </div>
                            <div class="form-group col-lg-12">
                                <input class="m-1 form-control" name="name" type="text"
                                       placeholder="نام و نام خانوادگی صاحب شماره تلفن">
                            </div>
                            <div class="form-group col-lg-12">
                <textarea class=" m-1 form-control" name="description"
                          placeholder="توضیحات : علت ممنوع بودن"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer text-left">
                            <button type="submit" class="btn btn-primary mr-1">ثبت در لیست ممنوعه</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <table class="table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>شماره</th>
                    <th>نام و نام خانوادگی</th>
                    <th>توضیحات</th>
                    <th>حذف از لیست</th>
                </tr>
                </thead>
                <tbody class="small text-right">
                <?php $i = 1;?>
                @foreach($blockedPhoneNumbers as $blockedPhoneNumber)
                    <tr>
                        <td>{{ $i++ }}</td>
                        <td>
                            {{ $blockedPhoneNumber->phoneNumber }}
                        </td>
                        <td>
                            {{ $blockedPhoneNumber->name }}
                        </td>
                        <td>
                            {{ $blockedPhoneNumber->description }}
                        </td>
                        <td>
                            <a class="btn btn-sm btn-danger"
                               href="{{ url('admin/unblockPhoneNumber') }}/{{ $blockedPhoneNumber->phoneNumber }}">حذف
                                از لیست</a>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $blockedPhoneNumbers }}
            </div>

        </div>
    </div>

@stop
