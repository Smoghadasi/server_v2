@extends('layouts.dashboard')

@section('content')


    <div class="card">
        <h5 class="card-header">
            واحد پردازش
        </h5>

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
                            {{ $status['text'] }}
                        </span>
                    </span>
                @endif
            </div>

            <div class="col-lg-6" style="height: 100vh;overflow-y: auto;">
                @csrf

                <div class="col-lg-12">

                    <button type="button" class="btn btn-outline-success mb-2 float-right" id="copyBtn">
                        کپی (COPY)
                    </button>
                    <a href="{{ url('admin/removeCargoFromCargoList') }}/{{ $cargo->id }}"
                        class="btn btn-danger mb-2 float-right">
                        حذف
                    </a>
                </div>
                <span class="col-lg-12 text-right">
                    <div class="row">
                        <div class="col-md-4">
                            <span class="alert alert-info p-1">بار خام</span>
                        </div>
                        <div class="col-md-6">
                            <span class="alert alert-info p-1">
                                زمان ثبت :
                                {{ str_replace('-', '/', gregorianDateToPersian($cargo->created_at, '-', true)) }}
                                @php
                                    $date = explode(' ', $cargo->created_at);
                                    if (isset($date[1])) {
                                        echo 'زمان : ' . $date[1];
                                    }
                                @endphp
                            </span>
                        </div>
                    </div>
                </span>
                <textarea id="cargoText" class="form-control mb-2" placeholder="ورود لیست بارها" name="cargo" rows="20">{{ $cargo->cargo }}</textarea>
            </div>
            <form method="post" action="{{ route('processingUnit.update', $cargo) }}" class="col-lg-6"
                style="height: 100vh;overflow-y: auto;">
                @csrf

                <div class="col-lg-12">
                    <div class="row">

                        <div class="col-md-6">
                            <a href="{{ url('admin/removeCargoFromCargoList') }}/{{ $cargo->id }}"
                                class="btn btn-danger mb-2 float-right">
                                حذف
                            </a>
                        </div>
                        <div class="col-md-6 text-end">
                            {{-- <button class="btn btn-primary mb-2 float-right" type="submit">ارسال به ثبت بار</button> --}}
                            <button class="btn btn-primary mb-2 float-right submitBtn" type="submit">ذخیره
                                نهایی</button>
                        </div>
                    </div>

                </div>
                <span class="col-lg-12 text-right">
                    <div class="row">
                        <div class="col-md-6">
                            <span class="alert alert-info p-1">نتیجه پردازش</span>
                        </div>
                        {{-- <div class="col-md-4">
                            <span class="alert alert-info p-1">
                                زمان ثبت :
                                {{ str_replace('-', '/', gregorianDateToPersian($cargo->created_at, '-', true)) }}
                                @php
                                    $date = explode(' ', $cargo->created_at);
                                    if (isset($date[1])) {
                                        echo 'زمان : ' . $date[1];
                                    }
                                @endphp
                            </span>
                        </div> --}}
                        <div class="col-md-6" style="justify-items: left">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" value="1" name="automatic"
                                    id="automatic" checked>
                                <label class="form-check-label" for="automatic">
                                    ذخیره اتوماتیک
                                </label>
                            </div>
                        </div>
                    </div>
                </span>
                <textarea id="cargoText" class="form-control mb-2" placeholder="نتیجه پردازش توسط هوش منصوعی" name="cargo"
                    rows="20"></textarea>
                <div class="col-md-12 text-end">
                    {{-- <button class="btn btn-primary mb-2 float-right" type="submit">ارسال به ثبت بار</button> --}}
                    <button class="btn btn-primary mb-2 float-right submitBtn" type="submit">ذخیره نهایی</button>
                </div>
            </form>
        </div>
    </div>

@endsection
@section('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/mobile-detect/1.4.5/mobile-detect.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#automatic').on('change', function() {
                if ($(this).is(':checked')) {
                    $('.submitBtn').text('ذخیره نهایی');
                } else {
                    $('.submitBtn').text('ارسال به ثبت بار');
                }
            });
        });
        $('#copyBtn').on('click', function() {
            var $textarea = $('#cargoText');
            var text = $textarea.val();

            navigator.clipboard.writeText(text).then(function() {
                // alert('متن کپی شد ✅');
            }, function(err) {
                console.error('خطا در کپی کردن متن:', err);
            });
        });
    </script>
@endsection
