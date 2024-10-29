@extends('layouts.auth')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form id="formid" class="form-horizontal m-t-20" method="POST" action="#" aria-label="{{ __('ورود') }}">
        @csrf
        <div class="input">
            <div class="form-group ">
                <div class="col-xs-12">
                    <input class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" type="text" name="email"
                        value="{{ old('email') }}" required="" placeholder="نام کاربری">
                    @if ($errors->has('email'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <div class="col-xs-12">
                    <input class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" type="password"
                        name="password" required="" placeholder="پسورد">
                    @if ($errors->has('password'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group ">
                <div class="col-xs-12">
                    <input class="form-control{{ $errors->has('captcha') ? ' is-invalid' : '' }}" type="text"
                        name="captcha" id="captcha" required="" placeholder="کد تایید">
                    @if ($errors->has('captcha'))
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $errors->first('captcha') }}</strong>
                        </span>
                    @endif
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group refereshrecapcha">
                                {!! captcha_img('math') !!}
                            </div>
                        </div>
                        <div class="col-6">
                            <button type="button" id="refreshCaptcha" class="btn btn-danger btn-sm">تازه‌سازی</button>
                        </div>
                    </div>

                </div>

            </div>
            {{-- {!! NoCaptcha::display() !!} --}}

        </div>

        <div class="form-group">
            <div class="col-xs-12">
                <input class="form-control timer" name="code" type="text" placeholder="کد تایید خود را وارد کنید">
            </div>
        </div>
        <div class="form-group">
            <div class="col-xs-12">
                <span class="btn btn-warning btn-block timer" id="timer">1:25</span>
            </div>
        </div>



        <div class="form-group text-center m-t-30">
            <div class="col-xs-12">
                <button id="submit" class="btn btn-custom btn-bordred btn-block waves-effect waves-light"
                    type="button">ادامه</button>
                <button id="submit_2" class="btn btn-custom btn-bordred btn-block waves-effect waves-light"
                    type="button">ثبت</button>

            </div>
        </div>
        <div id="login"></div>

        <div class="form-group m-t-30 m-b-0">
            <div class="col-sm-12">
                <a class="btn btn-link" href="{{ route('password.request') }}"><i class="fa fa-lock m-r-5"></i> آیا رمز خود
                    را فراموش کرده
                    اید؟</a>
            </div>
        </div>
        {!! GoogleReCaptchaV3::render(['login' => 'login']) !!}

    </form>
@endsection
@section('script')
    <script>
        $(document).ready(function() {

            $('#refreshCaptcha').click(function() {
                $.ajax({
                    url: "/refereshcapcha",
                    type: 'get',
                    dataType: 'html',
                    success: function(json) {
                        $('.refereshrecapcha').html(json);
                    },
                    error: function(data) {
                        alert('Try Again.');
                    }
                });
            });

            $('#formid').on('keyup keypress', function(e) {
                var keyCode = e.keyCode || e.which;
                if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(".timer").hide();
            $("#submit_2").hide();

            const counterdown = () => {

                $(".timer").show();
                $("#submit").hide();
                $("#submit_2").show();
                $(".input").hide();
                document.getElementById('timer').innerHTML =
                    02 + ":" + 00;
                startTimer();


                function startTimer() {
                    var presentTime = document.getElementById('timer').innerHTML;
                    var timeArray = presentTime.split(/[:]+/);
                    var m = timeArray[0];
                    var s = checkSecond((timeArray[1] - 1));
                    if (s == 59) {
                        m = m - 1
                    }
                    if (m < 0) {
                        return
                    }

                    document.getElementById('timer').innerHTML =
                        m + ":" + s;
                    setTimeout(startTimer, 1000);

                }

                function checkSecond(sec) {
                    if (sec < 10 && sec >= 0) {
                        sec = "0" + sec
                    }; // add zero in front of numbers < 10
                    if (sec < 0) {
                        sec = "59"
                    };
                    return sec;
                }
            }
            var mobile = 0;
            $('#submit').click(function(e) {
                e.preventDefault();
                var email = $("input[name=email]").val();
                var password = $("input[name=password]").val();
                var captcha = $("input[name=captcha]").val();
                $("#submit").attr("disabled", true);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('check.user') }}",
                    data: {
                        email: email,
                        password: password,
                        captcha: captcha,
                    },
                    success: function(data) {
                        if (data.status == 422) {
                            alert(data.response);
                            location.reload();
                        }
                        if (data.status == 403) {
                            alert(data.response);
                            location.reload();
                        }
                        if (data.status == 200) {
                            mobile = data.response;
                            counterdown();
                        }
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert('لطفا ورود را به درستی وارد کنید');
                        location.reload();
                    }
                });

            });

            $('#submit_2').click(function(e) {
                e.preventDefault();
                var code = $("input[name=code]").val();
                var password = $("input[name=password]").val();
                $("#submit_2").attr("disabled", true);
                $.ajax({
                    type: 'POST',
                    url: "{{ route('checkActivationCode') }}",
                    data: {
                        code: code,
                        mobileNumber: mobile,
                        password: password
                    },
                    success: function(data) {
                        if (data.status == 400) {
                            $("#submit_2").attr("disabled", false);
                            alert(data.response);
                        }
                        if (data.status == 200) {
                            $("#submit_2").attr("disabled", false);
                            window.location.href = "/dashboard";
                        }
                    }
                });
            });


            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


        });
    </script>
@endsection
