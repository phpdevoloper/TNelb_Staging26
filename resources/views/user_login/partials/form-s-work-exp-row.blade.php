@php
    $legacyEmpMap = [
        'company' => 'private_organisation',
        'contractor' => 'electrical_contractor',
        'apprentice' => 'apprenticeship',
        'electrical_inspector' => 'govt_organisation',
        'retired_employees' => 'retired_employee',
        'board_member_tnelb' => 'board_member_tnelb',
    ];
    $hasRow = isset($expRow) && $expRow;
    $empTypeRaw = $hasRow ? (string) ($expRow->emp_type ?? '') : '';
    $empType = $legacyEmpMap[$empTypeRaw] ?? $empTypeRaw;
    $contractorCat = '';
    $licenceNo = '';
    if ($hasRow && $empType === 'electrical_contractor') {
        $stored = (string) ($expRow->emp_cate ?? '');
        if ($stored !== '' && str_contains($stored, '||')) {
            $parts = explode('||', $stored, 2);
            $contractorCat = $parts[0] ?? '';
            $licenceNo = $parts[1] ?? '';
        } elseif ($stored !== '') {
            $contractorCat = $stored;
        }
    }
    $orgName = $hasRow ? (string) ($expRow->org_name ?? $expRow->company_name ?? '') : '';
    if ($hasRow && $orgName === '' && $empType !== 'electrical_contractor' && ! empty($expRow->emp_cate)) {
        $orgName = (string) $expRow->emp_cate;
    }
    $orgAddress = $hasRow ? (string) ($expRow->org_address ?? '') : '';
    $designation = $hasRow ? (string) ($expRow->designation ?? '') : '';
    $nature = $hasRow ? (string) ($expRow->nature_work ?? '') : '';
    $voltage = $hasRow ? (string) ($expRow->voltage_level ?? '') : '';
    $kva = $hasRow && $expRow->transformer_kva !== null && $expRow->transformer_kva !== '' ? (string) $expRow->transformer_kva : '';
    if ($kva !== '' && is_numeric($kva)) {
        $kva = (string) (0 + $kva);
    }
    $kvaOptions = ['25', '315', '400', '500', '630', '800', '1000', 'Above 1000'];
    $workFromDate = ($hasRow && $expRow->from_date) ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
    $workToDate = ($hasRow && $expRow->to_date) ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
    $isTill = $hasRow && $workFromDate !== '' && $workToDate === '';
    $durY = $hasRow && $expRow->total_y !== null ? (string) $expRow->total_y : '';
    $durM = $hasRow && $expRow->total_m !== null ? (string) $expRow->total_m : '';
    $durD = $hasRow && $expRow->total_d !== null ? (string) $expRow->total_d : '';
    $totalExp = $hasRow
        ? legacy_total_exp_from_duration(
            $expRow->total_exp ?? $expRow->experience ?? null,
            $expRow->total_y ?? null,
            $expRow->total_m ?? null,
            $expRow->total_d ?? null
        )
        : '';
    $supportDoc = $hasRow ? (string) ($expRow->support_document ?? $expRow->upload_document ?? '') : '';
    $relieveDoc = $hasRow ? (string) ($expRow->releive_document ?? $expRow->relieve_document ?? '') : '';
    $workId = $hasRow ? (string) ($expRow->exp_id ?? $expRow->id ?? '') : '';
    $meetingDetails = $hasRow ? (string) ($expRow->board_meeting_details ?? '') : '';
    $meetingDate = ($hasRow && !empty($expRow->board_meeting_date))
        ? \Carbon\Carbon::parse($expRow->board_meeting_date)->format('Y-m-d')
        : '';
    $rowIndex = $rowIndex ?? 0;
    $workPart = $workPart ?? 'all';
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
    $defaultTillDate = !empty($defaultTillDate);
    $hideDuration = !empty($hideDuration);
    $hideDates = !empty($hideDates) || $workPart === 'current';
    $hideRemoveButton = !empty($hideRemoveButton);
    $hideBoardPanelNote = !empty($hideBoardPanelNote);
    $useBootstrapGrid = !empty($useBootstrapGrid);
    $showSummaryPanel = !isset($showSummaryPanel) || (bool) $showSummaryPanel;
    $bxCol = static function (string $classes = '') use ($useBootstrapGrid): string {
        return $useBootstrapGrid ? trim($classes) : '';
    };
    if (!$hasRow && $defaultTillDate) {
        $isTill = true;
    }
    $removeClasses = 'work-row-remove remove-work' . ($workId !== '' ? ' remove_exp' : '');
    $alterationExistingRow = !empty($alterationExistingRow);
    $storedRowClass = $workId !== ''
        ? ($showSummaryPanel ? ' is-complete work-row--compact work-row--in-summary' : ' is-complete work-row--expanded')
        : '';
    if ($alterationExistingRow) {
        $storedRowClass .= ' fs-alt-existing-work';
    }
    $isBoardMemberRow = ($empType === 'board_member_tnelb');
    $meetingDetailsName = $isBoardMemberRow ? 'work_board_meeting_details[]' : '';
    $meetingDateName = $isBoardMemberRow ? 'work_board_meeting_date[]' : '';
    $hideUploadWhenDocExists = !empty($hideUploadWhenDocExists);
    $hideSupportUpload = $hideUploadWhenDocExists && $supportDoc !== '';
    $hideRelieveUpload = $hideUploadWhenDocExists && $relieveDoc !== '';
@endphp
<div class="work-entry-block">
<div class="work-fields work-row{{ $storedRowClass }}" data-row-index="{{ $rowIndex }}">
    @unless ($isBoardMemberRow)
        <input type="hidden" class="work-board-meeting-placeholder" name="work_board_meeting_details[]" value="">
        <input type="hidden" class="work-board-meeting-placeholder" name="work_board_meeting_date[]" value="">
    @endunless
    <div class="work-row-head" role="group">
        <span class="work-row-spacer"></span>
        <div class="work-row-head-actions">
            <button type="button" class="work-row-toggle-btn" aria-expanded="false" title="Expand to edit" aria-label="Expand entry to edit">
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button>
            @unless ($hideRemoveButton || $alterationExistingRow)
            <button type="button" class="{{ $removeClasses }}"
                @if($workId !== '') data-exp_id="{{ $workId }}" data-url="{{ route('delete_experience') }}" @endif
                title="Remove this entry" aria-label="Remove this work experience entry">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
            @endunless
        </div>
    </div>

    <div class="work-row-grid{{ $useBootstrapGrid ? ' row g-2' : '' }}">
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field">
            <label class="work-card-field-label">Employment Type <span class="req">*</span></label>
            <select class="form-control work-employment-type" name="work_employment_type[]" required @if($alterationExistingRow) disabled @endif>
                @include('user_login.partials.form-s-work-exp-employment-options', [
                    'selectedEmpType' => $empType,
                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                ])
            </select>
        </div>
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="contractor-cat">
            <label class="work-card-field-label">Grade of Licence <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            {{-- Select is UI-only: disabled fields are omitted from FormData and break [] index alignment. --}}
            <select class="form-control work-contractor-cat" disabled autocomplete="off" aria-label="Grade of Licence">
                <option value="">—</option>
                @foreach (['ESA', 'EA', 'ESB', 'EB'] as $cat)
                    <option value="{{ $cat }}" {{ $contractorCat === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <input type="hidden" class="work-contractor-category-sync" name="work_contractor_category[]" value="{{ $contractorCat }}" @if($alterationExistingRow) disabled @endif>
            <span class="work-card-field-hint" data-hint="cat" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
        </div>
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="licence-number">
            <label class="work-card-field-label">Licence No <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <input type="text" class="form-control work-licence-number" maxlength="15" inputmode="numeric" pattern="[0-9]*" autocomplete="off" disabled placeholder="e.g. 12345" value="{{ preg_replace('/\D+/', '', (string) $licenceNo) }}" aria-label="Licence number">
            <input type="hidden" class="work-licence-number-sync" name="work_licence_number[]" value="{{ preg_replace('/\D+/', '', (string) $licenceNo) }}" @if($alterationExistingRow) disabled @endif>
            <span class="work-card-field-hint" data-hint="licence" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
        </div>
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="organisation">
            <label class="work-card-field-label">Organisation <span class="req">*</span></label>
            <input type="text" class="form-control work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" disabled placeholder="Organisation name" value="{{ $orgName }}">
        </div>
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="organisation-address">
            <label class="work-card-field-label">Address <span class="req">*</span></label>
            <input type="text" class="form-control work-org-address" name="work_organisation_address[]" maxlength="255" autocomplete="off" disabled placeholder="Street, City, State, PIN" value="{{ $orgAddress }}">
        </div>
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="designation">
            <label class="work-card-field-label">Designation <span class="req">*</span></label>
            <input type="text" class="form-control work-designation" name="designation[]" maxlength="80" autocomplete="off" disabled placeholder="e.g. Site Engineer" value="{{ $designation }}">
        </div>
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="work-nature">
            <label class="work-card-field-label">Work Nature <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <select class="form-control work-nature" name="work_nature_of_work[]" disabled>
                <option value="">—</option>
                <option value="erection" {{ $nature === 'erection' ? 'selected' : '' }}>Erection</option>
                <option value="maintenance" {{ $nature === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="erection_maintenance" {{ $nature === 'erection_maintenance' ? 'selected' : '' }}>Erection &amp; Maintenance</option>
            </select>
        </div>
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="voltage-level">
            <label class="work-card-field-label">Voltage Level <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <select class="form-control work-voltage" name="work_voltage_level[]" disabled>
                <option value="">—</option>
                <option value="up_to_650v" {{ $voltage === 'up_to_650v' ? 'selected' : '' }}>Up to 650V</option>
                <option value="650v_to_33kv" {{ $voltage === '650v_to_33kv' ? 'selected' : '' }}>Above 650V to 33KV</option>
                <option value="above_33kv" {{ $voltage === 'above_33kv' ? 'selected' : '' }}>Above 33KV</option>
            </select>
        </div>
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="transformer-kva">
            <label class="work-card-field-label">Transformer (kVA) <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            {{-- Select is UI-only: disabled fields are omitted from FormData and break [] index alignment.
                 Hidden sync always posts one slot per work row (same pattern as work_to_till_date[]). --}}
            <select class="form-control work-transformer-kva" disabled autocomplete="off" aria-label="Transformer kVA">
                <option value="">Select</option>
                @foreach ($kvaOptions as $kvaOpt)
                    <option value="{{ $kvaOpt }}" {{ (string) $kva === (string) $kvaOpt ? 'selected' : '' }}>{{ $kvaOpt }}</option>
                @endforeach
            </select>
            <input type="hidden" class="work-transformer-kva-sync" name="work_transformer_kva[]" value="{{ $kva }}" @if($alterationExistingRow) disabled @endif>
            <span class="work-card-field-hint" data-hint="kva" style="display:none;"><i class="fa fa-info-circle"></i> Not applicable for voltage up to 650V</span>
        </div>
        @unless ($hideDates)
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="from-date">
            <label class="work-card-field-label">From date <span class="req">*</span></label>
            <input type="date" class="form-control work-date-from" name="work_date_from[]" value="{{ $workFromDate }}" title="From date" aria-label="Period of experience: from date" disabled>
        </div>
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="to-date">
            <label class="work-card-field-label">To date <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <input type="date" class="form-control work-date-to" name="work_date_to[]" value="{{ $workToDate }}" title="To date" aria-label="Period of experience: to date" disabled>
            <label class="work-card-till-toggle">
                <input type="checkbox" class="work-date-till" {{ $isTill ? 'checked' : '' }}>
                <span>Till date (currently working)</span>
            </label>
            <input type="hidden" class="work-date-till-hidden" name="work_to_till_date[]" value="{{ $isTill ? '1' : '0' }}">
        </div>
        @else
        <input type="hidden" class="work-date-from" name="work_date_from[]" value="" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" class="work-date-to" name="work_date_to[]" value="" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" class="work-date-till-hidden" name="work_to_till_date[]" value="0" @if($alterationExistingRow) disabled @endif>
        @endunless
        @unless ($hideDuration)
        <div class="work-card-field work-card-field--duration">
            <label class="work-card-field-label">Duration</label>
            <div class="work-card-duration-readout" role="group" aria-label="Auto-calculated duration">
                <div class="work-card-duration-cell">
                    <span class="work-duration-label">Years</span>
                    <input type="text" class="form-control work-duration-y" readonly inputmode="none" tabindex="-1" placeholder="0" value="{{ $durY }}" aria-label="Years in this period">
                </div>
                <div class="work-card-duration-cell">
                    <span class="work-duration-label">Months</span>
                    <input type="text" class="form-control work-duration-m" readonly inputmode="none" tabindex="-1" placeholder="0" value="{{ $durM }}" aria-label="Months in this period">
                </div>
                <div class="work-card-duration-cell">
                    <span class="work-duration-label">Days</span>
                    <input type="text" class="form-control work-duration-d" readonly inputmode="none" tabindex="-1" placeholder="0" value="{{ $durD }}" aria-label="Days in this period">
                </div>
            </div>
            <span class="work-card-field-hint" data-hint="duration-650v" style="display:none;"><i class="fa fa-info-circle"></i> Not counted toward experience total (voltage up to 650V)</span>
        </div>
        @else
        <input type="hidden" class="work-duration-y" value="{{ $durY }}">
        <input type="hidden" class="work-duration-m" value="{{ $durM }}">
        <input type="hidden" class="work-duration-d" value="{{ $durD }}">
        @endunless
        {{-- Board-meeting UI for non-§7b layouts only; §7b uses form-s-work-exp-7b-row. --}}
        <div class="work-board-member-panel work-row-grid-span" style="{{ $isBoardMemberRow ? '' : 'display:none;' }}">
            <div class="work-board-member-panel-hd">
                <span class="work-board-member-panel-badge">Board Member</span>
                <span class="work-board-member-panel-title">Details of board meeting attended</span>
                <span class="section-req">*</span>
                <span class="work-board-member-panel-hint">Mandatory when employment type is Board member of TNELB or Ex board member of TNELB</span>
                <span class="work-board-member-panel-tamil">தமிழ்நாடு மின்சார வாரிய கூட்டத்தில் கலந்துகொண்ட விவரங்கள்</span>
            </div>
            <div class="work-board-member-panel-body">
                <div class="work-card-field work-board-meeting-field" data-field="board-meeting-details">
                    <label class="work-card-field-label">Details of the meeting attended<span class="req">*</span></label>
                    <select class="form-control work-board-meeting-details" @if ($meetingDetailsName) name="{{ $meetingDetailsName }}" @endif autocomplete="off" {{ $isBoardMemberRow ? '' : 'disabled' }}>
                        <option value="">Select</option>
                        @for ($meetingOpt = 100; $meetingOpt <= 999; $meetingOpt++)
                            <option value="{{ $meetingOpt }}" {{ (string) $meetingDetails === (string) $meetingOpt ? 'selected' : '' }}>{{ $meetingOpt }}</option>
                        @endfor
                    </select>
                </div>
                <div class="work-card-field work-board-meeting-field" data-field="board-meeting-date">
                    <label class="work-card-field-label">Date of Meeting <span class="req">*</span></label>
                    <input type="date" class="form-control work-board-meeting-date" @if ($meetingDateName) name="{{ $meetingDateName }}" @endif value="{{ $meetingDate }}" title="Date of Meeting" aria-label="Date of board meeting attended" {{ $isBoardMemberRow ? '' : 'disabled' }}>
                </div>
            </div>
            @unless ($hideBoardPanelNote)
            <p class="work-board-member-panel-note"><i class="fa fa-paperclip"></i> Attach supporting documents for the meeting in the <strong>Supporting docs</strong> field below.</p>
            @endunless
        </div>
        <div class="{{ $bxCol('col-12 col-md-4') }} work-card-field" data-field="support-doc">
            <label class="work-card-field-label">Supporting docs <span class="req">*</span></label>
            @if ($supportDoc !== '')
                <div class="work-doc-existing mb-1 text-center">
                    <a class="text-primary" href="{{ competency_document_url($supportDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'experience_doc') }}" target="_blank" rel="noopener">
                        <i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document
                    </a>
                    <button type="button" class="btn btn-sm btn-danger ml-1 remove-work-doc-confirm" data-doc-kind="support">Remove</button>
                </div>
            @endif
            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined{{ $hideSupportUpload ? ' d-none work-upload-hidden-until-remove' : '' }}" data-upload-kind="work" data-doc-field="support">
                <input class="form-control work-doc-input" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
            </div>
            <span class="work-card-field-hint{{ $hideSupportUpload ? ' d-none work-upload-hint-hidden-until-remove' : '' }}"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
        </div>
        @if (($workPart ?? 'all') !== 'current')
        <div class="{{ $bxCol('col-12 d-none') }} work-card-field" data-field="relieve">
            <label class="work-card-field-label">Relieving Letter <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            @if ($relieveDoc !== '')
                <div class="work-relieve-existing mb-1 text-center">
                    <a class="text-primary" href="{{ competency_document_url($relieveDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'relieving_doc') }}" target="_blank" rel="noopener">
                        <i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document
                    </a>
                    <button type="button" class="btn btn-sm btn-danger ml-1 remove-work-relieve-confirm" data-doc-kind="relieve">Remove</button>
                </div>
            @endif
            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined{{ $hideRelieveUpload ? ' d-none work-upload-hidden-until-remove' : '' }}" data-upload-kind="work" data-doc-field="relieve">
                <input class="form-control work-relieve-input" name="work_relieving_letter[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
            </div>
            <span class="work-card-field-hint" data-hint="relieve" style="display:none;"><i class="fa fa-info-circle"></i> Not required when "Till date" is selected</span>
            <span class="work-card-field-hint" data-hint="relieve-board" style="display:none;"><i class="fa fa-info-circle"></i> Optional for Board Member / Ex. Board Member of TNELB</span>
            <span class="work-card-field-hint" data-hint="relieve-default"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
        </div>
        @endif
    </div>

    @if ($showSummaryPanel)
    <div class="work-row-done-bar{{ $useBootstrapGrid ? ' col-12' : '' }}">
        <button type="button" class="work-row-done-btn" aria-label="Submit this entry and return to summary card">
            <i class="fa fa-check" aria-hidden="true"></i> Submit
        </button>
    </div>
    @endif

        <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $totalExp }}" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="work_level[]" class="work-level-sync" value="{{ $orgName }}" tabindex="-1" aria-hidden="true" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="experience[]" class="experience-sync" value="{{ $totalExp }}" tabindex="-1" aria-hidden="true" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="work_exp_section[]" value="{{ $workPart }}" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="work_id[]" value="{{ $workId }}" @if($alterationExistingRow) disabled @endif>
        @if ($alterationExistingRow)
        <input type="hidden" name="fs_alt_existing_work[]" value="1" disabled>
        @endif
        <input type="hidden" name="existing_work_document[]" value="{{ $supportDoc }}" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="existing_work_relieving_document[]" value="{{ $relieveDoc }}" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="removed_document_work[]" value="0" @if($alterationExistingRow) disabled @endif>
        <input type="hidden" name="removed_document_work_relieving[]" value="0" @if($alterationExistingRow) disabled @endif>
    </div>
    <div class="work-row-date-validation" aria-live="polite"></div>
</div>
