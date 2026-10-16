@include('include.header')

@php
    $formCode = strtoupper(trim((string) ($applicant_data->form_name ?? '')));
    $licenseCode = strtoupper(trim((string) ($license_name_data->cert_licence_code ?? $applicant_data->license_name ?? '')));
    $appliedFor = $formCode;
    if ($licenseCode !== '') {
        $appliedFor = trim($formCode . ' | License ' . $licenseCode);
    }

    $submittedRaw = $applicant_data->dt_submit ?? $applicant_data->created_at ?? null;
    $submittedOn = $submittedRaw
        ? \Carbon\Carbon::parse($submittedRaw)->format('d-m-Y h:i A')
        : '—';

    $businessAddress = trim(preg_replace('/\s+/', ' ', (string) ($applicant_data->business_address ?? '')) ?? '');
    $ownershipCode = strtolower(trim((string) ($applicant_data->application_ownershiptype ?? '')));
    $ownershipLabel = match ($ownershipCode) {
        'pr' => 'Proprietor',
        'pt' => 'Partnership',
        'pvt' => 'Private Limited (Pvt LTD)',
        'ltd' => 'Limited (LTD)',
        default => ($ownershipCode !== '' ? strtoupper($ownershipCode) : '—'),
    };

    $mobile = trim((string) ($applicantUser->mobile ?? ''));
    $email = trim((string) ($applicantUser->email ?? ''));

    $sortedOwners = collect($cl_ownership_table)->sortBy(function ($item) {
        return match ($item->ownership_type ?? '') {
            'pr' => 1,
            'pt' => 2,
            'dr' => 3,
            default => 4,
        };
    })->values();
@endphp

<main class="apd-page">
    <div class="apd-wrap">
        <div class="apd-heading">
            <div>
                <h1>Application Details</h1>
                <p>View your application details and submitted information.</p>
            </div>
            <a href="{{ route('dashboard') }}" class="apd-back">
                <i class="fa fa-arrow-left" aria-hidden="true"></i>
                Back to My Applications
            </a>
        </div>

        <section class="apd-summary" aria-label="Application summary">
            <article class="apd-summary-card">
                <span class="apd-ico apd-ico-green"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                <div>
                    <span class="apd-label">Application ID</span>
                    <strong>{{ $applicant_data->application_id }}</strong>
                </div>
            </article>
            <article class="apd-summary-card">
                <span class="apd-ico apd-ico-blue"><i class="fa fa-user" aria-hidden="true"></i></span>
                <div>
                    <span class="apd-label">Applicant Name</span>
                    <strong>{{ $applicant_data->applicant_name ?: '—' }}</strong>
                </div>
            </article>
            <article class="apd-summary-card">
                <span class="apd-ico apd-ico-purple"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                <div>
                    <span class="apd-label">Applied For</span>
                    <strong>{{ $appliedFor !== '' ? $appliedFor : '—' }}</strong>
                </div>
            </article>
            <article class="apd-summary-card">
                <span class="apd-ico apd-ico-orange"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                <div>
                    <span class="apd-label">Submitted On</span>
                    <strong>{{ $submittedOn }}</strong>
                </div>
            </article>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-user" aria-hidden="true"></i>
                Application Details
            </header>
            <div class="apd-panel-body apd-split">
                <div class="apd-box">
                    <header class="apd-box-hd">
                        <i class="fa fa-user" aria-hidden="true"></i>
                        Basic Details
                    </header>
                    <dl class="apd-basic">
                        <div>
                            <dt><span>1.</span> Name of the Applicant</dt>
                            <dd><span>:</span> {{ $applicant_data->applicant_name ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt><span>2.</span> Business address</dt>
                            <dd><span>:</span> {{ $businessAddress !== '' ? $businessAddress : '—' }}</dd>
                        </div>
                        <div>
                            <dt><span>3.</span> Ownership Type</dt>
                            <dd><span>:</span> {{ $ownershipLabel }}</dd>
                        </div>
                        <div>
                            <dt><span>4.</span> Mobile Number</dt>
                            <dd><span>:</span> {{ $mobile !== '' ? $mobile : '—' }}</dd>
                        </div>
                        <div>
                            <dt><span>5.</span> Email ID</dt>
                            <dd><span>:</span> {{ $email !== '' ? $email : '—' }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="apd-box">
                    <header class="apd-box-hd">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Ownership / Partners / Directors Details
                    </header>
                    <div class="apd-table-wrap">
                        <table class="apd-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Ownership<br>Type</th>
                                    <th>Name</th>
                                    <th>Father's /<br>Husband's Name</th>
                                    <th>D.O.B, Age and Proof</th>
                                    <th>Address</th>
                                    <th>Qualification</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sortedOwners as $owner)
                                    @php
                                        $ownerType = match ($owner->ownership_type ?? '') {
                                            'pr' => 'Proprietor',
                                            'pt' => 'Partner',
                                            default => 'Director',
                                        };
                                        $dob = '';
                                        if (!empty($owner->dob)) {
                                            try {
                                                $dob = \Carbon\Carbon::parse($owner->dob)->format('d-m-Y');
                                            } catch (\Throwable $e) {
                                                $dob = $owner->dob;
                                            }
                                        }
                                        $qualification = trim((string) ($owner->qualification ?? ''));
                                        $qualificationExtra = trim((string) ($owner->qualification_text ?? ''));
                                        if ($qualificationExtra !== '') {
                                            $qualification = trim($qualification . ($qualification !== '' ? ', ' : '') . $qualificationExtra);
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $ownerType }}</td>
                                        <td>{{ $owner->proprietor_name }}</td>
                                        <td>{{ $owner->fathers_name }}</td>
                                        <td>
                                            {{ $dob }}@if($dob !== '' && $owner->age !== null && $owner->age !== ''), {{ $owner->age }}@elseif($owner->age !== null && $owner->age !== ''){{ $owner->age }}@endif
                                            @if (!empty($owner->age_proof_url))
                                                <a href="{{ $owner->age_proof_url }}" class="apd-pdf" target="_blank" rel="noopener" title="Age proof">
                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td class="apd-address">{{ $owner->proprietor_address }}</td>
                                        <td>{{ $qualification !== '' ? $qualification : '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="apd-empty">No ownership details available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-pencil" aria-hidden="true"></i>
                Uploaded Documents
            </header>
            <div class="apd-docs">
                @foreach ($documentCards as $card)
                    <article class="apd-doc">
                        <h2>{{ $card['title'] }}</h2>
                        <i class="fa fa-file-pdf-o apd-doc-pdf" aria-hidden="true"></i>
                        @if (count($card['files']) === 1)
                            <a class="apd-view" href="{{ $card['files'][0]['url'] }}" target="_blank" rel="noopener">
                                <i class="fa fa-eye" aria-hidden="true"></i> View
                            </a>
                        @elseif (count($card['files']) > 1)
                            <button type="button" class="apd-view" data-toggle="modal" data-target="#apd-docs-{{ $card['key'] }}">
                                <i class="fa fa-eye" aria-hidden="true"></i> View
                            </button>
                        @else
                            <span class="apd-view is-empty" title="No document uploaded">
                                <i class="fa fa-eye" aria-hidden="true"></i> View
                            </span>
                        @endif
                    </article>
                @endforeach
            </div>
            <div class="apd-print-row">
                @if ($printUrl)
                    <a href="{{ $printUrl }}" class="apd-print" target="_blank" rel="noopener">
                        <i class="fa fa-print" aria-hidden="true"></i> Print Application
                    </a>
                @else
                    <button type="button" class="apd-print" onclick="window.print()">
                        <i class="fa fa-print" aria-hidden="true"></i> Print Application
                    </button>
                @endif
            </div>
        </section>
    </div>
</main>

@foreach ($documentCards as $card)
    @if (count($card['files']) > 1)
        <div class="modal fade" id="apd-docs-{{ $card['key'] }}" tabindex="-1" role="dialog" aria-labelledby="apd-docs-label-{{ $card['key'] }}" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="apd-docs-label-{{ $card['key'] }}">{{ $card['title'] }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <ul class="apd-doc-list">
                            @foreach ($card['files'] as $file)
                                <li>
                                    <a href="{{ $file['url'] }}" target="_blank" rel="noopener">
                                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                        {{ $file['label'] }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endforeach

<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal[id^="apd-docs-"]').forEach(function (modal) {
            document.body.appendChild(modal);
        });
    });
</script>

<style>
    .apd-page {
        background: #f4f7fb;
        padding: 18px 22px 36px;
        color: #1f2937;
        font-family: "Segoe UI", Arial, sans-serif;
    }

    .apd-wrap {
        max-width: 1180px;
        margin: 0 auto;
    }

    .apd-crumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 8px;
        margin: 0 0 10px;
        font-size: 13px;
        color: #6b7280;
    }

    .apd-crumb a {
        color: #6b7280 !important;
        text-decoration: none;
    }

    .apd-crumb a:hover {
        color: #1d4ed8 !important;
    }

    .apd-crumb strong {
        color: #111827;
        font-weight: 700;
    }

    .apd-heading {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 16px;
    }

    .apd-heading h1 {
        margin: 0;
        font-size: 26px;
        line-height: 1.2;
        font-weight: 700;
        color: #111827;
    }

    .apd-heading p {
        margin: 4px 0 0;
        color: #6b7280;
        font-size: 14px;
    }

    .apd-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        flex: 0 0 auto;
        background: #fff;
        color: #1f2937 !important;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none !important;
    }

    .apd-back:hover {
        background: #f8fafc;
        color: #1d4ed8 !important;
    }

    .apd-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 16px;
    }

    .apd-summary-card {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
        background: #fff;
        border: 1px solid #e6ebf2;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        padding: 14px 16px;
    }

    .apd-summary-card .apd-label {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.3;
    }

    .apd-summary-card strong {
        display: block;
        margin-top: 2px;
        color: #111827;
        font-size: 15px;
        font-weight: 700;
        word-break: break-word;
    }

    .apd-page .apd-ico {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        flex: 0 0 40px;
        line-height: 1;
        padding: 0;
    }

    .apd-page .apd-ico .fa {
        display: inline-block;
        width: auto;
        height: auto;
        margin: 0;
        padding: 0;
        font-family: FontAwesome !important;
        font-style: normal !important;
        font-weight: normal !important;
        font-size: 16px !important;
        line-height: 1 !important;
        text-align: center;
    }

    .apd-page .apd-ico-green { background: #e7f8ee; }
    .apd-page .apd-ico-green .fa { color: #16a34a !important; }
    .apd-page .apd-ico-blue { background: #e7f0ff; }
    .apd-page .apd-ico-blue .fa { color: #2563eb !important; }
    .apd-page .apd-ico-purple { background: #f3e8ff; }
    .apd-page .apd-ico-purple .fa { color: #7c3aed !important; }
    .apd-page .apd-ico-orange { background: #fff3e0; }
    .apd-page .apd-ico-orange .fa { color: #f59e0b !important; }

    .apd-page .apd-panel-hd .fa,
    .apd-page .apd-box-hd .fa,
    .apd-page .apd-back .fa,
    .apd-page .apd-view .fa,
    .apd-page .apd-print .fa,
    .apd-page .apd-pdf,
    .apd-page .apd-doc-pdf {
        font-family: FontAwesome !important;
        font-style: normal !important;
        font-weight: normal !important;
        line-height: 1 !important;
        display: inline-block;
        width: 1.1em;
        text-align: center;
        vertical-align: middle;
    }

    .apd-panel {
        background: #fff;
        border: 1px solid #e6ebf2;
        border-radius: 12px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
        margin-bottom: 16px;
        overflow: hidden;
    }

    .apd-panel-hd,
    .apd-box-hd {
        display: flex;
        align-items: center;
        gap: 8px;
        background: #e8f3fc;
        color: #1e3a5f;
        font-weight: 700;
        font-size: 15px;
    }

    .apd-panel-hd {
        padding: 12px 16px;
    }

    .apd-panel-body {
        padding: 14px;
    }

    .apd-split {
        display: grid;
        grid-template-columns: minmax(280px, 0.9fr) minmax(0, 1.35fr);
        gap: 14px;
        align-items: start;
    }

    .apd-box {
        border: 1px solid #e6ebf2;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        min-width: 0;
    }

    .apd-box-hd {
        padding: 8px 12px;
        font-size: 14px;
    }

    .apd-basic {
        margin: 0;
    }

    .apd-basic > div {
        display: grid;
        grid-template-columns: minmax(148px, 42%) 1fr;
        gap: 8px;
        align-items: start;
        padding: 8px 12px;
        border-top: 1px solid #eef2f6;
        font-size: 13.5px;
    }

    .apd-basic dt,
    .apd-basic dd {
        margin: 0;
        color: #1f2937;
        font-weight: 500;
    }

    .apd-basic dt span {
        display: inline-block;
        min-width: 18px;
    }

    .apd-basic dd {
        display: flex;
        gap: 8px;
    }

    .apd-basic dd > span {
        color: #374151;
    }

    .apd-table-wrap {
        overflow-x: auto;
    }

    .apd-table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
        font-size: 12px;
        color: #1f2937;
    }

    .apd-table th,
    .apd-table td {
        border: 1px solid #e5e7eb;
        padding: 8px 8px;
        vertical-align: top;
        background: #fff;
        font-weight: 500;
    }

    .apd-table th {
        background: #f8fafc;
        color: #374151;
        font-size: 12px;
        font-weight: 700;
        white-space: nowrap;
    }

    .apd-address {
        white-space: pre-line;
        min-width: 140px;
    }

    .apd-pdf {
        color: #dc2626 !important;
        margin-left: 4px;
        font-size: 14px;
    }

    .apd-empty {
        text-align: center;
        color: #6b7280;
    }

    .apd-docs {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 12px;
        padding: 16px;
    }

    .apd-doc {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 132px;
        border: 1px solid #e6ebf2;
        border-radius: 8px;
        background: #fff;
        padding: 14px 8px;
        text-align: center;
    }

    .apd-doc h2 {
        margin: 0;
        font-size: 13.5px;
        font-weight: 600;
        color: #1f2937;
    }

    .apd-doc-pdf {
        color: #ef4444;
        font-size: 28px;
        line-height: 1;
    }

    .apd-view {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border: 0;
        background: transparent;
        color: #2563eb !important;
        font-size: 14px;
        font-weight: 600;
        padding: 0;
        cursor: pointer;
        text-decoration: none !important;
    }

    .apd-view:hover {
        color: #1d4ed8 !important;
    }

    .apd-view.is-empty {
        opacity: 0.45;
        cursor: default;
    }

    .apd-print-row {
        padding: 0 16px 16px;
    }

    .apd-print {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #1d4ed8;
        color: #fff !important;
        border: 0;
        border-radius: 6px;
        padding: 8px 14px;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none !important;
        cursor: pointer;
    }

    .apd-print:hover {
        background: #1e40af;
        color: #fff !important;
    }

    .apd-doc-list {
        list-style: none;
        margin: 0;
        padding: 0;
    }

    .apd-doc-list li + li {
        margin-top: 8px;
    }

    .apd-doc-list a {
        color: #1d4ed8 !important;
        font-weight: 600;
    }

    .apd-doc-list .fa {
        color: #ef4444;
        margin-right: 6px;
    }

    @media (max-width: 991px) {
        .apd-summary,
        .apd-split,
        .apd-docs {
            grid-template-columns: 1fr 1fr;
        }
    }

    @media (max-width: 640px) {
        .apd-page {
            padding: 14px 12px 28px;
        }

        .apd-heading,
        .apd-summary,
        .apd-split,
        .apd-docs,
        .apd-basic > div {
            grid-template-columns: 1fr;
            display: grid;
        }

        .apd-heading {
            display: flex;
            flex-direction: column;
        }
    }
</style>

@include('include.footer')
