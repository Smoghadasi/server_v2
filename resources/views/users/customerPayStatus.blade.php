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

<div class="alert @if($status > 0) alert-success @else alert-danger @endif  m-4 text-center h4">
    {{ $message }}
    <p></p>
    برای ادامه اپلیکیشن را باز کنید
    <p></p>
    <a class="btn btn-primary mt-2"
       href="intent:#Intent;action=ir.iran_tarabar.user;category=android.intent.category.DEFAULT;category=android.intent.category.BROWSABLE;S.msg_from_browser=Launched%20from%20Browser;end">بازگشت
        به اپلیکیشن</a>
</div>

</body>

</html>
