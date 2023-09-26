@extends('layouts.auth')

@section('content')
    <form class="form-horizontal m-t-20" method="POST" action="{{ route('user.resetPassword') }}">
        @csrf
        <div class="form-group">
            <p class="text-muted m-t-10">
                شماره موبایل خود را وارد کنید تا به صفحه مدیدرتی خود دسترسی داشته باشید.
            </p>
            <div class="input-group m-t-30">
                <input type="text" name="mobile" class="form-control input" placeholder="شماره موبایل خود را وارد کنید..."
                    required="">
                <input type="text" name="code" class="form-control timer" placeholder="کد اعتبار سنجی..."
                    required="">
                <span class="input-group-btn">

                    <button id="submit_2" class="btn btn-pink w-sm waves-effect waves-light">
                        ثبت
                    </button>
                    <button id="submit" class="btn btn-pink w-sm waves-effect waves-light">
                        ارسال
                    </button>
                    <span class="btn btn-warning  w-sm waves-effect waves-light timer" disabled id="timer">1:25</span>
                </span>
            </div>
            <div class="login_submit">
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="password" name="password" class="form-control" placeholder="رمز عبور..." required="">
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input type="password" name="config_password" class="form-control" placeholder="تکرار رمز عبور ..."
                            required="">
                    </div>
                </div>
                <div class="form-group text-center m-t-30">
                    <div class="col-xs-12">
                        <button type="submit" class="btn btn-pink w-sm waves-effect waves-light login_submit">
                            ذخیره
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </form>
@endsection
@section('script')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(".timer").hide();
            $("#submit_2").hide();
            $(".login_submit").hide();

            const counterdown = () => {
                $(".timer").show(1000);
                $("#submit").hide(1000);
                $("#submit_2").show(1000);
                $(".input").hide(1000);
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
            var sms = 0;

            $('#submit').click(function(e) {
                e.preventDefault();
                var mobile = $("input[name=mobile]").val();
                // var password = $("input[name=password]").val();
                $("#submit").attr("disabled", true);

                $.ajax({
                    type: 'POST',

                    url: "{{ route('check.mobile') }}",
                    data: {
                        mobile: mobile,
                    },
                    success: function(data) {
                        if (data.code == 422) {
                            $("#submit").attr("disabled", false);
                            alert(data.success);
                        } else {
                            if (data.code == 200) {
                                // console.log(data);
                                counterdown();
                                sms = data.sms;
                            } else {
                                $("#submit").attr("disabled", false);
                                alert(data.success)
                            }
                        }
                    }
                });

            });

            $('#submit_2').click(function(e) {
                e.preventDefault();
                var code = $("input[name=code]").val();

                if (sms == code) {
                    // $("#login_submit").show();
                    $(".login_submit").show();
                    $(".timer").hide();
                    $("#submit_2").hide();
                } else {
                    alert('کد ارسال شده اشتباه است')
                }
            });


            var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');


        });
    </script>
@endsection
