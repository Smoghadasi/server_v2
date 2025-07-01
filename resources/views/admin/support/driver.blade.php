@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            تماس های پشتیبانی
        </h5>
        <div class="card-body">
            <form method="get" action="{{ route('admin.support') }}" class="mt-3 mb-3 card card-body">
                <h5>جستجو :</h5>

                <div class="form-group">
                    <div class="col-md-12 row">
                        <input type="hidden" name="type" value="driver">
                        <div class="col-md-3">
                            <input class="form-control" type="text" id="fromDate" name="fromDate" placeholder="از تاریخ"
                                autocomplete="off" />
                        </div>
                        <div class="col-md-3">
                            <input class="form-control" type="text" name="toDate" id="toDate" placeholder="تا تاریخ"
                                autocomplete="off" />
                            <span id="span2"></span>
                        </div>
                    </div>
                    <button class="btn btn-primary m-2">جستجو</button>
                </div>
            </form>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>راننده</th>
                        {{-- <th>اپراتور</th> --}}
                        {{-- <th>موضوع</th>
                        <th>نتیجه</th> --}}
                        <th>تاریخ ثبت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody class="small text-right">
                    <?php $i = 1; ?>
                    @forelse ($supports as $key => $support)
                        <tr class="text-center">
                            <td>{{ ($supports->currentPage() - 1) * $supports->perPage() + ($key + 1) }}</td>
                            <td>
                                @if ($support->driver)
                                    <a href="{{ route('driver.detail', $support->driver_id) }}">
                                        {{ $support->driver ? $support->driver->name . ' ' . $support->driver->lastName . ' ( ' . $support->driver->mobileNumber . ' )' : '-' }}
                                    </a>
                                @else
                                    -
                                @endif
                            </td>

                            {{-- <td>
                                {{ $support->subject ?? '-' }}
                            </td>
                            <td>
                                {{ $support->result ?? '-' }}
                            </td> --}}
                            @php
                                $pieces = explode(' ', $support->created_at);
                            @endphp
                            <td>
                                {{ gregorianDateToPersian($support->created_at, '-', true) . ' ( ' . $pieces[1] . ' ) ' }}
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary mb-3 btn-sm text-nowrap"
                                    data-bs-toggle="modal" data-bs-target="#adminMessageForm_{{ $support->id }}">
                                    {{ $support->result != null || $support->subject != null ? 'مشاهده نتیجه' : 'ثبت نتیجه' }}
                                </button>
                                <div id="adminMessageForm_{{ $support->id }}" class="modal fade" role="dialog">
                                    <div class="modal-dialog">

                                        <!-- Modal content-->
                                        <form action="{{ route('admin.indexDriver.update', $support) }}" method="post"
                                            class="modal-content">
                                            @csrf
                                            @method('put')
                                            <div class="modal-header">
                                                <h4 class="modal-title">
                                                    {{ $support->driver->name . ' ' . $support->driver->lastName }}</h4>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group mb-2">
                                                    <select class="form-control form-select" name="subject" id="">
                                                        <option @if($support->subject == 'خرید اشتراک') selected @endif value="خرید اشتراک">خرید اشتراک</option>
                                                        <option @if($support->subject == 'تماس رایگان') selected @endif value="تماس رایگان">تماس رایگان</option>
                                                        <option @if($support->subject == 'احراز هویت') selected @endif value="احراز هویت">احراز هویت</option>
                                                        <option @if($support->subject == 'استعلام') selected @endif value="استعلام">استعلام</option>
                                                        <option @if($support->subject == 'شکایت') selected @endif value="شکایت">شکایت</option>
                                                        <option @if($support->subject == 'راهنمایی استفاده') selected @endif value="راهنمایی استفاده">راهنمایی استفاده</option>
                                                        <option @if($support->subject == 'ویرایش اطلاعات') selected @endif value="ویرایش اطلاعات">ویرایش اطلاعات</option>
                                                        <option @if($support->subject == 'سایر موارد') selected @endif value="سایر موارد">سایر موارد</option>
                                                    </select>
                                                </div>
                                                <div class="form-group">
                                                    <textarea class="form-control" name="result" id="result" rows="5" placeholder="پاسخ">{{ $support->result }}</textarea>
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
                    @empty
                        <tr class="text-center">
                            <td colspan="10">
                                دیتا مورد نظر یافت نشد
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="mt-3">
                {{ $supports }}
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script src="{{ asset('js/persianDatepicker.min.js') }}"></script>

    <script type="text/javascript">
        $("#fromDate, #toDate").persianDatepicker({
            formatDate: "YYYY/MM/DD",
            selectedBefore: !0
        });
    </script>
@endsection
