@extends('layouts.dashboard_optimize')

@section('content')


    <div class="card">
        <h5 class="card-header">
            ثبت بار
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

        <div class="card-body row">

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
                function getUserStatusHint($user) {
                    if (Cache::has('user-is-online-' . $user->id)) {
                        if (Cache::has('user-is-active-' . $user->id)) {
                            return ['class' => 'text-primary', 'text' => 'اکتیو'];
                        }
                        return ['class' => 'text-success', 'text' => 'آنلاین'];
                    }
                    return ['class' => 'text-secondary', 'text' => 'آفلاین'];
                }
            @endphp

            <div class="col-lg-12 m-2 p-2 text-right bg-light">
                <div class="col-lg-12 mb-1">وضعیت :</div>
                @if (in_array('onlineUsers', auth()->user()->userAccess))
                    @foreach ($users as $user)
                        <span class="table-bordered border-info rounded bg-white p-1 m-1">
                            @php
                                $status = getUserStatusHint($user);
                            @endphp
                            <span class="{{ $status['class'] }}">
                                {{ $user->name }} {{ $user->lastName }}
                            </span>
                        </span>
                    @endforeach
                @else
                    <span class="table-bordered border-info rounded bg-white p-1 m-1">
                        @php
                            $status = getUserStatusHint(auth()->user());
                        @endphp
                        {{ auth()->user()->name }} {{ auth()->user()->lastName }}
                        <span class="{{ $status['class'] }}">
                            {{ $status['text'] }} ({{ auth()->user()->numOfTodayLoads }})
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
                        نمایش بار بعدی
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
                <textarea class="form-control" placeholder="ورود لیست بارها" name="cargo" rows="20">{{ $originalText }}</textarea>
            </form>

            <div class="col-lg-6" style="height: 100vh;overflow-y: auto;">
                <form method="POST" action="{{ url('admin/storeMultiCargo') }}/{{ $cargo->id }}"
                    enctype="multipart/form-data">
                    @csrf
                    @foreach ($cargoList as $key => $item)
                        @if (count($item['fleets']) == 0)
                            @continue
                        @endif
                        <div class="form-group row text-right alert alert-light border border-dark" style="color: #000000">
                            <input type="hidden" name="key[]" value="{{ $key }}">

                            <label class="col-lg-12 mb-2">عنوان :
                                <input type="text" class="form-control" name="title_{{ $key }}"
                                    placeholder="بدون عنوان">
                            </label>
                            @if (isset($item['originProvince']))
                                <input type="hidden" class="form-control" name="origin_{{ $key }}"
                                    value="{{ $item['origin'] }}">

                                <label class="col-lg-6 mb-2">مبدا :
                                    <select class="form-select" name="originState_{{ $key }}" id="">
                                        @foreach ($item['originProvince'] as $province)
                                            <option value="{{ $province->parent_id }}">
                                                {{ $item['origin'] }} - {{ $province->state }}
                                            </option>
                                        @endforeach
                                    </select>
                                </label>
                            @else
                                <label class="col-lg-6 mb-2">مبدا :
                                    <input type="text" class="form-control" name="origin_{{ $key }}"
                                        value="{{ $item['origin'] }}">
                                </label>
                            @endif

                            <input type="hidden" class="form-control" name="destination_{{ $key }}"
                                value="{{ $item['destination'] }}">

                            <label class="col-lg-6 mb-2">مقصد :
                                <select class="form-select" name="destinationState_{{ $key }}" id="">
                                    @foreach ($item['descProvinces'] as $province)
                                        <option value="{{ $province->parent_id }}">
                                            {{ $item['destination'] }} - {{ $province->state }}
                                        </option>
                                    @endforeach
                                </select>
                            </label>

                            <label class="col-lg-12 mb-2">شماره تلفن :
                                <input type="number"
                                    class="form-control @if ($item['mobileNumber'] == null) is-invalid @else is-valid @endif"
                                    name="mobileNumber_{{ $key }}" value="{{ $item['mobileNumber'] }}">
                            </label>

                            <div class="col-lg-12 row mb-2">

                                <label class="col-lg-6">قیمت :
                                    <input type="text" class="form-control"
                                        onkeyup="separate('freight_{{ $key }}'); priceType('priceType_{{ $key }}',this.value)"
                                        id="freight_{{ $key }}" name="freight_{{ $key }}"
                                        value="{{ $item['freight'] }}">
                                </label>
                                <label class="col-lg-6">نوع قیمت :
                                    <div class="col-lg-12">
                                        <label class="ml-3">
                                            <input checked type="radio" value="توافقی"
                                                name="priceType_{{ $key }}" />توافقی
                                        </label>

                                        <label class="ml-3">
                                            <input type="radio" value="به ازای هر تن"
                                                name="priceType_{{ $key }}" />به ازای
                                            هر تن
                                        </label>
                                        <label class="ml-3">
                                            <input type="radio" value="به صورت صافی"
                                                name="priceType_{{ $key }}" />به صورت
                                            صافی
                                        </label>
                                    </div>

                                </label>
                            </div>

                            <label class="col-lg-12 row mb-2">
                                <lable class="col-lg-12">ناوگان :</lable>
                                @foreach ($item['fleets'] as $fleet)
                                    <input type="text" class="form-control col-lg-4"
                                        name="fleets_{{ $key }}[]" value="{{ $fleet }}">
                                @endforeach
                            </label>
                            <label class="col-lg-12 row">توضیحات :
                                <textarea class="form-control" name="description_{{ $key }}" rows="4"></textarea>
                            </label>
                            @if (Auth::user()->role == 'admin' || Auth::id() == 29)
                                <label class="col-lg-12 row">الگو :
                                    <input class="form-control" name="pattern_{{ $key }}" type="number">
                                </label>
                            @endif
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
    </script>

@endsection
@section('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.5/mobile-detect.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            let md = new MobileDetect(window.navigator.userAgent);
            console.log(window.navigator.userAgent);
            let deviceType = md.mobile() ? "موبایل" : md.tablet() ? "تبلت" : "دسکتاپ";

            document.querySelectorAll(".device-info").forEach(function (element) {
                console.log(element);
                element.innerText = deviceType;
            });
        });
    </script>
@endsection
