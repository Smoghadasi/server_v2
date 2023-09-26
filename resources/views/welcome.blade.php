<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">

        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ایران ترابر</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">

        <link rel="stylesheet" type="text/css" href="css/style.css">

    </head>
    <body>
        <div class="flex-center position-ref full-height">
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/dashboard') }}">داشبورد</a>
                    @else
                        <a href="{{ route('login') }}">ورود</a>
                    @endauth
                </div>
            @endif

            <div class="content">
                <div class="title m-b-md">
                    ایران ترابر
                </div>

            </div>
        </div>
    </body>
</html>
