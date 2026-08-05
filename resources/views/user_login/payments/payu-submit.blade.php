@include('include.header')

<style>
    .payu-redirect-wrap {
        background: #f0f4f9;
        min-height: 50vh;
        padding: 32px 0 48px;
    }
    .payu-redirect-card {
        max-width: 640px;
        margin: 0 auto;
        background: #fff;
        border: 1px solid #e3e8f0;
        border-radius: 8px;
        padding: 36px 24px;
        text-align: center;
    }
    .payu-redirect-card h5 {
        margin: 0 0 12px;
        font-weight: 700;
        color: #0f172a;
    }
    .payu-redirect-card p {
        margin: 0 0 20px;
        color: #64748b;
    }
    .payu-spinner {
        width: 48px;
        height: 48px;
        margin: 0 auto 18px;
        border: 4px solid #dbeafe;
        border-top-color: #0d6efd;
        border-radius: 50%;
        animation: payu-spin 0.8s linear infinite;
    }
    @keyframes payu-spin { to { transform: rotate(360deg); } }
</style>

{{-- BREADCRUMB --}}
<div class="fs-breadcrumb-bar">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><span class="fa fa-home"></span> Dashboard</a></li>
            <li><a href="#"><span class="fa fa-credit-card"></span> Redirecting to Payment Gateway</a></li>
        </ul>
    </div>
</div>

{{-- PAGE BODY --}}
<div class="payu-redirect-wrap">
    <div class="container">
        <div class="payu-redirect-card">
            <div class="payu-spinner" aria-hidden="true"></div>
            <h5>Opening Payment Gateway...</h5>
            <p>Please wait. This window will continue to PayU. Keep it open until payment is finished.</p>
            <button type="button" class="btn btn-primary" id="payuManualRedirectBtn">
                Continue to Payment Gateway
            </button>
        </div>

        {{-- Submit inside this same (popup) window so PayU UI opens here --}}
        <form id="payuForm" method="post" action="{{ $url }}">
            @foreach($data as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
        </form>
    </div>
</div>

<script>
    (function () {
        var form = document.getElementById('payuForm');
        var btn = document.getElementById('payuManualRedirectBtn');
        if (!form) return;

        function goPayU() {
            form.submit();
        }

        if (btn) {
            btn.addEventListener('click', goPayU);
        }

        // This page is already in the new window opened by Pay Now
        setTimeout(goPayU, 800);
    })();
</script>

@include('include.footer')
