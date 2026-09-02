@php
    $digi = $getdetails_digitisation ?? null;
    $oldCertNo = '';
    $appId = trim((string) ($timeline['application_id'] ?? ''));
    $qcExists = false;
    $isQc = false;
    $isQsc = false;
    $ccDocUrl = null;
    $qcDocUrl = null;
    $firstIssue = '';
    $validFrom = '';
    $validTo = '';
    $clType = '';
    $licenceNo = '';
    $contractorName = '';
    $hasDigi = false;

    $fmtDate = static function ($value): string {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '' || $raw === '0000-00-00') {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($raw)->format('d-m-Y');
        } catch (\Throwable $e) {
            return $raw;
        }
    };

    $docUrl = static function (?string $path, string $folder): ?string {
        $path = trim((string) ($path ?? ''));
        if ($path === '' || strcasecmp($path, 'pending') === 0) {
            return null;
        }
        if (preg_match('#^https?://#i', $path)) {
            return $path;
        }

        $normalized = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($normalized, 'public/')) {
            $normalized = substr($normalized, strlen('public/'));
        }

        $filename = basename($normalized);
        if ($filename === '' || $filename === '.' || $filename === '..') {
            return null;
        }

        $relative = str_contains($normalized, 'uploads/digitization/')
            ? $normalized
            : 'uploads/digitization/'.$folder.'/'.$filename;

        if (is_file(public_path($relative))) {
            return asset($relative);
        }

        $storageRoot = rtrim((string) config('document_versioning.storage_root', base_path('competency')), DIRECTORY_SEPARATOR);
        $storedFile = $storageRoot.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (is_file($storedFile)) {
            return \App\Services\Competency\CompetencyDocumentSupport::publicUrlForStoredPath($relative);
        }

        return asset($relative);
    };

    if ($digi) {
        $hasDigi = true;
        $oldCertNo = trim((string) ($digi->ccnumber ?? $digi->application_id ?? ''));
        $appId = trim((string) ($digi->application_id ?? $appId));
        $qcExists = (string) ($digi->qc_det ?? '') === '1';
        $isQc = $qcExists && (string) ($digi->qc ?? '') === '1';
        $isQsc = $qcExists && (string) ($digi->qsc ?? '') === '1';
        $eligKind = $isQc ? 'QC' : ($isQsc ? 'QSC' : null);
        $ccDocUrl = $docUrl($digi->cc_doc ?? null, 'scc');
        $qcDocUrl = $docUrl($digi->qc_doc ?? null, 'qc');
        $firstIssue = $fmtDate($digi->fissue ?? null);
        $validFrom = $fmtDate($digi->from_date ?? null);
        $validTo = $fmtDate($digi->to_date ?? null);
        $clType = trim((string) ($digi->cl_type ?? ''));
        $licenceNo = trim((string) ($digi->licence_no ?? ''));
        $contractorName = trim((string) ($digi->contractor_name ?? ''));
    }
@endphp

<style>
    .dash-tl-dep {
        --dash-tl-primary: #035ab3;
        --dash-tl-primary-soft: #e8f2ff;
        --dash-tl-text: #12233f;
        --dash-tl-muted: #5a7299;
        --dash-tl-border: #d7e2f0;
        --dash-tl-ok: #157347;
        --dash-tl-ok-bg: #d1e7dd;
        --dash-tl-no: #842029;
        --dash-tl-no-bg: #f8d7da;
        color: var(--dash-tl-text);
        font-size: 0.9rem;
    }
    .dash-tl-dep-intro {
        display: flex;
        align-items: flex-start;
        gap: 0.75rem;
        background: #fff;
        border: 1px solid var(--dash-tl-border);
        border-radius: 0.75rem;
        padding: 0.85rem 1rem;
        margin-bottom: 1rem;
        box-shadow: 0 1px 2px rgba(15, 40, 80, 0.04);
    }
    .dash-tl-dep-intro-icon {
        width: 2.35rem;
        height: 2.35rem;
        border-radius: 0.65rem;
        background: linear-gradient(180deg, #0472d9 0%, #035ab3 100%);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 1.05rem;
    }
    .dash-tl-dep-intro h3 {
        margin: 0;
        font-size: 0.98rem;
        font-weight: 700;
        color: var(--dash-tl-text);
        line-height: 1.35;
    }
    .dash-tl-dep-intro p {
        margin: 0.2rem 0 0;
        color: var(--dash-tl-muted);
        font-size: 0.78rem;
        line-height: 1.45;
    }
    .dash-tl-steps {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
    }
    .dash-tl-step {
        display: grid;
        grid-template-columns: 2.1rem minmax(0, 1fr);
        column-gap: 0.85rem;
        position: relative;
    }
    .dash-tl-step + .dash-tl-step {
        margin-top: 0.15rem;
    }
    .dash-tl-step-rail {
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .dash-tl-step-num {
        width: 2.1rem;
        height: 2.1rem;
        border-radius: 50%;
        background: var(--dash-tl-primary);
        color: #fff;
        font-size: 0.82rem;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        z-index: 1;
        box-shadow: 0 0 0 0.22rem #e8f2ff;
    }
    .dash-tl-step.is-yes .dash-tl-step-num {
        background: var(--dash-tl-ok);
        box-shadow: 0 0 0 0.22rem #d1e7dd;
    }
    .dash-tl-step.is-no .dash-tl-step-num {
        background: #6c757d;
        box-shadow: 0 0 0 0.22rem #e9ecef;
    }
    .dash-tl-step-line {
        flex: 1;
        width: 0.14rem;
        background: #cfdced;
        margin: 0.3rem 0 0.15rem;
        min-height: 1.1rem;
        border-radius: 999px;
    }
    .dash-tl-step:last-child .dash-tl-step-line {
        display: none;
    }
    .dash-tl-step-card {
        background: #fff;
        border: 1px solid var(--dash-tl-border);
        border-radius: 0.75rem;
        overflow: hidden;
        margin-bottom: 0.85rem;
        box-shadow: 0 1px 2px rgba(15, 40, 80, 0.04);
        min-width: 0;
    }
    .dash-tl-step-hd {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.65rem;
        padding: 0.7rem 0.95rem;
        background: linear-gradient(180deg, #f4f8fd 0%, #eaf1fa 100%);
        border-bottom: 1px solid #dde5f3;
    }
    .dash-tl-step-title {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        color: var(--dash-tl-text);
        line-height: 1.4;
    }
    .dash-tl-step-copy {
        margin: 0.15rem 0 0;
        font-size: 0.76rem;
        color: var(--dash-tl-muted);
        line-height: 1.4;
    }
    .dash-tl-step-body {
        padding: 0.9rem 0.95rem 1rem;
    }
    .dash-tl-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(10.5rem, 1fr));
        gap: 0.65rem;
    }
    .dash-tl-meta-item {
        background: #f7fafd;
        border: 1px solid #e3e8f0;
        border-radius: 0.5rem;
        padding: 0.55rem 0.7rem;
        min-width: 0;
    }
    .dash-tl-meta-label {
        display: block;
        font-size: 0.66rem;
        font-weight: 700;
        color: var(--dash-tl-muted);
        text-transform: uppercase;
        letter-spacing: 0.04em;
        margin-bottom: 0.2rem;
    }
    .dash-tl-meta-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: var(--dash-tl-text);
        word-break: break-word;
        line-height: 1.35;
    }
    .dash-tl-meta-value.is-empty {
        color: #9aa8bf;
        font-weight: 500;
        font-style: italic;
    }
    .dash-tl-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.45rem;
        margin-top: 0.8rem;
    }
    .dash-tl-doc-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: var(--dash-tl-primary-soft);
        color: var(--dash-tl-primary);
        border: 1px solid #c5dcf8;
        border-radius: 999px;
        padding: 0.38rem 0.8rem;
        font-size: 0.78rem;
        font-weight: 700;
        text-decoration: none;
        line-height: 1.2;
    }
    .dash-tl-doc-btn .fa-file-pdf-o {
        color: #d9534f;
    }
    .dash-tl-doc-btn:hover,
    .dash-tl-doc-btn:focus {
        background: #d6e8ff;
        color: #024a98;
        text-decoration: none;
    }
    .dash-tl-doc-btn:focus-visible {
        outline: 2px solid var(--dash-tl-primary);
        outline-offset: 2px;
    }
    .dash-tl-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.28rem;
        border-radius: 999px;
        padding: 0.22rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .dash-tl-pill-ok {
        background: var(--dash-tl-ok-bg);
        color: var(--dash-tl-ok);
    }
    .dash-tl-pill-no {
        background: var(--dash-tl-no-bg);
        color: var(--dash-tl-no);
    }
    .dash-tl-pill-muted {
        background: #e9ecef;
        color: #495057;
    }
    .dash-tl-result {
        display: flex;
        align-items: flex-start;
        gap: 0.7rem;
        border-radius: 0.6rem;
        padding: 0.75rem 0.85rem;
        margin-bottom: 0.75rem;
    }
    .dash-tl-result.is-yes {
        background: #edf8f1;
        border: 1px solid #c3e6cb;
    }
    .dash-tl-result.is-no {
        background: #f8f9fa;
        border: 1px solid #e3e8f0;
    }
    .dash-tl-result-icon {
        width: 2rem;
        height: 2rem;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        font-size: 0.95rem;
    }
    .dash-tl-result.is-yes .dash-tl-result-icon {
        background: var(--dash-tl-ok);
        color: #fff;
    }
    .dash-tl-result.is-no .dash-tl-result-icon {
        background: #6c757d;
        color: #fff;
    }
    .dash-tl-result-title {
        margin: 0;
        font-size: 0.9rem;
        font-weight: 700;
        line-height: 1.35;
    }
    .dash-tl-result.is-yes .dash-tl-result-title { color: var(--dash-tl-ok); }
    .dash-tl-result.is-no .dash-tl-result-title { color: #495057; }
    .dash-tl-result-text {
        margin: 0.15rem 0 0;
        font-size: 0.78rem;
        color: var(--dash-tl-muted);
        line-height: 1.4;
    }
    .dash-tl-empty {
        background: #fff;
        border: 1px dashed #c5d3e6;
        border-radius: 0.75rem;
        padding: 1.4rem 1rem;
        text-align: center;
        color: var(--dash-tl-muted);
    }
    .dash-tl-empty i {
        display: block;
        font-size: 1.6rem;
        color: #9aa8bf;
        margin-bottom: 0.45rem;
    }
    @media (max-width: 575.98px) {
        .dash-tl-step-hd {
            flex-direction: column;
            align-items: flex-start;
        }
        .dash-tl-dep-intro {
            flex-direction: column;
        }
    }
</style>

<section class="dash-tl-dep" aria-label="Digitisation dependency details">
    @if (! $hasDigi)
        <div class="dash-tl-empty" role="status">
            <i class="fa fa-info-circle" aria-hidden="true"></i>
            <strong>No digitisation record found</strong>
            <p class="mb-0 mt-1">Old certificate capture and QC / QSC details are not available for this application.</p>
        </div>
    @else
        <div class="dash-tl-dep-intro">
            <span class="dash-tl-dep-intro-icon" aria-hidden="true">
                <i class="fa fa-exchange"></i>
            </span>
            <div>
                <h3>Digitisation dependency</h3>
                <p>
                    Status of the old certificate captured for this application
                    @if ($appId !== '')
                        (<span class="font-weight-bold">{{ $appId }}</span>)
                    @endif
                    and whether QC or QSC eligibility was recorded.
                </p>
            </div>
        </div>

        <ol class="dash-tl-steps">
            <li class="dash-tl-step is-yes">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">1</span>
                    <span class="dash-tl-step-line"></span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">Old certificate captured</h4>
                            <p class="dash-tl-step-copy">
                                Old certificate number
                            </p>
                        </div>
                        <span class="dash-tl-pill dash-tl-pill-ok">
                            <i class="fa fa-check" aria-hidden="true"></i>
                            Captured
                        </span>
                    </header>
                    <div class="dash-tl-step-body">
                        <div class="dash-tl-meta">
                            <div class="dash-tl-meta-item">
                                <span class="dash-tl-meta-label">Certificate number</span>
                                <div class="dash-tl-meta-value {{ $oldCertNo === '' ? 'is-empty' : '' }}">
                                    {{ $oldCertNo !== '' ? $oldCertNo : 'Not recorded' }}
                                </div>
                            </div>
                            <div class="dash-tl-meta-item">
                                <span class="dash-tl-meta-label">Date of first issue</span>
                                <div class="dash-tl-meta-value {{ $firstIssue === '' ? 'is-empty' : '' }}">
                                    {{ $firstIssue !== '' ? $firstIssue : 'Not recorded' }}
                                </div>
                            </div>
                            <div class="dash-tl-meta-item">
                                <span class="dash-tl-meta-label">Validity from</span>
                                <div class="dash-tl-meta-value {{ $validFrom === '' ? 'is-empty' : '' }}">
                                    {{ $validFrom !== '' ? $validFrom : 'Not recorded' }}
                                </div>
                            </div>
                            <div class="dash-tl-meta-item">
                                <span class="dash-tl-meta-label">Validity to</span>
                                <div class="dash-tl-meta-value {{ $validTo === '' ? 'is-empty' : '' }}">
                                    {{ $validTo !== '' ? $validTo : 'Not recorded' }}
                                </div>
                            </div>
                        </div>
                        @if ($ccDocUrl)
                            <div class="dash-tl-actions">
                                <a class="dash-tl-doc-btn" href="{{ $ccDocUrl }}" target="_blank" rel="noopener noreferrer">
                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                    View old certificate
                                    <span class="sr-only">(opens in a new tab)</span>
                                </a>
                            </div>
                        @endif
                    </div>
                </article>
            </li>
            {{-- <li class="dash-tl-step {{ $qcEligible ? 'is-yes' : 'is-no' }}">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">2</span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">QC / QSC eligibility</h4>
                            <p class="dash-tl-step-copy">Check the validation of eligible QC or QSC.</p>
                        </div>
                        @if ($qcEligible)
                            <span class="dash-tl-pill dash-tl-pill-ok">Yes</span>
                        @else
                            <span class="dash-tl-pill dash-tl-pill-no">No</span>
                        @endif
                    </header>
                    <div class="dash-tl-step-body">
                        @if ($qcEligible)
                            <div class="dash-tl-result is-yes">
                                <span class="dash-tl-result-icon" aria-hidden="true">
                                    <i class="fa fa-check"></i>
                                </span>
                                <div>
                                    <p class="dash-tl-result-title">
                                        {{ $eligKind }} is eligible
                                    </p>
                                    <p class="dash-tl-result-text">
                                        @if ($isQc)
                                            Supervisory competency is recognised as Qualified Contractor (QC).
                                        @else
                                            Supervisory competency is recognised as Qualified Supervisor Certificate (QSC).
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <div class="dash-tl-meta">
                                @if ($clType !== '')
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">Grade of licence</span>
                                        <div class="dash-tl-meta-value">{{ $clType }}</div>
                                    </div>
                                @endif
                                @if ($licenceNo !== '')
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">Licence number</span>
                                        <div class="dash-tl-meta-value">{{ $licenceNo }}</div>
                                    </div>
                                @endif
                                @if ($contractorName !== '')
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">Name of contractor</span>
                                        <div class="dash-tl-meta-value">{{ $contractorName }}</div>
                                    </div>
                                @endif
                            </div>

                            @if ($qcDocUrl)
                                <div class="dash-tl-actions">
                                    <a class="dash-tl-doc-btn" href="{{ $qcDocUrl }}" target="_blank" rel="noopener noreferrer">
                                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                        View {{ $eligKind }} document
                                        <span class="sr-only">(opens in a new tab)</span>
                                    </a>
                                </div>
                            @endif
                        @else
                            <div class="dash-tl-result is-no">
                                <span class="dash-tl-result-icon" aria-hidden="true">
                                    <i class="fa fa-minus"></i>
                                </span>
                                <div>
                                    <p class="dash-tl-result-title">QC / QSC is not eligible</p>
                                    <p class="dash-tl-result-text mb-0">
                                        This certificate is not recognised as a qualification for QC or QSC under an EA / ESA contractor licence.
                                    </p>
                                </div>
                            </div>
                        @endif
                    </div>
                </article>
            </li> --}}
            <li class="dash-tl-step {{ $qcExists ? 'is-yes' : 'is-no' }}">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">2</span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">Existing QC / QSC</h4>
                            <p class="dash-tl-step-copy">Check the details of existing QC based on the current work experience with till date validation.</p>
                        </div>
                        @if ($isQc)
                            <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Yes</span>
                        @elseif ($isQsc)
                            <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Yes</span>
                        @else
                            <span class="dash-tl-pill dash-tl-pill-no"><i class="fa fa-times"></i> No</span>
                        @endif
                    </header>
                    <div class="dash-tl-step-body">
                        <div class="dash-tl-result {{ $qcExists ? 'is-yes' : 'is-no' }}">
                            <span class="dash-tl-result-icon" aria-hidden="true">
                                <i class="fa fa-check"></i>
                            </span>
                            <div>
                                <p class="dash-tl-result-text">
                                    @if ($isQc)
                                        Supervisory competency is recognised as Qualified Contractor (QC).
                                        <div class="dash-tl-meta">
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Grade of licence</span>
                                                <div class="dash-tl-meta-value">{{ $clType }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Licence number</span>
                                                <div class="dash-tl-meta-value">{{ $licenceNo }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Name of contractor</span>
                                                <div class="dash-tl-meta-value">{{ $contractorName }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Document</span>
                                                <div class="dash-tl-meta-value"><a href="{{ $qcDocUrl }}" target="_blank" rel="noopener noreferrer"><i class="fa fa-file-pdf-o"></i> View document</a></div>
                                            </div>
                                        </div>
                                    @elseif ($isQsc)
                                        Supervisory competency is recognised as Qualified Supervisor Certificate (QSC).
                                        <div class="dash-tl-meta">
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Grade of licence</span>
                                                <div class="dash-tl-meta-value">{{ $clType }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Licence number</span>
                                                <div class="dash-tl-meta-value">{{ $licenceNo }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Name of contractor</span>
                                                <div class="dash-tl-meta-value">{{ $contractorName }}</div>
                                            </div>
                                            <div class="dash-tl-meta-item">
                                                <span class="dash-tl-meta-label">Document</span>
                                                <div class="dash-tl-meta-value"><a href="{{ $qcDocUrl }}" target="_blank" rel="noopener noreferrer"><i class="fa fa-file-pdf-o" style="color: #bb1a1a; font-size: 1.2rem;"></i> View document</a></div>
                                            </div>
                                        </div>
                                    @else
                                        No existing QC / QSC is found.
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </article>
            </li>
            <li class="dash-tl-step {{ !empty($get_till_date_exp) ? 'is-yes' : 'is-no' }}">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">3</span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">Check till date validation</h4>
                            <p class="dash-tl-step-copy">Experience must be validated till date. as current work experience with till date validation.</p>
                        </div>
                        <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Captured</span>
                    </header>
                    <div class="dash-tl-step-body">
                        <div class="dash-tl-result is-yes">
                            <span class="dash-tl-result-icon" aria-hidden="true">
                                <i class="fa fa-check"></i>
                            </span>
                            <div>
                                <p class="dash-tl-result-text">
                                    Experience must be validated till date. as current work experience with till date validation.
                                </p>
                                <div class="dash-tl-meta">
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">Employee type</span>
                                        <div class="dash-tl-meta-value">{{ $get_till_date_exp->emp_type === 'electrical_contractor' ? 'Electrical Contractor' : '' }}</div>
                                    </div>
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">ContractorLicence & No</span>
                                        @php
                                            $licence_no = str_replace('||', ',', (string) ($get_till_date_exp->emp_cate ?? ''));
                                        @endphp
                                        <div class="dash-tl-meta-value">{{ $licence_no }}</div>
                                    </div>
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">from_date</span>
                                        <div class="dash-tl-meta-value">{{ format_date($get_till_date_exp->from_date) }}</div>
                                    </div>
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">to_date (till date)</span>
                                        <div class="dash-tl-meta-value">{{ format_date($get_till_date_exp->to_date) }} @if($get_till_date_exp->work_to_till_date == 1) (<span class="text-danger">till date</span>) @endif</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>
            </li>
            @php
                $app_status = trim((string) ($application->app_status ?? ''));
            @endphp
            @if($app_status === 'A')
            <li class="dash-tl-step is-yes">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">4</span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">Issued Certificate</h4>
                            <p class="dash-tl-step-copy">Check the issued certificate details.</p>
                        </div>
                        <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Issued(Digitised)</span>
                    </header>
                    <div class="dash-tl-step-body">
                        <div class="dash-tl-result is-yes">
                            
                                <div class="dash-tl-meta-item">
                                    <span class="dash-tl-meta-label">Certificate number</span>
                                    <div class="dash-tl-meta-value">{{ $get_issued_certificate->certificate_no }}</div>
                                </div>
                                <div class="dash-tl-meta-item">
                                    <span class="dash-tl-meta-label">Date of First issue</span>
                                    <div class="dash-tl-meta-value">{{ format_date($get_issued_certificate->dateof_issue) }}</div>
                                </div>
                                <div class="dash-tl-meta-item">
                                    <span class="dash-tl-meta-label">Validity from</span>
                                    <div class="dash-tl-meta-value">{{ format_date($get_issued_certificate->valid_from) }}</div>
                                </div>
                                <div class="dash-tl-meta-item">
                                    <span class="dash-tl-meta-label">Validity to</span>
                                    <div class="dash-tl-meta-value">{{ format_date($get_issued_certificate->valid_to) }}</div>
                                </div>
                        </div>
                    </div>
                </article>
            </li>
            @endif
            @if ($get_digitisation_mapping)
            <li class="dash-tl-step {{ !empty($get_digitisation_mapping->new_cc_no) ? 'is-yes' : 'is-no' }}">
                <div class="dash-tl-step-rail" aria-hidden="true">
                    <span class="dash-tl-step-num">5</span>
                </div>
                <article class="dash-tl-step-card">
                    <header class="dash-tl-step-hd">
                        <div>
                            <h4 class="dash-tl-step-title">Digitisation mapping</h4>
                            <p class="dash-tl-step-copy">Check the details of digitisation mapping of old certificate to new certificate.</p>
                        </div>
                        <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Captured</span>
                        @if(!empty($get_digitisation_mapping->new_cc_no)) 
                        <span class="dash-tl-pill dash-tl-pill-ok"><i class="fa fa-check"></i> Digitised</span> @else 
                        <span class="dash-tl-pill dash-tl-pill-no"><i class="fa fa-times"></i> Not Digitised</span> 
                        @endif
                    </header>
                    <div class="dash-tl-step-body">
                        <div class="dash-tl-result is-yes">
                            <span class="dash-tl-result-icon" aria-hidden="true">
                                <i class="fa fa-check"></i>
                            </span>
                            <div>
                            <p class="dash-tl-result-text">
                                The old certificate has been digitised and mapped to the new certificate.
                            </p>
                            <div class="dash-tl-meta">
                                <div class="dash-tl-meta-item">
                                    <span class="dash-tl-meta-label">Old certificate number</span>
                                    <div class="dash-tl-meta-value">{{ $get_digitisation_mapping->old_cc_no }}</div>
                                </div>
                                @if(!empty($get_digitisation_mapping->new_cc_no))
                                    <div class="dash-tl-meta-item">
                                        <span class="dash-tl-meta-label">On Approval (New Certificate number)</span>
                                        <div class="dash-tl-meta-value">{{ $get_digitisation_mapping->new_cc_no }}</div>
                                    </div>
                                @endif
                            </div>
                    </div>
                    </article>
                </li>
            @endif
            </ol>
        @endif
    </section>
