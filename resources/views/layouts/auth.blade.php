<!DOCTYPE html>
<html lang="en" dir="rtl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
    <meta name="author" content="Coderthemes">

    <!-- App Favicon -->
    <link rel="shortcut icon" href="/images/favicon.ico">

    <!-- App title -->
    <title>پنل مدیریت ایران ترابر - اهراز هویت</title>

    <!-- App CSS -->
    <link href="{{ asset('css/bootstrap-rtl.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/core.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/components.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/icons.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/pages.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/menu.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('css/responsive.css') }}" rel="stylesheet" type="text/css" />


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <script src="{{ asset('js/modernizr.min.js') }}"></script>

</head>

<body>

    <div class="account-pages"></div>
    <div class="clearfix"></div>
    <div class="wrapper-page">
        <div class="text-center">
            <a href="#" class="logo"><span>ایران <span>ترابر</span></span></a>
            <h5 class="text-muted m-t-0 font-600">بزرگترین سامانه هوشمند اعلام بار هوشمند</h5>
        </div>
        <div class="m-t-40 card-box">
            <div class="text-center">
                <h4 class="text-uppercase font-bold m-b-0">ورود به سیستم</h4>
            </div>

            <div class="panel-body">
                @yield('content')
            </div>
        </div>
        <!-- end card-box-->

    </div>
    <!-- end wrapper page -->



    <script>
        var resizefunc = [];
    </script>

    <!-- jQuery  -->
    <script src="/js/jquery.min.js"></script>
    <script src="/js/bootstrap-rtl.min.js"></script>
    <script src="/js/detect.js"></script>
    <script src="/js/fastclick.js"></script>
    <script src="/js/jquery.slimscroll.js"></script>
    <script src="/js/jquery.blockUI.js"></script>
    <script src="/js/waves.js"></script>
    <script src="/js/wow.min.js"></script>
    <script src="/js/jquery.nicescroll.js"></script>
    <script src="/js/jquery.scrollTo.min.js"></script>

    <!-- App js -->
    <script src="/js/jquery.core.js"></script>
    <script src="/js/jquery.app.js"></script>
    <script src="{{ asset('js/bootstrap-notify.min.js') }}"></script>
    @include('partials.flash')
    @yield('script')
    {{-- {!! NoCaptcha::renderJs() !!} --}}
    {!! GoogleReCaptchaV3::init() !!}
</body>

</html>
