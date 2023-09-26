<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ایران ترابر</title>


    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/registerAndLogin.css') }}" rel="stylesheet">

    <link href="../../assets/css/select2.css" rel="stylesheet"/>

    <script src="../../assets/js/jquery-2.1.0.js"></script>

</head>
<body>
<div id="app">

    <h1 class="mt-5 mb-5 page-title">ایرانترابر، سامانه تامین بار</h1>

    <main>
        @yield('content')
    </main>
</div>

<script src="../../assets/js/select2.js"></script>
<script src="../../assets/js/fa.js"></script>
<script>
    $('#city').select2({
        dir:"rtl",
        language: "fa"
    });

</script>

</body>
</html>
