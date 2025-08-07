@extends('layouts.dashboard')

@section('content')
    <div class="card">
        <h5 class="card-header">
            <div class="row justify-content-between">
                <div class="col-4">
                    لیست بار های کپی شده
                </div>
            </div>
        </h5>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>عنوان بار</th>
                            <th>شماره موبایل</th>
                            <th>صاحب بار</th>
                            <th>ناوگان</th>
                            <th>مبدا</th>
                            <th>مقصد</th>
                            <th>تعداد</th>
                            <th>تاریخ ثبت</th>
                            {{-- <th>تاریخ حذف</th> --}}
                            <th>عملیات</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 0; ?>

                        @foreach ($loads as $load)
                            <tr>
                                <td>{{ ($loads->currentPage() - 1) * $loads->perPage() + ++$i }}</td>
                                <td>
                                    @php
                                        $pieces = explode(' ', $load->deleted_at);
                                    @endphp
                                    @if ($load->deleted_at != null)
                                        <i class="menu-icon tf-icons bx bx-trash text-danger" data-bs-toggle="tooltip"
                                            data-bs-placement="bottom"
                                            title="{{ $load->deleted_at ? gregorianDateToPersian($load->deleted_at, '-', true) . ' ' . $pieces[1] : '-' }}"></i>
                                    @endif
                                    @if ($load->isBot != null)
                                        <i class="menu-icon tf-icons bx bx-check text-success"></i>
                                    @endif

                                    {{ $load->title }}
                                </td>
                                <td>{{ $load->senderMobileNumber }}</td>
                                <td>
                                    -
                                    {{-- <a class="{{ $load->owner->isAccepted == 1 ? 'text-success' : '' }}"
                                        href="{{ route('owner.show', $load->owner->id) }}">
                                        {{ $load->owner->name }} {{ $load->owner->lastName }}
                                    </a> --}}
                                </td>
                                <td>
                                    @php
                                        $fleets = json_decode($load->fleets, true);
                                    @endphp
                                    @foreach ($fleets as $fleet)
                                        <span class="alert alert-primary p-1 m-1 small"
                                            style="line-height: 2rem">{{ $fleet['title'] }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $load->fromCity }}</td>
                                <td>{{ $load->toCity }}</td>
                                <td>
                                    <a href="{{ route('admin.driverVisitLoads', $load) }}">
                                        <span class="badge bg-primary">بازدید : {{ $load->driverVisitLoadCount }}</span>
                                    </a>

                                    <span>
                                        <a class="badge bg-danger" href="{{ route('load.searchLoadInquiry', $load->id) }}">
                                            درخواست: {{ $load->numOfInquiryDrivers }}
                                        </a>

                                    </span>
                                    <span>
                                        <a class="badge bg-success"
                                            href="{{ route('load.searchLoadDriverCall', $load->id) }}">
                                            تماس: {{ $load->numOfDriverCalls }}
                                        </a>
                                    </span>
                                    <span>
                                        <a class="badge bg-black" href="{{ route('admin.nearLoadDrivers', $load->id) }}">
                                            رانندگان نزدیک: {{ $load->numOfNearDriver }}
                                        </a>
                                    </span>
                                </td>

                                <td>{{ $load->date }} {{ $load->dateTime }}</td>
                                <textarea class="form-control message-box d-none">
                                    🚛 ناوگان:
                                    @foreach ($fleets as $fleet)
                                    {{ $fleet['title'] }}
                                    @endforeach

                                    🏠 مبدا :   {{ $load->fromCity }}

                                    🏘 مقصد :  {{ $load->toCity }}

                                    ✳ عنوان بار : {{ $load->title }}

                                    ⏱تاریخ :  {{ $load->date }}

                                    وضعیت  :  موجود

                                    📞 ‌  :   {{ $load->senderMobileNumber }}
                                    لینک پیوستن به گروه:
                                    @elambarkhavari
                                </textarea>

                                <td>
                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn btn-primary btn-sm copyBtn" type="button">کپی متن</button>

                                        </div>
                                        <div class="col-6">
                                            <form action="{{ route('copyLoad.update', $load) }}" method="POST">
                                                @csrf
                                                @method('put')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    حذف از لیست
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    {{-- <a class="btn btn-info btn-sm" href="{{ route('loadInfo', $load->id) }}">جزئیات</a> --}}

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $('.copyBtn').on('click', function() {
            const textarea = $('.message-box');
            const $btn = $(this); // دکمه‌ای که کلیک شده

            if (textarea.length === 0) {
                alert('عنصر پیدا نشد');
                return;
            }

            const text = textarea.val();
            var cleanedText = text
                .split('\n') // تبدیل متن به آرایه‌ای از خطوط
                .map(line => line.trim()) // trim هر خط
                .join('\n'); // دوباره ترکیب به یک رشته


            // if (!cleanedText) {
            //     alert('متن خالی است!');
            //     return;
            // }

            // کپی با Clipboard API
            navigator.clipboard.writeText(cleanedText)
                .then(() => {
                    // تغییر کلاس دکمه
                    $btn
                        .removeClass('btn-primary')
                        .addClass('btn-success')
                        .text('کپی شد!');

                    // برگشت به حالت اولیه بعد از چند ثانیه (اختیاری)
                    // setTimeout(() => {
                    //     $btn
                    //         .removeClass('btn-success')
                    //         .addClass('btn-primary')
                    //         .text('کپی');
                    // }, 3000);
                })
                .catch((res) => {
                    console.log(res);
                    alert('خطا در کپی کردن متن!');
                });

        });
    </script>
@endsection
