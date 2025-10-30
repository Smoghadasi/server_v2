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
                            <div class="row">
                                <div class="form-group col-lg-6 col-sm-12">
                                    <label class="form-label">تلفن</label>
                                    <input class="m-1 form-control" name="phoneNumber" type="text">
                                </div>
                                <div class="form-group col-lg-6 col-sm-12">
                                    <label class="form-label">کد ملی</label>
                                    <input class="m-1 form-control" name="nationalCode" type="text">
                                </div>
                                <div class="form-group col-lg-6 col-sm-12">
                                    <label class="form-label">نام و نام خانوادگی</label>
                                    <input class="m-1 form-control" name="name" type="text">
                                </div>
                                <div class="form-group col-lg-6 col-sm-12">
                                    <label class="form-label">نوع</label>
                                    <select class="form-control form-select" name="type" id="">
                                        <option value="operator">اپراتور</option>
                                        <option value="owner">صاحب بار</option>
                                        <option value="both" selected>هر دو</option>
                                    </select>
                                </div>
                                <div class="form-group col-lg-12 col-sm-12 my-2">
                                    <input type="checkbox" class="form-check-input" name="isFraudster" value="1">
                                    <label class="form-check-label" for="exampleCheck1">کلاهبردار</label>
                                </div>
                                <div class="form-group col-lg-12 col-sm-12">
                                    <label class="form-label">توضیحات</label>
                                    <textarea class=" m-1 form-control" rows="5" name="description"></textarea>
                                </div>
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
            <form action="{{ route('blockedPhoneNumber.index') }}" method="get">
                <div class="col-lg-12 border rounded mt-2 mb-2 p-2">
                    {{-- <h6>جستجو : </h6> --}}
                    <div class="container">
                        <div class="row">
                            <div class="col-lg-4 col-sm-12 mb-2">
                                <div class="form-group">
                                    <label>شماره تلفن</label>
                                    <input type="text" name="mobileNumber" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 mb-2">
                                <div class="form-group">
                                    <label>کدملی</label>
                                    <input type="text" name="nationalCode" class="form-control">
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-12 mb-2">
                                <div class="form-group">
                                    <label>نام نام خانوادگی</label>
                                    <input type="text" name="name" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="form-group my-4">
                            <button class="btn btn-info" type="submit">جستجو</button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>شماره</th>
                            <th>کد ملی</th>
                            <th>نام و نام خانوادگی</th>
                            <th>توضیحات</th>
                            <th>اپراتور</th>
                            <th>نوع</th>
                            <th>کلاهبردار</th>
                            <th>تاریخ</th>
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="small text-right">
                        <?php $i = 1; ?>
                        @foreach ($blockedPhoneNumbers as $blockedPhoneNumber)
                            <tr>
                                <td>{{ ($blockedPhoneNumbers->currentPage() - 1) * $blockedPhoneNumbers->perPage() + ++$i }}</td>
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
                                <td>
                                    {{ $blockedPhoneNumber->operator ? $blockedPhoneNumber->operator->name . ' ' . $blockedPhoneNumber->operator->lastName : '-' }}
                                </td>
                                <td>
                                    {{ $blockedPhoneNumber->type ?? '-' }}
                                </td>
                                <td>
                                {{ $blockedPhoneNumber->isFraudster ? 'بله' : 'خیر' }}
                                </td>
                                @php
                                    $pieces = explode(' ', $blockedPhoneNumber->created_at);
                                @endphp
                                <td dir="ltr">
                                    {{ gregorianDateToPersian($blockedPhoneNumber->created_at, '-', true) . ' ' . $pieces[1] }}
                                </td>
                                <td>
                                    <form
                                        action="{{ route('blockedPhoneNumber.destroy', $blockedPhoneNumber->phoneNumber) }}"
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
            </div>


            <div class="mt-3">
                {{ $blockedPhoneNumbers->appends(request()->input())->links(); }}
            </div>

        </div>
    </div>

@stop
