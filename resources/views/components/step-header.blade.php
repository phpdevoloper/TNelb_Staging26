{{--
    Wizard step header: round icon + "Step x of y" kicker + title + lead text.

    <x-step-header icon="mobile" kicker="Step 1 of 3" title="Mobile Number Verification"
                   lead="Enter your mobile number and verify with OTP to proceed." />

    Pass :inline="false" to stack the icon above a centred heading (step 3 style).
--}}
@props([
    'icon' => null,
    'kicker' => null,
    'title' => null,
    'lead' => null,
    'inline' => true,
    'headingId' => null,
])

<div @class(['tnelb-wiz-headrow' => $inline])>
    @if ($icon)
        <div class="tnelb-wiz-icon"><i class="fa fa-{{ $icon }}" aria-hidden="true"></i></div>
    @endif

    <div>
        @if ($kicker)
            <div class="tnelb-wiz-kicker">{{ $kicker }}</div>
        @endif

        @if ($title)
            <h3 @if ($headingId) id="{{ $headingId }}" @endif>{{ $title }}</h3>
        @endif

        @if ($lead)
            <p class="tnelb-wiz-lead">{{ $lead }}</p>
        @endif

        {{ $slot }}
    </div>
</div>
