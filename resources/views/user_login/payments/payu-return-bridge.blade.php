<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completing payment...</title>
    <style>
        :root {
            --brand: #0d6efd;
            --text: #334155;
            --muted: #64748b;
            --bg: #f8fafc;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .wrap {
            text-align: center;
            padding: 32px 24px;
            max-width: 420px;
        }
        .spinner {
            width: 52px;
            height: 52px;
            margin: 0 auto 20px;
            border: 4px solid #dbeafe;
            border-top-color: var(--brand);
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        h1 {
            font-size: 1.25rem;
            margin: 0 0 8px;
            font-weight: 700;
        }
        p {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 0.95rem;
            line-height: 1.45;
        }
        a {
            color: var(--brand);
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="spinner" aria-hidden="true"></div>
        <h1>Payment successful</h1>
        <p id="bridgeMsg">Updating your application page...</p>
        <a id="continueLink" href="{{ $dashboardUrl }}">Click here if nothing happens</a>
    </div>
    <script>
        (function () {
            var payload = @json($successPayload ?? []);
            var dashboardUrl = @json($dashboardUrl);

            // Prefer notifying the original application tab (opener), then close this popup.
            try {
                if (window.opener && !window.opener.closed) {
                    window.opener.postMessage({
                        type: 'TNELB_PAYU_SUCCESS',
                        payload: payload
                    }, window.location.origin);
                    document.getElementById('bridgeMsg').textContent = 'You can close this window and continue on the application page.';
                    setTimeout(function () {
                        window.close();
                    }, 600);
                    return;
                }
            } catch (e) {}

            // Fallback: no opener — go to dashboard (same-site navigation keeps session)
            window.location.replace(dashboardUrl);
        })();
    </script>
</body>
</html>
