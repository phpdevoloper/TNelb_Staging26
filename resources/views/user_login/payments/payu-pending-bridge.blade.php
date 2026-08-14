<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment In Progress</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 560px; margin: 48px auto; padding: 0 16px; }
        .card { border: 1px solid #bfdbfe; background: #eff6ff; border-radius: 8px; padding: 24px; text-align: center; }
        h2 { color: #1d4ed8; margin-top: 0; }
        p { color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Payment In Progress</h2>
        <p id="pendingMsg">{{ $pendingMessage ?? 'Your payment is still being processed. Please check your dashboard shortly.' }}</p>
        @if(!empty($payment?->application_id))
            <p><strong>Application ID:</strong> {{ $payment->application_id }}</p>
        @endif
    </div>
    <script>
        (function () {
            var payload = @json($pendingPayload ?? []);
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'TNELB_PAYU_PENDING',
                        payload: payload
                    }, window.location.origin);
                    document.getElementById('pendingMsg').textContent =
                        'Payment is in progress. You can close this window and check your application page.';
                    setTimeout(function () { window.close(); }, 800);
                    return;
                }
            } catch (e) {}
        })();
    </script>
</body>
</html>
