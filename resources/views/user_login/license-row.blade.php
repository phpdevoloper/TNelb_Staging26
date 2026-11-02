{{-- One credit-card style licence / certificate card. Expects: $licence, $codes (codes that have a colour), optional $flags (warning lines). --}}
@php
    $code = strtoupper(trim((string) ($licence->license_name ?? '')));
    $licName = $mstLicences->firstWhere('cert_licence_code', $code);
    $name = $licName->licence_name ?? ($code !== '' ? 'Form ' . $code : 'N/A');
    $formCode = strtoupper(trim((string) ($licName->form_code ?? $code)));
    $codeClass = in_array($code, $codes, true) ? 'lic-card--' . strtolower($code) : '';

    $today = \Carbon\Carbon::today();
    $from = $licence->valid_from ? \Carbon\Carbon::parse($licence->valid_from) : null;
    $toRaw = ($licence->renewal_expires_at ?? null) ?: $licence->valid_to;
    $to = $toRaw ? \Carbon\Carbon::parse($toRaw) : null;

    $flags = $flags ?? [];
    $isExpired = $to ? $to->lte($today) : false;
    $daysLeft = ($to && ! $isExpired) ? (int) ceil(($to->timestamp - $today->timestamp) / 86400) : 0;
    $isExpiring = ! $isExpired && $to && $daysLeft <= 90;
    $state = ($isExpired || $flags !== []) ? 'is-expired' : ($isExpiring ? 'is-expiring' : 'is-active');
    $statusLabel = $isExpired ? 'Expired' : ($flags !== [] ? 'Action needed' : ($isExpiring ? 'Expires soon' : 'Active'));

    $timeLeft = '';
    if ($to && ! $isExpired) {
        $iv = $today->diff($to);
        $timeLeft = $iv->y ? "{$iv->y}y {$iv->m}m left" : ($iv->m ? "{$iv->m}m {$iv->d}d left" : "{$iv->d} days left");
    }

    // Share of the validity period already used, drawn as the thin bar along the bottom
    $progress = null;
    if ($from && $to && $to->gt($from)) {
        $progress = (int) round(max(0, min(1, ($today->timestamp - $from->timestamp) / ($to->timestamp - $from->timestamp))) * 100);
    }
@endphp

<article class="lic-card {{ $codeClass }} {{ $state }}">
    <div class="lic-card__top">
        <div class="lic-card__name" title="{{ $name }}">{{ $name }}</div>
        <span class="lic-card__status">{{ $statusLabel }}</span>
    </div>

    <div class="lic-card__middle">
        {{-- <span class="lic-card__chip" aria-hidden="true"></span> --}}
        <span class="lic-card__number">{{ $licence->license_number ?? '— — — —' }}</span>
    </div>

    @foreach ($flags as $flag)
        <div class="lic-card__flag"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> <span>{{ $flag }}</span></div>
    @endforeach

    <div class="lic-card__bottom">
        <div class="lic-card__dates">
            <div class="lic-card__date">
                <span class="lic-card__label">Valid from</span>
                <span class="lic-card__value">{{ $from ? $from->format('d-m-Y') : 'N/A' }}</span>
            </div>
            <div class="lic-card__date">
                <span class="lic-card__label">Valid thru</span>
                <span class="lic-card__value">{{ $to ? $to->format('d-m-Y') : 'N/A' }}</span>
            </div>
        </div>
        <div class="lic-card__end">
            @if ($timeLeft !== '')
                <span class="lic-card__left">{{ $timeLeft }}</span>
            @endif
            <span class="lic-card__brand">Form {{ $formCode !== '' ? $formCode : 'N/A' }}</span>
        </div>
    </div>

    @if ($progress !== null)
        <span class="lic-card__bar" role="progressbar" aria-label="Validity used" aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $progress }}">
            <span style="width: {{ $progress }}%"></span>
        </span>
    @endif
</article>
