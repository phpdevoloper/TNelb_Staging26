<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 560px; margin: 48px auto; padding: 0 16px; }
        .card { border: 1px solid #f1c0c0; background: #fff6f6; border-radius: 8px; padding: 24px; }
        h2 { color: #b02a37; margin-top: 0; }
        a.btn { display: inline-block; margin-top: 16px; padding: 10px 16px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px; }
        .err { color: #842029; font-size: 14px; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Payment Not Confirmed</h2>
        <p><strong>Application ID:</strong> {{ optional($payment)->application_id ?? 'N/A' }}</p>
        <p><strong>Transaction ID:</strong> {{ optional($payment)->txnid ?? 'N/A' }}</p>
        <p><strong>Status:</strong> {{ optional($payment)->status ?? 'FAILED' }}</p>
        @if(!empty($errorMessage))
            <p class="err">{{ $errorMessage }}</p>
        @endif
        <p>Please login and try again, or contact support with your Application ID.</p>
        <a class="btn" href="{{ url('/login') }}">Go to Login</a>
    </div>
</body>
</html>
