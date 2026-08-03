<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 560px; margin: 48px auto; padding: 0 16px; }
        .card { border: 1px solid #cfe8cf; background: #f6fff6; border-radius: 8px; padding: 24px; }
        h2 { color: #157347; margin-top: 0; }
        a.btn { display: inline-block; margin-top: 16px; padding: 10px 16px; background: #0d6efd; color: #fff; text-decoration: none; border-radius: 4px; }
        .note { margin-top: 12px; font-size: 13px; color: #555; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Payment Successful</h2>
        <p><strong>Application ID:</strong> {{ $payment->application_id ?? 'N/A' }}</p>
        <p><strong>Transaction ID:</strong> {{ $payment->txnid ?? 'N/A' }}</p>
        <p><strong>Amount:</strong> Rs. {{ $payment->amount ?? '0.00' }}</p>
        <p><strong>Status:</strong> {{ $payment->status ?? 'SUCCESS' }}</p>
        <p class="note">If you were asked to sign in again, that is normal after returning from PayU. Please login and open your application.</p>
        <a class="btn" href="{{ url('/login') }}">Go to Login</a>
    </div>
</body>
</html>
