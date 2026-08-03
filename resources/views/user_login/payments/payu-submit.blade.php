<!DOCTYPE html>
<html>
<head>
    <title>Redirecting to PayU...</title>
</head>
<body onload="document.getElementById('payuForm').submit();">
    <p>Please wait, redirecting to payment gateway...</p>
    <form id="payuForm" method="post" action="{{ $url }}">
        @foreach($data as $key => $value)
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endforeach
    </form>
</body>
</html>