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



            <form method="post" action="{{ route('processingUnit.update', $cargo) }}" class="col-lg-6"
                style="height: 100vh;overflow-y: auto;">
                @csrf

                <div class="col-lg-12">
                    <button class="btn btn-primary mb-2 float-right" type="submit">ذخـــیره</button>

                    <button type="button" class="btn btn-outline-success mb-2 float-right" id="cutBtn">
                       کات (Cut)
                     </button>
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
@endsection
