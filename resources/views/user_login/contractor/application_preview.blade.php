@include('include.header')

@php
    $formCode = strtoupper(trim((string) ($applicant_data->form_name ?? '')));
    $licenseCode = strtoupper(trim((string) ($license_name_data->cert_licence_code ?? $applicant_data->license_name ?? '')));
    $licenceTitle = trim((string) (optional($license_name_data)->licence_name ?? optional($license_name)->licence_name ?? ''));
    $appliedFor = $licenceTitle !== ''
        ? $licenceTitle . ($licenseCode !== '' ? ' (' . $licenseCode . ')' : '')
        : ($formCode !== '' ? 'Form ' . $formCode : '—');
    $applTypeLabel = trim((string) ($applicant_data->appl_type_label ?? ''));
    if ($applTypeLabel === '') {
        $applTypeLabel = '—';
    }

    $submittedRaw = $applicant_data->dt_submit ?? $applicant_data->created_at ?? null;
    $submittedOn = $submittedRaw
        ? \Carbon\Carbon::parse($submittedRaw)->format('d-m-Y h:i A')
        : '—';

    $fmtDate = static function ($value): string {
        $value = trim((string) ($value ?? ''));
        if ($value === '' || $value === '0000-00-00') {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($value)->format('d-m-Y');
        } catch (\Throwable $e) {
            return $value;
        }
    };
    $yn = static function ($value): string {
        $value = strtolower(trim((string) ($value ?? '')));
        if ($value === '') {
            return '—';
        }
        return strtoupper($value);
    };
    $dash = static function ($value): string {
        $text = trim((string) ($value ?? ''));
        return $text !== '' ? $text : '—';
    };

    $businessAddress = trim(preg_replace('/\s+/', ' ', (string) ($applicant_data->business_address ?? '')) ?? '');
    $ownershipCode = strtolower(trim((string) ($applicant_data->application_ownershiptype ?? '')));
    $ownershipLabel = match ($ownershipCode) {
        'pr' => 'Proprietor',
        'pt' => 'Partnership',
        'pvt' => 'Private Limited (Pvt LTD)',
        'ltd' => 'Limited (LTD)',
        default => ($ownershipCode !== '' ? strtoupper($ownershipCode) : '—'),
    };

    $mobile = trim((string) ($applicantUser->mobile ?? $applicant_data->mobile ?? ''));
    $email = trim((string) ($applicantUser->email ?? $applicant_data->applicant_email ?? ''));

    $sortedOwners = collect($cl_ownership_table)->sortBy(function ($item) {
        return match ($item->ownership_type ?? '') {
            'pr' => 1,
            'pt' => 2,
            'dr' => 3,
            default => 4,
        };
    })->values();

    $attachmentByType = collect($attachments_cl ?? [])->keyBy(function ($row) {
        return strtolower(trim((string) ($row->type ?? '')));
    });

    $addressProofNo = trim((string) ($addressproof->addressproofno ?? ''));
    if ($addressProofNo !== '') {
        $plainProof = displayProofNumber($addressProofNo);
        if ($plainProof !== '') {
            $addressProofNo = $plainProof;
        } else {
            try {
                $addressProofNo = \Illuminate\Support\Facades\Crypt::decryptString($addressProofNo);
            } catch (\Throwable $e) {
                // keep stored value
            }
        }
        if (strlen($addressProofNo) > 4) {
            $addressProofNo = str_repeat('X', max(strlen($addressProofNo) - 4, 0)) . substr($addressProofNo, -4);
        }
    }
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

        <section class="apd-summary row" aria-label="Application summary">
            <div class="col-12 col-sm-6 col-lg-3">
                <article class="apd-summary-card">
                    <span class="apd-ico apd-ico-green"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    <div>
                        <span class="apd-label">Application ID</span>
                        <strong>{{ $applicant_data->application_id }}</strong>
                    </div>
                </article>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <article class="apd-summary-card">
                    <span class="apd-ico apd-ico-purple"><i class="fa fa-file-text-o" aria-hidden="true"></i></span>
                    <div>
                        <span class="apd-label">Applied For</span>
                        <strong>{{ $appliedFor !== '' ? $appliedFor : '—' }}</strong>
                    </div>
                </article>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <article class="apd-summary-card">
                    <span class="apd-ico apd-ico-blue"><i class="fa fa-refresh" aria-hidden="true"></i></span>
                    <div>
                        <span class="apd-label">Application Type</span>
                        <strong>{{ $applTypeLabel }}</strong>
                    </div>
                </article>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <article class="apd-summary-card">
                    <span class="apd-ico apd-ico-orange"><i class="fa fa-calendar" aria-hidden="true"></i></span>
                    <div>
                        <span class="apd-label">Submitted On</span>
                        <strong>{{ $submittedOn }}</strong>
                    </div>
                </article>
            </div>
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
                        @if(!empty($applicant_data->old_application))
                            <div>
                                <dt><span>6.</span> Previous Application</dt>
                                <dd><span>:</span> {{ $applicant_data->old_application }}</dd>
                            </div>
                        @endif
                        @if(!empty($applicant_data->previous_validity_from) || !empty($applicant_data->previous_validity_to) || !empty($applicant_data->license_number))
                            <div>
                                <dt><span>7.</span> Previous Licence</dt>
                                <dd><span>:</span>
                                    {{ $dash($applicant_data->license_number ?? '') }}
                                    @if(!empty($applicant_data->previous_validity_from) || !empty($applicant_data->previous_validity_to))
                                        ({{ $fmtDate($applicant_data->previous_validity_from) ?: '—' }}
                                        to {{ $fmtDate($applicant_data->previous_validity_to) ?: '—' }})
                                    @endif
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="apd-box">
                    <header class="apd-box-hd">
                        <i class="fa fa-users" aria-hidden="true"></i>
                        Ownership / Partners / Directors Details
                        @if(!empty($applicant_data->ownership_doc_url))
                            <a href="{{ $applicant_data->ownership_doc_url }}" class="apd-pdf" target="_blank" rel="noopener" title="Ownership document">
                                <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                            </a>
                        @endif
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
                                    <th>Present business</th>
                                    <th>Competency Certificate</th>
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
                                        $dob = $fmtDate($owner->dob ?? null);
                                        $qualification = trim((string) ($owner->qualification ?? ''));
                                        $qualificationExtra = trim((string) ($owner->qualification_text ?? ''));
                                        if ($qualificationExtra !== '') {
                                            $qualification = trim($qualification . ($qualification !== '' ? ', ' : '') . $qualificationExtra);
                                        }
                                        $ccNo = trim((string) ($owner->competency_certificate_number ?? ''));
                                        $employed = trim((string) ($owner->presently_employed_name ?? ''));
                                        $prevExp = trim((string) ($owner->previous_experience_name ?? ''));
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
                                        <td>
                                            {{ $qualification !== '' ? $qualification : '—' }}
                                            @if (!empty($owner->education_url))
                                                <a href="{{ $owner->education_url }}" class="apd-pdf" target="_blank" rel="noopener" title="Education proof">
                                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $dash($owner->present_business ?? '') }}</td>
                                        <td>
                                            @if($ccNo !== '')
                                                CC No: {{ $ccNo }}<br>
                                                First issue: {{ $fmtDate($owner->competency_certificate_first_issue) ?: '—' }}<br>
                                                From: {{ $fmtDate($owner->competency_certificate_validity_from) ?: '—' }}<br>
                                                To: {{ $fmtDate($owner->competency_certificate_validity_to ?: $owner->competency_certificate_validity) ?: '—' }}
                                            @else
                                                {{ $yn($owner->competency_certificate_holding ?? 'no') }}
                                            @endif
                                            @if($employed !== '')
                                                <div class="apd-muted">Employed: {{ $employed }}{{ !empty($owner->presently_employed_address) ? ' — '.$owner->presently_employed_address : '' }}</div>
                                            @endif
                                            @if($prevExp !== '')
                                                <div class="apd-muted">Previous contractor: {{ $prevExp }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="apd-empty">No ownership details available.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        @if(!empty($clDigitization))
            <section class="apd-panel">
                <header class="apd-panel-hd">
                    <i class="fa fa-id-card" aria-hidden="true"></i>
                    Existing Licence (Digitisation)
                </header>
                <div class="apd-panel-body">
                    <dl class="apd-basic apd-basic-wide">
                        <div>
                            <dt>Licence Number</dt>
                            <dd><span>:</span> {{ $dash($clDigitization->clnumber ?? '') }}</dd>
                        </div>
                        <div>
                            <dt>First Issue</dt>
                            <dd><span>:</span> {{ $fmtDate($clDigitization->fissue ?? null) ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt>Validity</dt>
                            <dd><span>:</span> {{ $fmtDate($clDigitization->from_date ?? null) ?: '—' }} to {{ $fmtDate($clDigitization->to_date ?? null) ?: '—' }}</dd>
                        </div>
                        @if(!empty($clDigitization->cl_doc))
                            <div>
                                <dt>Licence Document</dt>
                                <dd><span>:</span>
                                    <a href="{{ asset($clDigitization->cl_doc) }}" class="apd-view" target="_blank" rel="noopener">
                                        <i class="fa fa-eye" aria-hidden="true"></i> View
                                    </a>
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>
        @endif

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-pencil-square-o" aria-hidden="true"></i>
                4. Authorised Signatory
            </header>
            <div class="apd-panel-body">
                <dl class="apd-basic apd-basic-wide">
                    <div>
                        <dt>Whether authorised signatory is declared</dt>
                        <dd><span>:</span> {{ $yn($applicant_data->authorised_name_designation ?? '') }}</dd>
                    </div>
                    @if(strtolower(trim((string) ($applicant_data->authorised_name_designation ?? ''))) === 'yes')
                        <div>
                            <dt>Name</dt>
                            <dd><span>:</span> {{ $dash($applicant_data->authorised_name ?? '') }}</dd>
                        </div>
                        <div>
                            <dt>Designation</dt>
                            <dd><span>:</span> {{ $dash($applicant_data->authorised_designation ?? '') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-history" aria-hidden="true"></i>
                5. Previous Contractor Licence
            </header>
            <div class="apd-panel-body">
                <dl class="apd-basic apd-basic-wide">
                    <div>
                        <dt>Whether any application was made previously</dt>
                        <dd><span>:</span> {{ $yn($applicant_data->previous_contractor_license ?? '') }}</dd>
                    </div>
                    @if(strtolower(trim((string) ($applicant_data->previous_contractor_license ?? ''))) === 'yes')
                        <div>
                            <dt>Previous licence / application number</dt>
                            <dd><span>:</span> {{ $dash($applicant_data->previous_application_number ?? '') }}</dd>
                        </div>
                        <div>
                            <dt>Previous validity</dt>
                            <dd><span>:</span> {{ $fmtDate($applicant_data->previous_application_validity ?? null) ?: '—' }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-users" aria-hidden="true"></i>
                6A. QC / QSC Staff Details
            </header>
            <div class="apd-table-wrap">
                <table class="apd-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Staff Name</th>
                            <th>CC Number</th>
                            <th>First Issue</th>
                            <th>Validity From</th>
                            <th>Validity To</th>
                            <th>Attachments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($qcQscStaffs_details as $staff)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dash($staff->staff_name ?? '') }}</td>
                                <td>{{ $dash($staff->staff_cc_no ?? '') }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_first_issue ?? null) ?: '—' }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_validity_from ?? null) ?: '—' }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_validity_to ?? null) ?: '—' }}</td>
                                <td>
                                    @if(!empty($staff->appointment_url) || !empty($staff->consent_url))
                                        <div class="apd-file-stack">
                                            @if(!empty($staff->appointment_url))
                                                <a href="{{ $staff->appointment_url }}" class="apd-view" target="_blank" rel="noopener">Appointment <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i></a>
                                            @endif
                                            @if(!empty($staff->consent_url))
                                                <a href="{{ $staff->consent_url }}" class="apd-view" target="_blank" rel="noopener">Consent <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i></a>
                                            @endif
                                        </div>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="apd-empty">No QC / QSC staff details available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-users" aria-hidden="true"></i>
                6B. Other Staff Details
            </header>
            <div class="apd-table-wrap">
                <table class="apd-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Category</th>
                            <th>Name / Designation</th>
                            <th>CC Number</th>
                            <th>First Issue</th>
                            <th>Validity From</th>
                            <th>Validity To</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($otherstaffdetails as $staff)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dash($staff->staff_category ?? '') }}</td>
                                <td>
                                    @if(strtoupper(trim((string) ($staff->staff_category ?? ''))) === 'OTHERS')
                                        {{ $dash($staff->staff_designation ?? $staff->staff_name ?? '') }}
                                    @else
                                        {{ $dash($staff->staff_name ?? '') }}
                                    @endif
                                </td>
                                <td>{{ $dash($staff->staff_cc_no ?? '') }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_first_issue ?? null) ?: '—' }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_validity_from ?? null) ?: '—' }}</td>
                                <td>{{ $fmtDate($staff->staff_cc_validity_to ?? null) ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="apd-empty">No other staff details available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-university" aria-hidden="true"></i>
                7. Bank Solvency Certificate
            </header>
            <div class="apd-panel-body">
                <dl class="apd-basic apd-basic-wide">
                    <div>
                        <dt>Bank name and address</dt>
                        <dd><span>:</span> {{ $dash($banksolvency->bank_address ?? '') }}</dd>
                    </div>
                    <div>
                        <dt>Validity</dt>
                        <dd><span>:</span> {{ !empty($banksolvency->bank_validity) ? $fmtDate($banksolvency->bank_validity) : '—' }}</dd>
                    </div>
                    <div>
                        <dt>Amount</dt>
                        <dd><span>:</span> {{ $dash($banksolvency->bank_amount ?? '') }}</dd>
                    </div>
                    <div>
                        <dt>Proof</dt>
                        <dd><span>:</span>
                            @if(!empty($banksolvency->bank_doc_url))
                                <a href="{{ $banksolvency->bank_doc_url }}" class="apd-view" target="_blank" rel="noopener">
                                    View Document <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i>
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-info-circle" aria-hidden="true"></i>
                Other Details, Address Proof and Enclosures
            </header>
            <div class="apd-panel-body">
                <dl class="apd-basic apd-basic-wide">
                    <div>
                        <dt>8. Convicted for criminal offence</dt>
                        <dd><span>:</span> {{ $yn($applicant_data->criminal_offence ?? '') }}
                            @if(!empty($attachmentByType['criminaloffence']->file_doc_url))
                                <a href="{{ $attachmentByType['criminaloffence']->file_doc_url }}" class="apd-pdf" target="_blank" rel="noopener">
                                    <i class="fa fa-file-pdf-o" aria-hidden="true"></i>
                                </a>
                            @endif
                        </dd>
                    </div>
                
                    <div>
                        <dt>9. Address proof type</dt>
                        <dd><span>:</span> {{ $dash($addressproof->type_doc ?? '') }}</dd>
                        <dt>GST / Rental / Others No</dt>
                        <dd><span>:</span> {{ $addressProofNo !== '' ? $addressProofNo : '—' }}</dd>
                    
                        <dt>Address proof document</dt>
                        <dd><span>:</span>
                            @if(!empty($addressproof->file_doc_url))
                                <a href="{{ $addressproof->file_doc_url }}" class="apd-view" target="_blank" rel="noopener">
                                    View Document <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i>
                                </a>
                            @else
                                —
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-pencil" aria-hidden="true"></i>
                11(ii). Persons authorised to sign
            </header>
            <div class="apd-table-wrap">
                <table class="apd-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Qualification</th>
                            <th>Designation</th>
                            <th>Specimen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($authorisedSigns as $sign)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dash($sign->name_of_authorised_to_sign ?? '') }}</td>
                                <td>{{ $dash($sign->age_of_authorised_to_sign ?? '') }}</td>
                                <td>{{ $dash($sign->qualification_of_authorised_to_sign ?? '') }}</td>
                                <td>{{ $dash($sign->designation_of_authorised_to_sign ?? '') }}</td>
                                <td>
                                    @if(!empty($sign->specimen_url))
                                        <a href="{{ $sign->specimen_url }}" class="apd-view" target="_blank" rel="noopener">
                                            View Document <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i>
                                        </a>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="apd-empty">No authorised signatory rows available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="apd-panel">
            <header class="apd-panel-hd">
                <i class="fa fa-wrench" aria-hidden="true"></i>
                Equipment / Instruments List
            </header>
            <div class="apd-table-wrap">
                <table class="apd-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Equipment Name</th>
                            <th>Type</th>
                            <th>Serial No</th>
                            <th>Make / Model</th>
                            <th>Test Report</th>
                            <th>Purchase Report</th>
                            <th>Date of Test</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($equipmentlist as $equip)
                            @php
                                $master = $equipmentMasters[$equip->equipment_id] ?? null;
                            @endphp
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $dash($master->equip_name ?? '') }}</td>
                                <td>{{ $dash($master->equipment_type ?? '') }}</td>
                                <td>{{ $dash($equip->serial_no ?? '') }}</td>
                                <td>{{ $dash($equip->model_no ?? '') }}</td>
                                <td>
                                    @if(!empty($equip->testreport_url))
                                        <a href="{{ $equip->testreport_url }}" class="apd-view" target="_blank" rel="noopener">View Document <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i></a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if(!empty($equip->purchasereport_url))
                                        <a href="{{ $equip->purchasereport_url }}" class="apd-view" target="_blank" rel="noopener">View Document <i class="fa fa-file-pdf-o text-danger" aria-hidden="true"></i></a>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $fmtDate($equip->dateoftest ?? null) ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="apd-empty">No equipment details available.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        @if(!empty($payment) || !empty($applicant_data->payment_status))
            <section class="apd-panel">
                <header class="apd-panel-hd">
                    <i class="fa fa-credit-card" aria-hidden="true"></i>
                    Payment Details
                </header>
                <div class="apd-panel-body">
                    <dl class="apd-basic apd-basic-wide">
                        <div>
                            <dt>Payment status</dt>
                            <dd><span>:</span> {{ $dash($payment->payment_status ?? $applicant_data->payment_status ?? '') }}</dd>
                        </div>
                        <div>
                            <dt>Application fee</dt>
                            <dd><span>:</span> {{ $dash($payment->application_fee ?? $payment->amount ?? '') }}</dd>
                        </div>
                        @if(!empty($payment->late_fee) && (float) $payment->late_fee > 0)
                            <div>
                                <dt>Late fee</dt>
                                <dd><span>:</span> {{ $payment->late_fee }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt>Transaction ID</dt>
                            <dd><span>:</span> {{ $dash($payment->transaction_id ?? '') }}</dd>
                        </div>
                        <div>
                            <dt>Payment mode</dt>
                            <dd><span>:</span> {{ $dash($payment->payment_mode ?? '') }}</dd>
                        </div>
                        @if(!empty($payment->created_at) || !empty($payment->transaction_date))
                            <div>
                                <dt>Payment date</dt>
                                <dd><span>:</span> {{ $fmtDate($payment->transaction_date ?? $payment->created_at) ?: '—' }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>
        @endif

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
            {{-- <div class="apd-print-row">
                @if ($printUrl)
                    <a href="{{ $printUrl }}" class="apd-print" target="_blank" rel="noopener">
                        <i class="fa fa-print" aria-hidden="true"></i> Print Application
                    </a>
                @else
                    <button type="button" class="apd-print" onclick="window.print()">
                        <i class="fa fa-print" aria-hidden="true"></i> Print Application
                    </button>
                @endif
            </div> --}}
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
        margin-left: -7px;
        margin-right: -7px;
        margin-bottom: 16px;
    }

    .apd-summary > [class*="col-"] {
        padding-left: 7px;
        padding-right: 7px;
        margin-bottom: 14px;
    }

    .apd-summary-card {
        display: flex;
        align-items: center;
        gap: 12px;
        height: 100%;
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

    .apd-basic-wide > div {
        grid-template-columns: minmax(220px, 46%) 1fr;
    }

    .apd-muted {
        margin-top: 4px;
        color: #6b7280;
        font-size: 12px;
        font-weight: 500;
    }

    .apd-file-stack {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
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
