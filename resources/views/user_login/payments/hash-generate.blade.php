<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PayU Verify Hash</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f6f8; margin: 0; padding: 24px; }
        .box { max-width: 560px; margin: 0 auto; background: #fff; padding: 24px; border: 1px solid #ddd; border-radius: 6px; }
        h2 { margin: 0 0 16px; font-size: 18px; }
        label { display: block; margin: 10px 0 4px; font-size: 13px; }
        input { width: 100%; box-sizing: border-box; padding: 8px; font-size: 14px; }
        button { margin-top: 16px; padding: 8px 16px; cursor: pointer; }
        .hash { margin-top: 16px; padding: 12px; background: #f0f4f9; word-break: break-all; font-family: monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="box">
        <h2>PayU verify_payment Hash</h2>
        <form method="post" action="{{ route('payu.hash.generate') }}">
            @csrf
            <label>var1 (txnid)</label>
            <input type="text" name="var1" value="{{ $var1 ?? '' }}" required>
            <button type="submit">Generate Hash</button>
        </form>

        @if (!empty($hash))
            <div class="hash">{{ $hash }}</div>
        @endif
    </div>
</body>
</html>
