<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>ایران ترابر</title>

    <!-- Bootstrap core CSS-->
    <link href="../../assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template-->
    <link href="../../assets/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">


    <link href="../../assets/vendor/bootstrap/css/bootstrap-rtl.min.css" rel="stylesheet">

</head>

<body>
    <div class="text-success m-4 text-center h4">
        <div class="mb-5">
            @if ($status)
                <img class="img-fuild" width="120" src="{{ asset('assets/img/ep--success-filled.png') }}"
                    alt="">
            @else
                <img class="img-fuild" width="120" src="{{ asset('assets/img/ix--error-filled.png') }}"
                    alt="">
            @endif
        </div>
        {{-- <hr> --}}
        <p>{{ $message }}.</p>
        <p class="text-dark">کد رهگیری: {{ $authority }}</p>
        <hr>
        برای دسترسی به بارها وارد برنامه شوید.
    </div>
</body>

</html>
