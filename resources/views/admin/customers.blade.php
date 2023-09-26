@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            صاحب بارها
        </h5>
        <div class="card-body">
            <form method="post" action="{{ url('admin/customers') }}">
                @csrf
                <div class="form-group row">
                    <div class="col-md-12">
                        <label class="radio-inline"><input type="radio" checked name="searchMethod" value="name">
                            نام</label>
                        <label class="radio-inline"><input type="radio" name="searchMethod" value="lastName">
                            نام خانوادگی</label>
                        <label class="radio-inline"><input type="radio" name="searchMethod" value="nationalCode">
                            کد ملی</label>
                        <label class="radio-inline"><input type="radio" name="searchMethod" value="mobileNumber">
                            شماره تلفن همراه</label>
                    </div>
                    <div class="col-md-4 mt-3">
                        <input class="form-control" name="word" id="searchWord" placeholder="جستجو">
                    </div>
                    <div class="col-md-4 mt-3">
                        <button class="btn btn-primary mr-2">جستجو</button>
                    </div>
                </div>
                @if (isset($message))
                    <div class="alert alert-info text-right">{{ $message }}</div>
                @endif
            </form>


            <table class="table mt-3">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نام و نام خانوادگی</th>
                        <th>کد ملی</th>
                        <th>شماره تلفن همراه</th>
                        <th>تعداد بار مانده</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach ($customers as $key => $customer)
                        <tr>
                            <td>
                                {{ ($customers->currentPage() - 1) * $customers->perPage() + ($key + 1) }}
                            </td>
                            <td>
                                {{ $customer->name }} {{ $customer->lastName }}
                                @if ($customer->status == 0)
                                    <span class="alert alert-warning p-1">غیرفعال</span>
                                @else
                                    <span class="alert alert-success p-1">فعال</span>
                                @endif
                            </td>
                            <td>{{ $customer->nationalCode }}</td>
                            <td>{{ $customer->mobileNumber }}</td>
                            <td>{{ $customer->freeLoads }}</td>
                            <td>
                                <a class="btn btn-sm btn-primary mb-1"
                                    href="{{ url('admin/customerLoads') }}/{{ $customer->id }}">
                                    نمایش بارهای مشتری
                                </a>

                                <button type="button" class="btn btn-success btn-sm mb-1" data-bs-toggle="modal"
                                    data-bs-target="#editCustomer_{{ $customer->id }}">
                                    ویرایش
                                </button>

                                <div id="editCustomer_{{ $customer->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form class="modal-content" method="post"
                                            action="{{ url('admin/updateCustomer') }}/{{ $customer->id }}">
                                            @csrf
                                            @method('patch')
                                            <div class="modal-header">
                                                <h4 class="modal-title">ویرایش اطلاعات صاحب بار</h4>
                                            </div>
                                            <div class="modal-body text-right">
                                                <div class="form-group">
                                                    <label>نام :</label>
                                                    <input type="text" value="{{ $customer->name }}" name="name"
                                                        class="form-control">
                                                </div>

                                                <div class="form-group">
                                                    <label>نام خانوادگی :</label>
                                                    <input type="text" value="{{ $customer->lastName }}" name="lastName"
                                                        class="form-control">
                                                </div>

                                                <div class="form-group">
                                                    <label>شماره همراه :</label>
                                                    <input type="text" value="{{ $customer->mobileNumber }}"
                                                        name="mobileNumber" class="form-control">
                                                </div>

                                            </div>
                                            <div class="modal-footer text-left">
                                                <button type="submit" class="btn btn-primary">ثبت اطلاعات جدید
                                                </button>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                    انصراف
                                                </button>
                                            </div>
                                        </form>

                                    </div>
                                </div>

                                @if ($customer->status == 0)
                                    <a class="btn btn-primary btn-sm"
                                        href="{{ url('admin/changeCustomerStatus') }}/{{ $customer->id }}">تغییر به
                                        فعال</a>
                                @else
                                    <a class="btn btn-warning btn-sm"
                                        href="{{ url('admin/changeCustomerStatus') }}/{{ $customer->id }}">تغییر به
                                        غیر فعال</a>
                                @endif

                                @if (auth()->user()->role == 'admin')
                                    <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                        data-bs-target="#removeCustomer_{{ $customer->id }}">حذف
                                    </button>

                                    <div id="removeCustomer_{{ $customer->id }}" class="modal fade" role="dialog">
                                        <div class="modal-dialog">

                                            <!-- Modal content-->
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h4 class="modal-title">حذف صاحب بار</h4>
                                                </div>
                                                <div class="modal-body">
                                                    <p>آیا مایل به حذف صاحب بار
                                                        <span class="text-primary"> {{ $customer->title }}</span>
                                                        هستید؟
                                                    </p>
                                                </div>
                                                <div class="modal-footer text-left">
                                                    <a class="btn btn-primary"
                                                        href="{{ url('admin/removeCustomer') }}/{{ $customer->id }}">حذف
                                                        صاحب بار</a>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">
                                                        انصراف
                                                    </button>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                @endif

                                @if (auth()->user()->role == 'admin' && (strlen($customer->ip) > 0 || $customer->ip != null))
                                    @if ($customer->blockedIp == false)
                                        <button type="button" class="btn btn-danger btn-sm mb-1" data-bs-toggle="modal"
                                            data-bs-target="#blockUserIp_{{ $customer->id }}">مسدود کردن IP
                                        </button>

                                        <div id="blockUserIp_{{ $customer->id }}" class="modal fade" role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">مسدود کردن IP</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>آیا مایل به مسدود کردن IP
                                                            <span class="text-primary"> {{ $customer->name }}
                                                                {{ $customer->lastName }}</span>
                                                            هستید؟
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <a class="btn btn-primary"
                                                            href="{{ url('admin/blockUserIp') }}/{{ $customer->id }}/{{ ROLE_CUSTOMER }}/{{ $customer->ip }}">
                                                            بله مسدود شود
                                                        </a>
                                                        <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
                                                            انصراف
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-success btn-sm mb-1" data-bs-toggle="modal"
                                            data-bs-target="#unBlockUserIp_{{ $customer->id }}">
                                            حذف از لیست Ipهای مسدود
                                        </button>

                                        <div id="unBlockUserIp_{{ $customer->id }}" class="modal fade"
                                            role="dialog">
                                            <div class="modal-dialog">

                                                <!-- Modal content-->
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">حذف از لیست Ipهای مسدود</h4>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>آیا مایل به حذف کردن IP
                                                            <span class="text-primary"> {{ $customer->name }}
                                                                {{ $customer->lastName }}</span>
                                                            از لیست مسدودها هستید؟
                                                        </p>
                                                    </div>
                                                    <div class="modal-footer text-left">
                                                        <a class="btn btn-primary"
                                                            href="{{ url('admin/unBlockUserIp') }}/{{ $customer->id }}/{{ ROLE_CUSTOMER }}">
                                                            بله از لیست حذف شود
                                                        </a>
                                                        <button type="button" class="btn btn-danger"
                                                            data-bs-dismiss="modal">
                                                            انصراف
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                {{ $customers }}
            </div>


        </div>
    </div>


@stop
