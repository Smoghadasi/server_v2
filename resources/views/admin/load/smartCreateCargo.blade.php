@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            ثبت بار پلاس
            -
            تعداد بار در صف : {{ $countOfCargos }}
        </h5>
        @if (auth()->user()->id == 25)
            <div class="m-3">
                <a class="btn btn-warning btn-sm" href="{{ route('delete.duplicate') }}">
                    <i class="fas fa-angle-right"></i>
                    حذف بار های تکراری
                </a>
            </div>
        @endif

        @php
                /**
                 * بررسی وضعیت کاربر و برگرداندن کلاس CSS و متن وضعیت متناسب.
                 *
                 * اگر کاربر آنلاین باشد:
                 *   - اگر اکتیو باشد: متن "اکتیو" با کلاس text-primary
                 *   - در غیر این صورت: متن "آنلاین" با کلاس text-success
                 * در غیر این صورت (آفلاین): متن "آفلاین" با کلاس text-secondary
                 *
                 * @param  \App\Models\User  $user
                 * @return array
                 */
                function getUserStatusHint($user)
                {
                    if (Cache::has('user-is-online-' . $user->id)) {
                        if (Cache::has('user-is-active-' . $user->id)) {
                            return ['class' => 'text-primary', 'text' => 'اکتیو'];
                        }
                        return ['class' => 'text-success', 'text' => 'آنلاین'];
                    }
                    return ['class' => 'text-secondary', 'text' => 'آفلاین'];
                }
            @endphp

        <div class="card-body row">
            <div class="col-lg-12 m-2 p-2 text-right bg-light">
                <div class="col-lg-12 mb-1">وضعیت :</div>
                @if (in_array('onlineUsers', auth()->user()->userAccess))
                    @foreach ($users as $user)
                        <span class="table-bordered border-info rounded bg-white p-1 m-1" data-bs-toggle="tooltip"
                            data-bs-placement="top" data-bs-title="{{ $user->mobileNumber }}">
                            {{-- @if (Cache::has('user-is-online-' . $user->id)) --}}
                            @if (Cache::has('user-is-active-' . $user->id))
                                <span class="text-primary">{{ $user->name }} {{ $user->lastName }}</span>
                            @else
                                <span class="text-success">{{ $user->name }} {{ $user->lastName }}</span>
                            @endif
                            @switch($user->device)
                                @case('Mobile')
                                    @if ($user->id == 29)
                                        <i class="bx bx-desktop text-black fs-5"></i>
                                    @else
                                        <i class="bx bx-mobile text-black fs-5"></i>
                                    @endif
                                @break

                                @case('Desktop')
                                    <i class="bx bx-desktop text-black fs-5"></i>
                                @break
                            @endswitch
                        </span>
                    @endforeach
                @else
                    <span class="table-bordered border-info rounded bg-white p-1 m-1">
                        @php
                            $status = getUserStatusHint(auth()->user());
                        @endphp
                        {{ auth()->user()->name }} {{ auth()->user()->lastName }}
                        <span class="{{ $status['class'] }}">
                            {{ $status['text'] }}
                        </span>
                    </span>
                @endif
            </div>



            <form method="post" action="{{ url('admin/updateCargoInfo') }}/{{ $cargo->id }}" class="col-lg-6"
                style="height: 100vh;overflow-y: auto;">
                @csrf

                <div class="col-lg-12">
                    <button class="btn btn-primary mb-2 float-right" type="submit">ثبت اطلاعات ویرایش شده</button>
                    <a href="{{ url('admin/removeCargoFromCargoList') }}/{{ $cargo->id }}"
                        class="btn btn-danger mb-2 float-right">
                        حذف
                    </a>
                    <a href="{{ route('updateCargoTime', ['cargo' => $cargo]) }}"
                        class="btn btn-outline-secondary mb-2 float-right">
                        ارسال به انتها
                    </a>

                    <a class="btn btn-outline-success mb-2 float-right" href="{{ route('processingUnit', $cargo) }}">
                        ارسال به واحد پردازش

                    </a>
                </div>
                <span class="col-lg-12 text-right">
                    <span class="alert alert-info p-1">
                        زمان ثبت : {{ str_replace('-', '/', gregorianDateToPersian($cargo->created_at, '-', true)) }}
                        @php
                            $date = explode(' ', $cargo->created_at);
                            if (isset($date[1])) {
                                echo 'زمان : ' . $date[1];
                            }
                        @endphp
                    </span>
                </span>
                <textarea id="cargoText" class="form-control mb-2" placeholder="ورود لیست بارها" name="cargo" rows="20">{{ $cargo->cargo }}</textarea>
            </form>

            <div class="col-lg-6" style="height: 100vh; overflow-y: auto;">
                <form method="POST" action="{{ url('admin/storeMultiCargoSmart') }}/{{ $cargo->id }}"
                    enctype="multipart/form-data">
                    @csrf
                    @foreach ($uniqueResults as $key => $item)
                        <div class="form-group row text-right alert alert-light border border-dark" style="color: #000000">
                            <input type="hidden" name="key[]" value="{{ $key }}">

                            <label class="col-lg-12 mb-2 text-end">
                                <span class="text-danger btn-remove-item" style="font-size: 30px; cursor:pointer;"
                                    data-key="{{ $key }}">X</span>
                            </label>


                            {{-- عنوان --}}
                            <label class="col-lg-12 mb-2">عنوان :
                                <input type="text" class="form-control" name="title_{{ $key }}"
                                    value="{{ $item['title'] ?? '' }}" placeholder="بدون عنوان">
                            </label>

                            <input type="hidden" class="form-control" name="origin_{{ $key }}"
                                value="{{ $item['origin'] }}">

                            <label class="col-lg-6 mb-2">مبدا :
                                <select class="form-select" name="originState_{{ $key }}" required id="">
                                    @foreach ($item['origins'] as $province)
                                        <option value="{{ $province->parent_id }}">
                                            {{ $item['origin'] }} - {{ $province->state }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            {{-- مبدا --}}
                            {{-- <label class="col-lg-6 mb-2">مبدا :
                                <input type="text" class="form-control" name="origin_{{ $key }}"
                                    value="{{ $item['origin'] ?? '' }}">
                            </label>
                            <input type="hidden" name="originState_{{ $key }}"
                                value="{{ $item['origin_id'] ?? '' }}"> --}}

                            {{-- مقصد --}}
                            <input type="hidden" class="form-control" name="destination_{{ $key }}"
                                value="{{ $item['destination'] }}">

                            <label class="col-lg-6 mb-2">مقصد :
                                <select class="form-select" name="destinationState_{{ $key }}" required
                                    id="">
                                    @foreach ($item['destinations'] as $province)
                                        <option value="{{ $province->parent_id }}">
                                            {{ $item['destination'] }} - {{ $province->state }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            {{-- شماره تلفن --}}
                            <label class="col-lg-12 mb-2">شماره تلفن :
                                <input type="text" class="form-control" name="mobileNumber_{{ $key }}"
                                    value="{{ $item['phoneNumber'] ?? '' }}">
                            </label>

                            {{-- قیمت --}}
                            <div class="col-lg-12 row mb-2">
                                <label class="col-lg-6">قیمت :
                                    <input type="text" class="form-control"
                                        onkeyup="separate('freight_{{ $key }}'); priceType('priceType_{{ $key }}',this.value)"
                                        id="freight_{{ $key }}" name="freight_{{ $key }}"
                                        value="{{ $item['price'] ?? '' }}">
                                </label>
                                <label class="col-lg-6">نوع قیمت :
                                    <div class="col-lg-12">
                                        <label class="ml-3">
                                            <input checked type="radio" value="توافقی"
                                                name="priceType_{{ $key }}" />توافقی
                                        </label>
                                        <label class="ml-3">
                                            <input type="radio" value="به ازای هر تن"
                                                name="priceType_{{ $key }}" />به ازای هر تن
                                        </label>
                                        <label class="ml-3">
                                            <input type="radio" value="به صورت صافی"
                                                name="priceType_{{ $key }}" />به صورت صافی
                                        </label>
                                    </div>
                                </label>
                            </div>

                            {{-- ناوگان --}}
                            <input type="hidden" class="form-control" name="fleetId_{{ $key }}"
                                value="{{ $item['fleet_id'] ?? '' }}">
                            <label class="col-lg-12 mb-2">ناوگان :
                                <input required type="text" class="form-control" name="fleets_{{ $key }}"
                                    value="{{ $item['fleet'] ?? '' }}">
                            </label>

                            {{-- توضیحات --}}
                            <label class="col-lg-12 row">توضیحات :
                                <textarea class="form-control" name="description_{{ $key }}" rows="4">{{ $item['description'] ?? '' }}</textarea>
                            </label>

                            {{-- متن خام --}}
                            {{-- <label class="col-lg-12 row">متن اصلی پیام :
                                <textarea class="form-control" name="raw_{{ $key }}" rows="4" readonly>{{ $item['raw'] ?? '' }}</textarea>
                            </label> --}}

                        </div>
                    @endforeach

                    <div class="row form-group row mb-0">
                        <div class="col-md-12 offset-md-4">
                            <button type="submit" class="btn btn-primary">
                                ارسال برای ذخیره
                            </button>
                        </div>
                    </div>
                </form>
            </div>


        </div>
    </div>

@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.5/mobile-detect.min.js"></script>

    <script>
        $('#cutBtn').on('click', function() {
            var $textarea = $('#cargoText');
            var text = $textarea.val();

            navigator.clipboard.writeText(text).then(function() {
                $textarea.val(''); // پاک کردن متن بعد از کپی
                // alert('متن کات شد ✂️');
            }, function(err) {
                console.error(err);
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.btn-remove-item').click(function() {
                var key = $(this).data('key');
                $('input[name="key[]"][value="' + key + '"]').closest('.form-group').remove();
            });
        });
    </script>

    <script>
        function separate(freight) {
            document.getElementById(freight).value = document.getElementById(freight).value.replace(/\D/g, "")
                .replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function priceType(pt, value) {
            value = value.replace(",", "");
            if (value !== "0" && value.length > 0)
                document.getElementById(pt).value = "به صورت صافی";
            else
                document.getElementById(pt).value = "توافقی";
        }
        document.addEventListener("DOMContentLoaded", function() {
            let md = new MobileDetect(window.navigator.userAgent);
            console.log(window.navigator.userAgent);
            let deviceType = md.mobile() ? "موبایل" : md.tablet() ? "تبلت" : "دسکتاپ";

            document.querySelectorAll(".device-info").forEach(function(element) {
                console.log(element);
                element.innerText = deviceType;
            });
        });
    </script>
@endsection
