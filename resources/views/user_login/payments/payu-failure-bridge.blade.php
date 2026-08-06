<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 560px; margin: 48px auto; padding: 0 16px; }
        .card { border: 1px solid #f1c0c0; background: #fff6f6; border-radius: 8px; padding: 24px; text-align: center; }
        h2 { color: #b02a37; margin-top: 0; }
        p { color: #64748b; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Payment Not Completed</h2>
        <p id="failMsg">{{ $errorMessage ?? 'Payment failed or was cancelled. You can close this window and try again on the application page.' }}</p>
        @if(!empty($payment?->application_id))
            <p><strong>Application ID:</strong> {{ $payment->application_id }}</p>
        @endif
    </div>
    <script>
        (function () {
            var payload = @json($failurePayload ?? []);
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'TNELB_PAYU_FAILED',
                        payload: payload
                    }, window.location.origin);
                    document.getElementById('failMsg').textContent =
                        'Payment was not completed. You can close this window and try again on the application page.';
                    setTimeout(function () { window.close(); }, 800);
                    return;
                }
            } catch (e) {}
        })();
    </script>
</body>
</html>
