@include('include.header')

<style>
    .dash-prv-page {
        background: #eef3f9;
        min-height: 60vh;
        padding: 1.25rem 0 2.5rem;
    }
    .dash-prv-page .container {
        max-width: 76rem;
    }
    .dash-prv-page-bar {
        background: #fff;
        border-bottom: 1px solid #e3e8f0;
        padding: 0.7rem 0;
    }
    .dash-prv-page-bar-inner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .dash-prv-page-title {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1a2a4a;
    }
    .dash-prv-page-back {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        color: #035ab3;
        font-weight: 600;
        font-size: 0.88rem;
        text-decoration: none;
    }
    .dash-prv-page-back:hover,
    .dash-prv-page-back:focus-visible {
        color: #024a93;
        text-decoration: underline;
    }
    @include('user_login.partials.dashboard-application-preview-styles')
</style>

<div class="dash-prv-page-bar">
    <div class="container">
        <div class="dash-prv-page-bar-inner">
            <h1 class="dash-prv-page-title">
                <i class="fa fa-file-text-o" aria-hidden="true"></i>
                Application Preview
            </h1>
            <a href="{{ route('dashboard') }}" class="dash-prv-page-back">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>

<div class="dash-prv-page">
    <div class="container">
        @include('user_login.partials.dashboard-application-preview')
    </div>
</div>

<footer class="main-footer">
    @include('include.footer')
</footer>
