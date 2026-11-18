<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Payment cannot start</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6fb; margin: 0; padding: 32px 16px; color: #1f2d4d; }
        .box { max-width: 520px; margin: 0 auto; background: #fff; border: 1px solid #d9e0ef; border-radius: 8px; padding: 24px; }
        h1 { font-size: 1.15rem; margin: 0 0 12px; color: #b3261e; }
        p { line-height: 1.5; margin: 0 0 16px; }
        button { background: #0b5cab; color: #fff; border: 0; border-radius: 4px; padding: 8px 18px; cursor: pointer; }
    </style>
</head>
<body>
    <div class="box">
        <h1>Payment cannot be started</h1>
        <p>{{ $message }}</p>
        <p>Please close this window, correct the application and try again. You have not been charged.</p>
        <button type="button" onclick="window.close()">Close window</button>
    </div>
</body>
</html>
