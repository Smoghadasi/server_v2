<html>
<head>
</head>

<body>
<form method="post" name="payForm" id="payForm" action="https://bpm.shaparak.ir/pgwchannel/startpay.mellat">
    <input type="hidden" name="RefId" value="{{ $RefId }}">
</form>
<script>
    const payForm = document.getElementById("payForm");
    payForm.submit();
</script>
</body>
</html>
