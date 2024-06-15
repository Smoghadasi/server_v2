@extends('layouts.dashboard')

@section('content')

    <div class="card">

        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col">
                    شماره تلفن های لیست ممنوعه
                </div>
                <div class="col" style="text-align: left;">
                    <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal"
                        data-bs-target="#blockPhoneNumberForm">
                        اضافه کردن
                    </button>
                </div>
            </div>
        </h5>
        <div class="card-body">

            <div id="blockPhoneNumberForm" class="modal fade" role="dialog">
                <div class="modal-dialog">

                    <!-- Modal content-->
                    <form action="{{ route('blockedPhoneNumber.store') }}" method="post" class="modal-content">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">وارد کردن شماره تلفن به لیست ممنوعه</h4>
                        </div>
                        <div class="modal-body text-right">

                            <div class="form-group col-lg-12">
                                <input class="m-1 form-control" name="phoneNumber" type="text" placeholder="شماره تلفن">
                            </div>
                            <div class="form-group col-lg-12">
                                <input class="m-1 form-control" name="nationalCode" type="text" placeholder="کد ملی">
                            </div>
                            <div class="form-group col-lg-12">
                                <input class="m-1 form-control" name="name" type="text"
                                    placeholder="نام و نام خانوادگی">
                            </div>
                            <div class="form-group col-lg-12">
                                <textarea class=" m-1 form-control" name="description" placeholder="توضیحات"></textarea>
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
            <form action="{{ route('search.blockNumber') }}" method="post">
                @csrf
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    {{-- <h6>جستجو : </h6> --}}
                    <div class="container">
                        <div class="row row-cols-4">
                            <div class="col">
                                <div class="form-group">
                                    <label>شماره تلفن :</label>
                                    <input type="text" name="mobileNumber" class="form-control">
                                </div>
                            </div>
                            <div class="col">
                                <div class="form-group">
                                    <label>کدملی :</label>
                                    <input type="text" name="nationalCode" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>
                </div>
            </form>

            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>شماره</th>
                        <th>کد ملی</th>
                        <th>نام و نام خانوادگی</th>
                        <th>توضیحات</th>
                        <th>تاریخ</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @foreach ($blockedPhoneNumbers as $blockedPhoneNumber)
                        <tr>
                            <td>{{ $i++ }}</td>
                            <td>
                                {{ $blockedPhoneNumber->phoneNumber ?? '-' }}
                            </td>
                            <td>
                                {{ $blockedPhoneNumber->nationalCode ?? '-' }}
                            </td>
                            <td>
                                {{ $blockedPhoneNumber->name ?? '-' }}
                            </td>
                            <td>
                                {{ $blockedPhoneNumber->description ?? '-' }}
                            </td>

                            @php
                                $pieces = explode(' ', $blockedPhoneNumber->created_at);
                            @endphp
                            <td dir="ltr">
                                {{ gregorianDateToPersian($blockedPhoneNumber->created_at, '-', true) . ' ' . $pieces[1] }}
                            </td>
                            <td>
                                <form action="{{ route('blockedPhoneNumber.destroy', $blockedPhoneNumber->phoneNumber) }}"
                                    method="POST">
                                    @csrf
                                    @method('delete')
                                    <button type="submit" class="btn btn-danger btn-sm">حذف</button>
                                </form>
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
