<style>
    @include('user_login.partials.dashboard-application-preview-styles')
</style>
@php
    $formCode = strtoupper((string) ($formCode ?? $application_details->form_name ?? ''));
    $isFormP = $formCode === 'P';
    $isFormS = $formCode === 'S';
    $applType = strtoupper((string) ($application_details->appl_type ?? ''));
    $applTypeLabel = match ($applType) {
        'N' => 'New',
        'R' => 'Renewal',
        'D' => 'Digitisation',
        'A' => 'Alteration',
        default => $applType !== '' ? $applType : '—',
    };
    $licenceLabel = trim((string) ($licence_name->licence_name ?? ''));
    if ($licenceLabel === '') {
        $licenceLabel = 'Form ' . ($formCode !== '' ? $formCode : '—');
    }
    $photoUrl = competency_media_url($applicant_photo->upload_path ?? null);
    $signUrl = competency_media_url($proof_doc->uploaded_doc ?? $proof_doc->upload_path ?? null);
    $appPk = (int) ($application_details->id ?? $application_details->app_id ?? 0);

    $dashTxt = static function ($value): string {
        $text = trim((string) ($value ?? ''));
        return $text;
    };
    $fmtDate = static function ($value): string {
        $ymd = calendar_date_ymd($value);
        if ($ymd === '') {
            return '';
        }
        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', $ymd)->format('d-m-Y');
        } catch (\Throwable $e) {
            return $ymd;
        }
    };
    $monthLabel = static function ($value): string {
        $raw = trim((string) ($value ?? ''));
        if ($raw === '' || $raw === '0') {
            return '';
        }
        $map = [
            '1' => 'Jan', '01' => 'Jan', 'jan' => 'Jan', 'january' => 'Jan',
            '2' => 'Feb', '02' => 'Feb', 'feb' => 'Feb', 'february' => 'Feb',
            '3' => 'Mar', '03' => 'Mar', 'mar' => 'Mar', 'march' => 'Mar',
            '4' => 'Apr', '04' => 'Apr', 'apr' => 'Apr', 'april' => 'Apr',
            '5' => 'May', '05' => 'May', 'may' => 'May',
            '6' => 'Jun', '06' => 'Jun', 'jun' => 'Jun', 'june' => 'Jun',
            '7' => 'Jul', '07' => 'Jul', 'jul' => 'Jul', 'july' => 'Jul',
            '8' => 'Aug', '08' => 'Aug', 'aug' => 'Aug', 'august' => 'Aug',
            '9' => 'Sep', '09' => 'Sep', 'sep' => 'Sep', 'sept' => 'Sep', 'september' => 'Sep',
            '10' => 'Oct', 'oct' => 'Oct', 'october' => 'Oct',
            '11' => 'Nov', 'nov' => 'Nov', 'november' => 'Nov',
            '12' => 'Dec', 'dec' => 'Dec', 'december' => 'Dec',
        ];
        return $map[strtolower($raw)] ?? $raw;
    };
    $aadhaarPlain = displayProofNumber($application_details->aadhaar ?? '');
    $panPlain = displayProofNumber($application_details->pancard ?? '');
    $aadhaarDocUrl = proof_document_url($application_details->aadhaar_doc ?? null, 'aadhaar');
    $panDocUrl = proof_document_url($application_details->pan_doc ?? $application_details->pancard_doc ?? null, 'pan');

    $hasPrevCert = $dashTxt($application_details->previously_number ?? '') !== '';
    $hasWiremanCert = $isFormS && $dashTxt($application_details->competency_certificate_no ?? $application_details->certificate_no ?? '') !== '';
    $prevCertTitle = match ($formCode) {
        'S' => 'Do you already possess a Supervisor Competency Certificate issued by this Board? If yes, please furnish the details.',
        'W' => 'Previous Wireman / Helper Certificate',
        'WH' => 'Previous Wireman Helper Certificate',
        default => 'Previous Certificate',
    };
    $prevCertTamil = match ($formCode) {
        'S' => 'இந்த வாரியத்தால் வழங்கப்பட்ட மேற்பார்வையாளர் தகுதி சான்றிதழ் உங்களிடம் உள்ளதா? ஆம் என்றால் அதன் குறிப்பு எண் மற்றும் தேதியை குறிப்பிடுக',
        'W' => 'மின்கம்பியாளர் / உதவியாளர் தகுதி சான்றிதழ் விவரம்',
        'WH' => 'மின் கம்பி உதவியாளர் தகுதி சான்றிதழ் விவரம்',
        default => '',
    };
    $expPrevious = $exp_previous ?? collect();
    $expBoard = $exp_board ?? collect();
@endphp
<div class="dash-prv">
    <div class="dash-prv-meta">
        <div class="dash-prv-meta-card">
            <div class="dash-prv-meta-label">Applicant</div>
            <div class="dash-prv-meta-value">{{ $dashTxt($application_details->applicant_name) !== '' ? $application_details->applicant_name : '—' }}</div>
        </div>
        <div class="dash-prv-meta-card">
            <div class="dash-prv-meta-label">Application ID</div>
            <div class="dash-prv-meta-value">{{ $dashTxt($application_details->application_id) !== '' ? $application_details->application_id : '—' }}</div>
        </div>
        <div class="dash-prv-meta-card">
            <div class="dash-prv-meta-label">Licence / Certificate</div>
            <div class="dash-prv-meta-value">{{ $licenceLabel }} · {{ $applTypeLabel }}</div>
        </div>
    </div>

    <section class="dash-prv-section">
        <div class="dash-prv-section-hd">
            <span class="dash-prv-section-num">1</span>
            <div>
                <div class="dash-prv-section-title">Personal &amp; Contact Details</div>
                <div class="dash-prv-section-tamil">விண்ணப்பதாரர் தனிப்பட்ட மற்றும் தொடர்பு விவரங்கள்</div>
            </div>
        </div>
        <div class="dash-prv-section-body">
            <div class="dash-prv-personal">
                <div class="dash-prv-media">
                    <div>
                        <div class="dash-prv-media-label">Photo</div>
                        <div class="dash-prv-thumb dash-prv-thumb--photo">
                            @if ($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Applicant photo">
                            @else
                                <div class="dash-prv-no-img dash-prv-no-img--photo">No Photo</div>
                            @endif
                        </div>
                    </div>
                    <div>
                        <div class="dash-prv-media-label">Signature</div>
                        <div class="dash-prv-thumb dash-prv-thumb--sign">
                            @if ($signUrl)
                                <img src="{{ $signUrl }}" alt="Applicant signature">
                            @else
                                <div class="dash-prv-no-img dash-prv-no-img--sign">No Signature</div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="dash-prv-details">
                    <div class="dash-prv-field">
                        <div class="dash-prv-label">Applicant's Name</div>
                        <div class="dash-prv-value {{ $dashTxt($application_details->applicant_name) === '' ? 'is-empty' : '' }}">{{ $dashTxt($application_details->applicant_name) !== '' ? $application_details->applicant_name : '—' }}</div>
                    </div>
                    <div class="dash-prv-field">
                        <div class="dash-prv-label">Father's Name</div>
                        <div class="dash-prv-value {{ $dashTxt($application_details->fathers_name) === '' ? 'is-empty' : '' }}">{{ $dashTxt($application_details->fathers_name) !== '' ? $application_details->fathers_name : '—' }}</div>
                    </div>
                    <div class="dash-prv-field">
                        <div class="dash-prv-label">Email ID</div>
                        <div class="dash-prv-value {{ $dashTxt($application_details->applicant_email) === '' ? 'is-empty' : '' }}">{{ $dashTxt($application_details->applicant_email) !== '' ? $application_details->applicant_email : '—' }}</div>
                    </div>
                    <div class="dash-prv-field">
                        <div class="dash-prv-label">Date of Birth</div>
                        <div class="dash-prv-value {{ $fmtDate($application_details->d_o_b ?? null) === '' ? 'is-empty' : '' }}">{{ $fmtDate($application_details->d_o_b ?? null) !== '' ? $fmtDate($application_details->d_o_b) : '—' }}</div>
                    </div>
                    <div class="dash-prv-field">
                        <div class="dash-prv-label">Age</div>
                        <div class="dash-prv-value {{ $dashTxt($application_details->age ?? '') === '' ? 'is-empty' : '' }}">{{ $dashTxt($application_details->age ?? '') !== '' ? $application_details->age : '—' }}</div>
                    </div>
                    <div class="dash-prv-field dash-prv-field--full">
                        <div class="dash-prv-label">Address</div>
                        <div class="dash-prv-value {{ $dashTxt($application_details->applicants_address ?? '') === '' ? 'is-empty' : '' }}" style="white-space:pre-line;">{{ $dashTxt($application_details->applicants_address ?? '') !== '' ? $application_details->applicants_address : '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($isFormP)
        <section class="dash-prv-section">
            <div class="dash-prv-section-hd">
                <span class="dash-prv-section-num">6</span>
                <div>
                    <div class="dash-prv-section-title">Technical Qualifications &amp; Experience</div>
                    <div class="dash-prv-section-tamil">தொழில்நுட்ப தகுதி, பயிற்சி மற்றும் அனுபவ விவரங்கள்</div>
                </div>
            </div>
            <div class="dash-prv-section-body">
                <div class="dash-prv-subhead">(i) Educational Qualification</div>
                <div class="dash-prv-table-wrap">
                    <table class="dash-prv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Education Level</th>
                                <th>Institution</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Certificate No.</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($edu_details as $edu)
                                @php
                                    $eduDoc = $edu->upload_document ?? $edu->education_document ?? null;
                                    $eduDocUrl = $eduDoc ? competency_document_url($eduDoc, 'education', (int) ($edu->id ?? $edu->edu_id ?? 0), 'certificate', [$appPk]) : null;
                                    $level = $edu->educational_level ?? $edu->education_level ?? '';
                                    $institute = $edu->institute_name ?? $edu->university ?? '';
                                    $month = $monthLabel($edu->month_passing ?? $edu->month_of_passing ?? '');
                                    $year = $dashTxt($edu->year_of_passing ?? '');
                                    $certNo = $edu->certificate_no ?? '';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($level) !== '' ? $level : '—' }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($institute) !== '' ? $institute : '—' }}</td>
                                    <td>{{ $month !== '' ? $month : '—' }}</td>
                                    <td>{{ $year !== '' && $year !== '0' ? $year : '—' }}</td>
                                    <td>{{ $dashTxt($certNo) !== '' ? $certNo : '—' }}</td>
                                    <td>
                                        @if ($eduDocUrl)
                                            <a class="dash-prv-doc-pill" href="{{ $eduDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                                        @else
                                            <span class="dash-prv-doc-empty">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted py-3 text-center">No education entries</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="dash-prv-subhead">(ii) Training Institute</div>
                <div class="dash-prv-table-wrap">
                    <table class="dash-prv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Institute Name &amp; Address</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Duration (yrs)</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($institutes as $inst)
                                @php
                                    $instDoc = $inst->upload_doc ?? null;
                                    $instDocUrl = $instDoc ? competency_document_url($instDoc, 'institute', (int) ($inst->id ?? 0), 'certificate', [$appPk]) : null;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($inst->institute_name_address ?? '') !== '' ? $inst->institute_name_address : '—' }}</td>
                                    <td>{{ $fmtDate($inst->from_date ?? null) !== '' ? $fmtDate($inst->from_date) : '—' }}</td>
                                    <td>{{ $fmtDate($inst->to_date ?? null) !== '' ? $fmtDate($inst->to_date) : '—' }}</td>
                                    <td>{{ $dashTxt($inst->duration ?? '') !== '' ? $inst->duration : '—' }}</td>
                                    <td>
                                        @if ($instDocUrl)
                                            <a class="dash-prv-doc-pill" href="{{ $instDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                                        @else
                                            <span class="dash-prv-doc-empty">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-muted py-3 text-center">No training institute entries</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="dash-prv-subhead">(iii) Power Station Experience</div>
                <div class="dash-prv-table-wrap">
                    <table class="dash-prv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Power Station</th>
                                <th>From</th>
                                <th>To</th>
                                <th>Duration</th>
                                <th>Designation</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($exp_details as $exp)
                                @php
                                    $station = $exp->org_name ?? $exp->company_name ?? $exp->emp_cate ?? '';
                                    $supportDoc = $exp->support_document ?? $exp->upload_document ?? '';
                                    $supportUrl = $supportDoc !== '' ? competency_document_url($supportDoc, 'experience', (int) ($exp->id ?? $exp->exp_id ?? 0), 'experience_doc') : null;
                                    $durParts = [];
                                    if ((int) ($exp->total_y ?? 0) > 0) { $durParts[] = (int) $exp->total_y . 'y'; }
                                    if ((int) ($exp->total_m ?? 0) > 0) { $durParts[] = (int) $exp->total_m . 'm'; }
                                    if ((int) ($exp->total_d ?? 0) > 0) { $durParts[] = (int) $exp->total_d . 'd'; }
                                    $durTxt = implode(' ', $durParts);
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($station) !== '' ? $station : '—' }}</td>
                                    <td>{{ $fmtDate($exp->from_date ?? null) !== '' ? $fmtDate($exp->from_date) : '—' }}</td>
                                    <td>{{ $fmtDate($exp->to_date ?? null) !== '' ? $fmtDate($exp->to_date) : '—' }}</td>
                                    <td>{{ $durTxt !== '' ? $durTxt : '—' }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($exp->designation ?? '') !== '' ? $exp->designation : '—' }}</td>
                                    <td>
                                        @if ($supportUrl)
                                            <a class="dash-prv-doc-pill" href="{{ $supportUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                                        @else
                                            <span class="dash-prv-doc-empty">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted py-3 text-center">No power station experience</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="dash-prv-field mb-0">
                    <div class="dash-prv-label">(iv) Name of the Employer</div>
                    <div class="dash-prv-value {{ $dashTxt($application_details->employer_detail ?? '') === '' ? 'is-empty' : '' }}" style="white-space:pre-line;">{{ $dashTxt($application_details->employer_detail ?? '') !== '' ? $application_details->employer_detail : '—' }}</div>
                </div>
            </div>
        </section>

        <section class="dash-prv-section">
            <div class="dash-prv-section-hd">
                <span class="dash-prv-section-num">7</span>
                <div>
                    <div class="dash-prv-section-title">Previous Application</div>
                    <div class="dash-prv-section-tamil">முந்தைய விண்ணப்பம் பற்றிய விவரம்</div>
                </div>
            </div>
            <div class="dash-prv-section-body">
                <div class="mb-2">
                    @if ($hasPrevCert)
                        <span class="dash-prv-yes">Yes</span>
                    @else
                        <span class="dash-prv-no">No</span>
                    @endif
                </div>
                @if ($hasPrevCert)
                    <div class="dash-prv-grid-2">
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">Application Number</div>
                            <div class="dash-prv-value">{{ $application_details->previously_number }}</div>
                        </div>
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">Date</div>
                            <div class="dash-prv-value">{{ $fmtDate($application_details->previously_valid_to ?? $application_details->previously_date ?? null) !== '' ? $fmtDate($application_details->previously_valid_to ?? $application_details->previously_date) : '—' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @else
        <section class="dash-prv-section">
            <div class="dash-prv-section-hd">
                <span class="dash-prv-section-num">6</span>
                <div>
                    <div class="dash-prv-section-title">{{ $isFormS ? "Applicant's Educational / Technical Qualification" : 'Educational / Technical Qualification' }}</div>
                    <div class="dash-prv-section-tamil">விண்ணப்பதாரரின் கல்வி தகுதி மற்றும் தேர்ச்சி விவரங்கள்</div>
                </div>
            </div>
            <div class="dash-prv-section-body">
                <div class="dash-prv-table-wrap">
                    <table class="dash-prv-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Education Level</th>
                                <th>University / Institute</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Certificate No.</th>
                                <th>Document</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($edu_details as $edu)
                                @php
                                    $eduDoc = $edu->upload_document ?? $edu->education_document ?? null;
                                    $eduDocUrl = $eduDoc ? competency_document_url($eduDoc, 'education', (int) ($edu->id ?? $edu->edu_id ?? 0), 'certificate', [$appPk]) : null;
                                    $level = $edu->educational_level ?? $edu->education_level ?? '';
                                    $institute = $edu->institute_name ?? $edu->university ?? '';
                                    $month = $monthLabel($edu->month_passing ?? $edu->month_of_passing ?? '');
                                    $year = $dashTxt($edu->year_of_passing ?? '');
                                    $certNo = $edu->certificate_no ?? '';
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($level) !== '' ? $level : '—' }}</td>
                                    <td class="dash-prv-td-left">{{ $dashTxt($institute) !== '' ? $institute : '—' }}</td>
                                    <td>{{ $month !== '' ? $month : '—' }}</td>
                                    <td>{{ $year !== '' && $year !== '0' ? $year : '—' }}</td>
                                    <td>{{ $dashTxt($certNo) !== '' ? $certNo : '—' }}</td>
                                    <td>
                                        @if ($eduDocUrl)
                                            <a class="dash-prv-doc-pill" href="{{ $eduDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                                        @else
                                            <span class="dash-prv-doc-empty">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-muted py-3 text-center">No education entries</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <section class="dash-prv-section">
            <div class="dash-prv-question">
                <div class="dash-prv-section-hd">
                    <span class="dash-prv-section-num dash-prv-section-num--sub">7a</span>
                    <div>
                        <div class="dash-prv-section-title">Previous Work Experience</div>
                        <div class="dash-prv-section-tamil">முந்தைய பணி அனுபவ விவரங்கள்</div>
                    </div>
                </div>
                <div class="dash-prv-section-body">
                    @include('user_login.partials.dashboard-application-preview-exp', [
                        'exp_details' => $expPrevious,
                        'hideVoltageFields' => ! $isFormS,
                    ])
                </div>
            </div>
            <div class="dash-prv-question">
                <div class="dash-prv-section-hd">
                    <span class="dash-prv-section-num dash-prv-section-num--sub">7b</span>
                    <div>
                        <div class="dash-prv-section-title">Are you a Board member of TNELB or Ex board member of TNELB?</div>
                        <div class="dash-prv-section-tamil">நீங்கள் மின்சார உரிமையாளர்கள் வாரியத்தின் குழு உறுப்பினரா / முன்னாள் குழு உறுப்பினரா?</div>
                    </div>
                </div>
                <div class="dash-prv-section-body">
                    @include('user_login.partials.form-s-board-member-view', ['boardMemberRows' => $expBoard])
                </div>
            </div>
        </section>

        <section class="dash-prv-section">
            <div class="dash-prv-section-hd">
                <span class="dash-prv-section-num dash-prv-section-num--sub">8</span>
                <div>
                    <div class="dash-prv-section-title">{{ $prevCertTitle }}</div>
                    @if ($prevCertTamil !== '')
                        <div class="dash-prv-section-tamil">{{ $prevCertTamil }}</div>
                    @endif
                </div>
            </div>
            <div class="dash-prv-section-body">
                <div class="mb-2">
                    @if ($hasPrevCert)
                        <span class="dash-prv-yes">Yes</span>
                    @else
                        <span class="dash-prv-no">No</span>
                    @endif
                </div>
                @if ($hasPrevCert)
                    <div class="dash-prv-grid-4">
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">Certificate Number</div>
                            <div class="dash-prv-value">{{ $application_details->previously_number }}</div>
                        </div>
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">Date of First Issue</div>
                            <div class="dash-prv-value">{{ $fmtDate($application_details->previously_issue_date ?? null) !== '' ? $fmtDate($application_details->previously_issue_date) : '—' }}</div>
                        </div>
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">From date</div>
                            <div class="dash-prv-value">{{ $fmtDate($application_details->previously_valid_from ?? null) !== '' ? $fmtDate($application_details->previously_valid_from) : '—' }}</div>
                        </div>
                        <div class="dash-prv-field mb-0">
                            <div class="dash-prv-label">To date</div>
                            <div class="dash-prv-value">{{ $fmtDate($application_details->previously_valid_to ?? $application_details->previously_date ?? null) !== '' ? $fmtDate($application_details->previously_valid_to ?? $application_details->previously_date) : '—' }}</div>
                        </div>
                    </div>
                @endif
            </div>
        </section>

        @if ($isFormS)
            <section class="dash-prv-section">
                <div class="dash-prv-section-hd">
                    <span class="dash-prv-section-num dash-prv-section-num--sub">9</span>
                    <div>
                        <div class="dash-prv-section-title">Do you also possess Wireman Competency Certificate issued by this Board? If so furnish the details.</div>
                        <div class="dash-prv-section-tamil">இந்த வாரியம் வழங்கிய கம்பி இணைப்பாளர் திறன் சான்றிதழ் உள்ளதா? இருந்தால், அதன் விவரங்களை வழங்கவும்.</div>
                    </div>
                </div>
                <div class="dash-prv-section-body">
                    <div class="mb-2">
                        @if ($hasWiremanCert)
                            <span class="dash-prv-yes">Yes</span>
                        @else
                            <span class="dash-prv-no">No</span>
                        @endif
                    </div>
                    @if ($hasWiremanCert)
                        <div class="dash-prv-grid-4">
                            <div class="dash-prv-field mb-0">
                                <div class="dash-prv-label">Certificate Number</div>
                                <div class="dash-prv-value">{{ $application_details->competency_certificate_no ?? $application_details->certificate_no }}</div>
                            </div>
                            <div class="dash-prv-field mb-0">
                                <div class="dash-prv-label">Date of First Issue</div>
                                <div class="dash-prv-value">{{ $fmtDate($application_details->certificate_issue_date ?? null) !== '' ? $fmtDate($application_details->certificate_issue_date) : '—' }}</div>
                            </div>
                            <div class="dash-prv-field mb-0">
                                <div class="dash-prv-label">From date</div>
                                <div class="dash-prv-value">{{ $fmtDate($application_details->certificate_valid_from ?? null) !== '' ? $fmtDate($application_details->certificate_valid_from) : '—' }}</div>
                            </div>
                            <div class="dash-prv-field mb-0">
                                <div class="dash-prv-label">To date</div>
                                <div class="dash-prv-value">{{ $fmtDate($application_details->certificate_valid_to ?? $application_details->certificate_date ?? null) !== '' ? $fmtDate($application_details->certificate_valid_to ?? $application_details->certificate_date) : '—' }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </section>
        @endif
    @endif

    <section class="dash-prv-section">
        <div class="dash-prv-section-hd">
            <span class="dash-prv-section-num">{{ $isFormP ? '8' : ($isFormS ? '10' : '9') }}</span>
            <div>
                <div class="dash-prv-section-title">Identity Documents</div>
                <div class="dash-prv-section-tamil">அடையாள ஆவண விவரங்கள்</div>
            </div>
        </div>
        <div class="dash-prv-section-body">
            <div class="dash-prv-grid-id">
                <div class="dash-prv-field mb-0">
                    <div class="dash-prv-label">Aadhaar Number</div>
                    <div class="dash-prv-value {{ $aadhaarPlain === '' ? 'is-empty' : '' }}">{{ $aadhaarPlain !== '' ? $aadhaarPlain : '—' }}</div>
                </div>
                <div class="dash-prv-field mb-0">
                    <div class="dash-prv-label">Aadhaar Document</div>
                    <div class="dash-prv-value {{ ! $aadhaarDocUrl ? 'is-empty' : '' }}">
                        @if ($aadhaarDocUrl)
                            <a class="dash-prv-doc-pill" href="{{ $aadhaarDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="dash-prv-field mb-0">
                    <div class="dash-prv-label">PAN Number</div>
                    <div class="dash-prv-value {{ $panPlain === '' ? 'is-empty' : '' }}">{{ $panPlain !== '' ? $panPlain : '—' }}</div>
                </div>
                <div class="dash-prv-field mb-0">
                    <div class="dash-prv-label">PAN Document</div>
                    <div class="dash-prv-value {{ ! $panDocUrl ? 'is-empty' : '' }}">
                        @if ($panDocUrl)
                            <a class="dash-prv-doc-pill" href="{{ $panDocUrl }}" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View Document</a>
                        @else
                            —
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
