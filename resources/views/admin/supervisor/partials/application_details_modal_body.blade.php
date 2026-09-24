@php
    $applTypeCode = strtoupper((string) ($applicant->appl_type ?? ''));
    $applTypeLabel = match ($applTypeCode) {
        'N' => 'New',
        'R' => 'Renewal',
        'D' => 'Digitization',
        'A' => 'Alteration',
        default => 'N/A',
    };
    $appliedOnRaw = $applicant->submitted_date ?? $applicant->created_at ?? $applicant->dt_submit ?? null;
    $formLabel = trim((string) ($applicant->form_name ?? ''));
    $isAlterationApp = $applTypeCode === 'A';
    $parentForAlter = $parentApplicantForAlter ?? null;
    $previousApplicantName = $parentForAlter
        ? trim((string) ($parentForAlter->applicant_name ?? $parentForAlter->applicants_name ?? ''))
        : '';
    $nameAltered = $isAlterationApp && $previousApplicantName !== ''
        && trim((string) ($applicant->applicant_name ?? '')) !== $previousApplicantName;
    $hasAlteredWork = $isAlterationApp && ($workExperience ?? collect())->contains(function ($row) {
        return !empty($row->is_alteration_new);
    });
    $hasAlterationProofs = $isAlterationApp && ($alterationProofs ?? collect())->isNotEmpty();
    $nameProofDoc = ($alterationProofs ?? collect())->first(function ($proof) {
        return ($proof->document_type ?? '') === 'name_proof';
    });
    $addressProofDoc = ($alterationProofs ?? collect())->first(function ($proof) {
        return ($proof->document_type ?? '') === 'address_proof';
    });
    $resolveAlterProofUrl = function ($proof) {
        if (!$proof) {
            return null;
        }
        if (!empty($proof->url)) {
            return $proof->url;
        }
        $storedPath = trim((string) ($proof->proof_doc ?? ''));

        return $storedPath !== '' ? competency_document_path_url($storedPath) : null;
    };
    $nameProofUrl = $resolveAlterProofUrl($nameProofDoc);
    $addressProofUrl = $resolveAlterProofUrl($addressProofDoc);
    $parentAddressValue = $parentForAlter
        ? trim((string) ($parentForAlter->applicants_address ?? $parentForAlter->applicant_address ?? ''))
        : '';
    $addressAltered = $isAlterationApp && $parentForAlter
        && trim((string) ($applicant->applicants_address ?? $applicant->applicant_address ?? '')) !== $parentAddressValue;

    $photoPath = !empty($uploadedPhoto?->upload_path) ? $uploadedPhoto->upload_path : null;
    $signPath = !empty($uploadedSign?->uploaded_doc) ? $uploadedSign->uploaded_doc : null;
    $photoUrl = !empty($uploadedPhoto?->media_url)
        ? $uploadedPhoto->media_url
        : ($photoPath ? competency_media_url($photoPath) : null);
    $signUrl = !empty($uploadedSign?->media_url)
        ? $uploadedSign->media_url
        : ($signPath ? competency_media_url($signPath) : null);

    $decryptedAadhaar = displayProofNumber($applicant->aadhaar ?? '');
    $decryptedPan = displayProofNumber($applicant->pancard ?? '');
    $maskedAadhaar = strlen($decryptedAadhaar) === 12
        ? str_repeat('X', 8) . substr($decryptedAadhaar, -4)
        : ($decryptedAadhaar !== '' ? 'Invalid Aadhaar' : '—');
    $maskedPan = strlen($decryptedPan) === 10
        ? str_repeat('X', 6) . substr($decryptedPan, -4)
        : ($decryptedPan !== '' ? 'Invalid PAN' : '—');
    $aadhaarDocUrl = function_exists('proof_document_url')
        ? proof_document_url($applicant->aadhaar_doc ?? null, 'aadhaar')
        : null;
    $panDocUrl = function_exists('proof_document_url')
        ? proof_document_url($applicant->pan_doc ?? $applicant->pancard_doc ?? null, 'pan')
        : null;

    $hasPreviousEaQual = !empty($applicant->previously_number) || !empty($applicant->previously_date);
    $hasCert = !empty($applicant->certificate_no) && !empty($applicant->certificate_date);
    $paymentStatus = strtoupper((string) ($applicant->payment_status ?? $applicant->gateway_payment_status ?? ''));
    $appStatus = strtoupper((string) ($applicant->status ?? $applicant->app_status ?? ''));
    $statusLabel = match ($appStatus) {
        'A' => 'Completed',
        'P' => 'Pending',
        'RE' => 'Resubmitted',
        'PRE' => 'Pending Review',
        'QU' => 'Returned',
        'F', 'RF' => 'Forwarded',
        default => ($appStatus !== '' ? $appStatus : 'N/A'),
    };
    $formDisplay = $formLabel === ''
        ? '—'
        : (str_starts_with(strtoupper($formLabel), 'FORM') ? $formLabel : 'Form ' . $formLabel);
    $mobile = $applicant->applicant_mobile ?? $applicant->mobile ?? $applicant->mobile_no ?? $applicant->contact_no ?? $applicant->phone ?? null;
    $licenseNumber = $applicant->license_number ?? $applicant->licence_number ?? null;
    $oldApplication = $applicant->old_application ?? null;
    $paymentTime = $applicant->transaction_date ?? $applicant->payment_created_at ?? null;
    $applicationFee = $applicant->application_fee ?? null;
    $lateFee = $applicant->late_fee ?? null;
@endphp

<style>
    .app-details-modal-body .adm-hero {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 1rem 1.15rem 0.95rem;
        margin-bottom: 1rem;
    }
    .app-details-modal-body .adm-hero-top {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 0.75rem;
        flex-wrap: wrap;
    }
    .app-details-modal-body .adm-hero-name {
        margin: 0;
        font-size: 1.15rem;
        font-weight: 700;
        color: #0f172a;
        line-height: 1.3;
    }
    .app-details-modal-body .adm-hero-id {
        margin-top: 0.2rem;
        color: #64748b;
        font-size: 0.82rem;
        font-weight: 600;
    }
    .app-details-modal-body .adm-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
    }
    .app-details-modal-body .adm-pill {
        display: inline-flex;
        align-items: center;
        border-radius: 999px;
        padding: 0.22rem 0.7rem;
        font-size: 0.72rem;
        font-weight: 700;
        background: #eef2f7;
        color: #1e3a5f;
    }
    .app-details-modal-body .adm-pill.is-type { background: #dbeafe; color: #1d4ed8; }
    .app-details-modal-body .adm-pill.is-ok { background: #dcfce7; color: #166534; }
    .app-details-modal-body .adm-pill.is-warn { background: #ffedd5; color: #9a3412; }
    .app-details-modal-body .adm-pill.is-muted { background: #e2e8f0; color: #334155; }
    .app-details-modal-body .adm-hero-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 0.7rem;
        margin-top: 0.9rem;
    }
    @media (max-width: 767.98px) {
        .app-details-modal-body .adm-hero-grid { grid-template-columns: 1fr 1fr; }
    }
    .app-details-modal-body .adm-hero-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 0.55rem 0.7rem;
    }
    .app-details-modal-body .adm-hero-item span {
        display: block;
    }
    .app-details-modal-body .adm-k {
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        text-transform: uppercase;
        color: #64748b;
        margin-bottom: 0.15rem;
    }
    .app-details-modal-body .adm-v {
        font-size: 0.88rem;
        font-weight: 600;
        color: #0f172a;
        word-break: break-word;
    }
    .app-details-modal-body .nav-tabs {
        border-bottom: 1px solid #e2e8f0;
        gap: 0.35rem;
    }
    .app-details-modal-body .nav-tabs .nav-link {
        font-weight: 600;
        color: #475569;
        border: none;
        border-radius: 8px 8px 0 0;
        padding: 0.55rem 0.95rem;
    }
    .app-details-modal-body .nav-tabs .nav-link:hover {
        background: #eef2f7;
        color: #1e3a5f;
    }
    .app-details-modal-body .nav-tabs .nav-link.active {
        background: #e8eef6;
        color: #1e3a5f;
        box-shadow: inset 0 -2px 0 #2563eb;
    }
    .app-details-modal-body .adm-tab-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-top: none;
        border-radius: 0 0 12px 12px;
        padding: 1.05rem;
    }
    .app-details-modal-body .adm-section-title {
        color: #1e3a5f;
        font-weight: 700;
        margin: 1.2rem 0 0.6rem;
        padding-bottom: 0.3rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .app-details-modal-body .adm-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.7rem;
    }
    @media (max-width: 767.98px) {
        .app-details-modal-body .adm-fields { grid-template-columns: 1fr; }
    }
    .app-details-modal-body .adm-field {
        background: #f8fafc;
        border: 1px solid #e8eef5;
        border-radius: 10px;
        padding: 0.7rem 0.8rem;
        min-width: 0;
    }
    .app-details-modal-body .adm-field.is-wide { grid-column: 1 / -1; }
    .app-details-modal-body .adm-field.is-changed {
        background: #fff7ed;
        border-color: #fdba74;
    }
    .app-details-modal-body .adm-photo-wrap {
        background: #f8fafc;
        border: 1px solid #e8eef5;
        border-radius: 12px;
        padding: 0.85rem;
        height: 100%;
    }
    .app-details-modal-body .adm-photo-wrap img.adm-photo {
        width: 140px;
        height: 180px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
    }
    .app-details-modal-body .adm-photo-wrap img.adm-sign {
        width: 150px;
        height: 70px;
        object-fit: contain;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
    }
    .app-details-modal-body .adm-pay-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }
    @media (max-width: 767.98px) {
        .app-details-modal-body .adm-pay-grid { grid-template-columns: 1fr; }
    }
    .app-details-modal-body .adm-pay-card {
        background: #f8fafc;
        border: 1px solid #e8eef5;
        border-radius: 12px;
        padding: 0.9rem 1rem;
    }
    .app-details-modal-body .adm-data-table {
        --bs-table-bg: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 0;
    }
    .app-details-modal-body .adm-data-table thead,
    #applicationDetailsModal .adm-data-table thead {
        background: #eef2f7 !important;
        color: #1e293b !important;
    }
    .app-details-modal-body .adm-data-table thead th,
    #applicationDetailsModal .adm-data-table thead tr th,
    #applicationDetailsModal .adm-data-table > thead > tr > th {
        background: #eef2f7 !important;
        color: #334155 !important;
        font-weight: 700 !important;
        font-size: 0.78rem !important;
        letter-spacing: 0.02em !important;
        border: none !important;
        border-bottom: 1px solid #d8e0ea !important;
        white-space: normal !important;
        padding: 0.7rem 0.75rem !important;
    }
    .app-details-modal-body .adm-data-table tbody td,
    #applicationDetailsModal .adm-data-table tbody td {
        vertical-align: middle;
        background: #fff !important;
        color: #1e293b;
        border-color: #eef2f7 !important;
        font-size: 0.86rem;
        padding: 0.65rem 0.75rem;
    }
    .app-details-modal-body .adm-data-table tbody tr:nth-child(even) td {
        background: #f8fafc !important;
    }
    .app-details-modal-body .adm-alter-badge {
        display: inline-block;
        background: #ea580c;
        color: #fff;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 0.12rem 0.4rem;
        border-radius: 999px;
    }
    .app-details-modal-body .adm-renew-badge {
        display: inline-block;
        background: #0f766e;
        color: #fff;
        font-size: 0.62rem;
        font-weight: 800;
        padding: 0.12rem 0.4rem;
        border-radius: 999px;
    }
    .app-details-modal-body a {
        color: #2563eb;
        font-weight: 500;
    }
</style>

<div class="app-details-modal-body">
    <div class="adm-hero">
        <div class="adm-hero-top">
            <div>
                <h6 class="adm-hero-name">{{ $applicant->applicant_name ?? 'Applicant' }}</h6>
                <div class="adm-hero-id">Application ID: {{ $applicant->application_id ?? '—' }}</div>
            </div>
            <div class="adm-pills">
                <span class="adm-pill">{{ $formDisplay }}</span>
                <span class="adm-pill is-type">{{ $applTypeLabel }}</span>
                <span class="adm-pill {{ in_array($appStatus, ['A'], true) ? 'is-ok' : (in_array($appStatus, ['QU'], true) ? 'is-warn' : 'is-muted') }}">{{ $statusLabel }}</span>
                @if($paymentStatus !== '')
                    <span class="adm-pill {{ in_array($paymentStatus, ['PAYMENT', 'PAID', 'SUCCESS'], true) ? 'is-ok' : 'is-muted' }}">{{ $paymentStatus }}</span>
                @endif
            </div>
        </div>
        <div class="adm-hero-grid">
            <div class="adm-hero-item">
                <span class="adm-k">License</span>
                <span class="adm-v">{{ $applicant->license_name ?? '—' }}</span>
            </div>
            <div class="adm-hero-item">
                <span class="adm-k">Applied On</span>
                <span class="adm-v">{{ $appliedOnRaw ? format_date_other($appliedOnRaw) : '—' }}</span>
            </div>
            <div class="adm-hero-item">
                <span class="adm-k">D.O.B / Age</span>
                <span class="adm-v">
                    {{ !empty($applicant->d_o_b) ? format_date($applicant->d_o_b) : '—' }}
                    @if(!empty($applicant->age)) ({{ $applicant->age }} yrs) @endif
                </span>
            </div>
            <div class="adm-hero-item">
                <span class="adm-k">{{ !empty($mobile) ? 'Mobile' : 'Email' }}</span>
                <span class="adm-v">
                    @if(!empty($mobile))
                        {{ $mobile }}
                    @elseif(!empty($applicant->applicant_email))
                        {{ $applicant->applicant_email }}
                    @else
                        —
                    @endif
                </span>
            </div>
            @if(!empty($licenseNumber))
                <div class="adm-hero-item">
                    <span class="adm-k">Licence No</span>
                    <span class="adm-v">{{ $licenseNumber }}</span>
                </div>
            @endif
            @if(!empty($oldApplication))
                <div class="adm-hero-item">
                    <span class="adm-k">Previous Application</span>
                    <span class="adm-v">{{ $oldApplication }}</span>
                </div>
            @endif
        </div>
    </div>

    <ul class="nav nav-tabs" id="appDetailsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="appDetailsHome-tab" data-bs-toggle="tab" data-bs-target="#appDetailsHome" type="button" role="tab" aria-controls="appDetailsHome" aria-selected="true">Personal Details</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="appDetailsPayment-tab" data-bs-toggle="tab" data-bs-target="#appDetailsPayment" type="button" role="tab" aria-controls="appDetailsPayment" aria-selected="false">Payment Status</button>
        </li>
    </ul>

    <div class="tab-content adm-tab-panel" id="appDetailsTabContent">
        <div class="tab-pane fade show active" id="appDetailsHome" role="tabpanel" aria-labelledby="appDetailsHome-tab" tabindex="0">
            @if($isAlterationApp && ($nameAltered || $addressAltered || $hasAlteredWork || $hasAlterationProofs))
                <div class="alert alert-warning py-2 px-3">
                    <strong>Altered in this request</strong>
                    <ul class="mb-0">
                        @if($nameAltered)<li>Applicant name <span class="adm-alter-badge">ALTER</span></li>@endif
                        @if($addressAltered)<li>Address <span class="adm-alter-badge">ALTER</span></li>@endif
                        @if($hasAlteredWork)<li>Work experience marked as altered</li>@endif
                        @if($hasAlterationProofs)<li>Supporting documents uploaded for name/address change</li>@endif
                    </ul>
                </div>
            @endif

            <div class="row g-3">
                <div class="col-md-8">
                    <div class="adm-fields">
                        <div class="adm-field {{ $nameAltered ? 'is-changed' : '' }}">
                            <span class="adm-k">Applicant Name</span>
                            <span class="adm-v">
                                {{ $applicant->applicant_name ?? '—' }}
                                @if($nameAltered)<span class="adm-alter-badge ms-1">ALTER</span>@endif
                            </span>
                            @if($nameAltered && $previousApplicantName !== '')
                                <div class="text-muted small mt-1">Previously: {{ $previousApplicantName }}</div>
                            @endif
                            @if(!empty($nameProofUrl))
                                <div class="mt-1">
                                    <a href="{{ $nameProofUrl }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-file-pdf-o text-danger"></i> View name proof
                                    </a>
                                </div>
                            @endif
                        </div>
                        <div class="adm-field">
                            <span class="adm-k">Father's Name</span>
                            <span class="adm-v">{{ $applicant->fathers_name ?? '—' }}</span>
                        </div>
                        <div class="adm-field">
                            <span class="adm-k">D.O.B &amp; Age</span>
                            <span class="adm-v">
                                {{ !empty($applicant->d_o_b) ? format_date($applicant->d_o_b) : '—' }}
                                @if(!empty($applicant->age)) ({{ $applicant->age }} years old) @endif
                            </span>
                        </div>
                        <div class="adm-field">
                            <span class="adm-k">Email</span>
                            <span class="adm-v">
                                @if(!empty($applicant->applicant_email))
                                    <a href="mailto:{{ e($applicant->applicant_email) }}">{{ e($applicant->applicant_email) }}</a>
                                @else
                                    —
                                @endif
                            </span>
                        </div>
                        @if(!empty($mobile))
                            <div class="adm-field">
                                <span class="adm-k">Mobile</span>
                                <span class="adm-v">{{ $mobile }}</span>
                            </div>
                        @endif
                        <div class="adm-field is-wide {{ $addressAltered ? 'is-changed' : '' }}">
                            <span class="adm-k">Address</span>
                            <span class="adm-v">
                                {{ $applicant->applicants_address ?? $applicant->applicant_address ?? '—' }}
                                @if($addressAltered)<span class="adm-alter-badge ms-1">ALTER</span>@endif
                            </span>
                            @if($addressAltered && $parentAddressValue !== '')
                                <div class="text-muted small mt-1">Previously: {{ $parentAddressValue }}</div>
                            @endif
                            @if(!empty($addressProofUrl))
                                <div class="mt-1">
                                    <a href="{{ $addressProofUrl }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-file-pdf-o text-danger"></i> View address proof
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-4 text-center">
                    <div class="adm-photo-wrap">
                        @if($photoUrl)
                            <img src="{{ $photoUrl }}" alt="Applicant Photo" class="adm-photo mb-2">
                        @else
                            <p class="text-muted mb-2">No photo available</p>
                        @endif
                        @if($signUrl)
                            <img src="{{ $signUrl }}" alt="Applicant Signature" class="adm-sign">
                        @else
                            <p class="text-muted mb-0">No signature available</p>
                        @endif
                    </div>
                </div>
            </div>

            <h6 class="adm-section-title">Educational Qualifications</h6>
            <div class="table-responsive">
                <table class="table table-sm adm-data-table">
                    <thead>
                        <tr>
                            <th>Degree / Level</th>
                            <th>Institution</th>
                            <th>Year of Passing</th>
                            <th>Certificate No</th>
                            <th>Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($educationalQualifications as $education)
                            @php
                                $certificateNo = data_get($education, 'certificate_no');
                                $percentage = data_get($education, 'percentage');
                                $eduDocUrl = !empty($education->document_url)
                                    ? $education->document_url
                                    : (!empty($education->upload_document)
                                        ? competency_document_url($education->upload_document, 'education', (int) ($education->id ?? $education->edu_id ?? 0), 'certificate')
                                        : null);
                            @endphp
                            <tr>
                                <td>{{ $education->educational_level ?? '—' }}</td>
                                <td>{{ $education->institute_name ?? '—' }}</td>
                                <td>{{ $education->year_of_passing ?? '—' }}</td>
                                <td>
                                    @if($certificateNo !== null && $certificateNo !== '')
                                        {{ $certificateNo }}
                                    @elseif($percentage !== null && $percentage !== '')
                                        {{ $percentage }}%
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($eduDocUrl)
                                        <a href="{{ $eduDocUrl }}" target="_blank" rel="noopener noreferrer">
                                            <i class="fa fa-file-pdf-o text-danger"></i> View
                                        </a>
                                    @else
                                        No Documents Uploaded
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No educational details available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($isFormP)
                <h6 class="adm-section-title">Institute Training</h6>
                <div class="table-responsive">
                    <table class="table table-sm adm-data-table">
                        <thead>
                            <tr>
                                <th>Institute Name &amp; Address</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Duration</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($instituteDetails as $institutes)
                                <tr>
                                    <td>{{ $institutes->institute_name_address ?? '—' }}</td>
                                    <td>{{ !empty($institutes->from_date) ? format_date($institutes->from_date) : '—' }}</td>
                                    <td>{{ !empty($institutes->to_date) ? format_date($institutes->to_date) : '—' }}</td>
                                    <td>{{ $institutes->duration ?? '—' }} years</td>
                                    <td class="text-center">
                                        @if(!empty($institutes->upload_doc))
                                            <a href="{{ competency_document_url($institutes->upload_doc, 'experience', (int) ($institutes->id ?? 0), 'supporting') }}" target="_blank" rel="noopener noreferrer">
                                                <i class="fa fa-file-pdf-o text-danger"></i> View
                                            </a>
                                        @else
                                            No Documents Uploaded
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No institute details available.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif

            <h6 class="adm-section-title">{{ $isFormP ? 'Power Station / Work Experience' : 'Work Experience' }}</h6>
            <div class="table-responsive">
                <table class="table table-sm adm-data-table">
                    <thead>
                        <tr>
                            <th>Company</th>
                            <th>Designation</th>
                            <th>Years of Experience</th>
                            <th>Document</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workExperience as $experience)
                            @php
                                $expDocUrl = !empty($experience->document_url)
                                    ? $experience->document_url
                                    : (!empty($experience->upload_document)
                                        ? competency_document_url($experience->upload_document, 'experience', (int) ($experience->exp_id ?? $experience->id ?? 0), 'experience_doc')
                                        : null);
                            @endphp
                            <tr>
                                <td>
                                    {{ $experience->emp_cate ?? $experience->company_name ?? '—' }}
                                    @if(!empty($experience->is_alteration_new))
                                        <span class="adm-alter-badge ms-1">ALTER</span>
                                    @elseif(!empty($experience->is_renewal_new))
                                        <span class="adm-renew-badge ms-1">RENEW</span>
                                    @endif
                                </td>
                                <td>{{ $experience->designation ?? '—' }}</td>
                                <td>{{ format_total_exp_years($experience->total_exp ?? $experience->experience ?? null) ?? (($experience->total_exp ?? $experience->experience ?? '—') . (isset($experience->total_exp) || isset($experience->experience) ? ' years' : '')) }}</td>
                                <td class="text-center">
                                    @if($expDocUrl)
                                        <a href="{{ $expDocUrl }}" target="_blank" rel="noopener noreferrer">
                                            <i class="fa fa-file-pdf-o text-danger"></i> View
                                        </a>
                                    @else
                                        No Documents Uploaded
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No work experience available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($hasPreviousEaQual || $hasCert)
                <h6 class="adm-section-title">Existing Certificates</h6>
                <div class="adm-fields">
                    @if($hasPreviousEaQual)
                        <div class="adm-field">
                            <span class="adm-k">Previous License Number</span>
                            <span class="adm-v">{{ $applicant->previously_number ?: '—' }}</span>
                            <div class="text-muted small mt-1">
                                First issue: {{ !empty($applicant->previously_issue_date) ? format_date($applicant->previously_issue_date) : '—' }}
                                · Valid to: {{ !empty($applicant->previously_date) ? format_date($applicant->previously_date) : '—' }}
                            </div>
                        </div>
                    @endif
                    @if($hasCert)
                        <div class="adm-field">
                            <span class="adm-k">Certificate / License Number</span>
                            <span class="adm-v">{{ $applicant->certificate_no ?: '—' }}</span>
                            <div class="text-muted small mt-1">
                                First issue: {{ !empty($applicant->certificate_issue_date) ? format_date($applicant->certificate_issue_date) : '—' }}
                                · Valid to: {{ !empty($applicant->certificate_date) ? format_date($applicant->certificate_date) : '—' }}
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <h6 class="adm-section-title">Identity Documents</h6>
            <div class="adm-fields">
                <div class="adm-field">
                    <span class="adm-k">Aadhaar</span>
                    <span class="adm-v">{{ $maskedAadhaar }}</span>
                    @if($aadhaarDocUrl)
                        <div class="mt-1">
                            <a href="{{ $aadhaarDocUrl }}" target="_blank" rel="noopener noreferrer">
                                <i class="fa fa-file-pdf-o text-danger"></i> View document
                            </a>
                        </div>
                    @endif
                </div>
                <div class="adm-field">
                    <span class="adm-k">PAN</span>
                    <span class="adm-v">{{ $maskedPan }}</span>
                    @if($panDocUrl)
                        <div class="mt-1">
                            <a href="{{ $panDocUrl }}" target="_blank" rel="noopener noreferrer">
                                <i class="fa fa-file-pdf-o text-danger"></i> View document
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="tab-pane fade" id="appDetailsPayment" role="tabpanel" aria-labelledby="appDetailsPayment-tab" tabindex="0">
            <div class="adm-pay-grid">
                <div class="adm-pay-card">
                    <span class="adm-k">Payment Status</span>
                    <span class="adm-v">
                        @if($paymentStatus !== '')
                            <span class="adm-pill {{ in_array($paymentStatus, ['PAYMENT', 'PAID', 'SUCCESS'], true) ? 'is-ok' : 'is-muted' }}">{{ $paymentStatus }}</span>
                        @else
                            —
                        @endif
                    </span>
                </div>
                <div class="adm-pay-card">
                    <span class="adm-k">Transaction Id</span>
                    <span class="adm-v">{{ $applicant->transaction_id ?? '—' }}</span>
                </div>
                <div class="adm-pay-card">
                    <span class="adm-k">Amount Paid</span>
                    <span class="adm-v">{{ isset($applicant->amount) && $applicant->amount !== null && $applicant->amount !== '' ? number_format((float) $applicant->amount, 2) : '—' }}</span>
                </div>
                <div class="adm-pay-card">
                    <span class="adm-k">Payment Mode</span>
                    <span class="adm-v">{{ $applicant->payment_mode ?? '—' }}</span>
                </div>
                <div class="adm-pay-card">
                    <span class="adm-k">Payment Time</span>
                    <span class="adm-v">{{ !empty($paymentTime) ? format_date_other($paymentTime) : '—' }}</span>
                </div>
                @if($applicationFee !== null && $applicationFee !== '')
                    <div class="adm-pay-card">
                        <span class="adm-k">Application Fee</span>
                        <span class="adm-v">{{ number_format((float) $applicationFee, 2) }}</span>
                    </div>
                @endif
                @if($lateFee !== null && $lateFee !== '' && (float) $lateFee > 0)
                    <div class="adm-pay-card">
                        <span class="adm-k">Late Fee</span>
                        <span class="adm-v">{{ number_format((float) $lateFee, 2) }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
