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
    $kva = $hasRow && $expRow->transformer_kva !== null && $expRow->transformer_kva !== '' ? $expRow->transformer_kva : '';
    $workFromDate = ($hasRow && $expRow->from_date) ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
    $workToDate = ($hasRow && $expRow->to_date) ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
    $isTill = $hasRow && $workFromDate !== '' && $workToDate === '';
    $totalExp = $hasRow ? (string) ($expRow->total_exp ?? $expRow->experience ?? '') : '';
    $durY = $hasRow && $expRow->total_y !== null ? (string) $expRow->total_y : '';
    $durM = $hasRow && $expRow->total_m !== null ? (string) $expRow->total_m : '';
    $durD = $hasRow && $expRow->total_d !== null ? (string) $expRow->total_d : '';
    $supportDoc = $hasRow ? (string) ($expRow->support_document ?? $expRow->upload_document ?? '') : '';
    $relieveDoc = $hasRow ? (string) ($expRow->releive_document ?? '') : '';
    $workId = $hasRow ? (string) ($expRow->id ?? '') : '';
    $rowIndex = $rowIndex ?? 0;
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
    $removeClasses = 'work-row-remove remove-work' . ($workId !== '' ? ' remove_exp' : '');
    $storedRowClass = $workId !== '' ? ' is-complete work-row--compact work-row--in-summary' : '';
@endphp
<div class="work-entry-block">
<div class="work-fields work-row{{ $storedRowClass }}" data-row-index="{{ $rowIndex }}">
    <div class="work-row-head" role="group">
        <span class="work-row-spacer"></span>
        <div class="work-row-head-actions">
            <button type="button" class="work-row-toggle-btn" aria-expanded="false" title="Expand to edit" aria-label="Expand entry to edit">
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button>
            <button type="button" class="{{ $removeClasses }} py-1 px-2"
                @if($workId !== '') data-exp_id="{{ $workId }}" data-url="{{ route('delete_experience') }}" @endif
                title="Remove this entry" aria-label="Remove this work experience entry">
                <i class="fa fa-trash-o" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <div class="work-row-grid">
        <div class="work-card-field">
            <label class="work-card-field-label">Employment Type <span class="req">*</span></label>
            <select class="form-control work-employment-type" name="work_employment_type[]" required>
                @include('user_login.partials.form-s-work-exp-employment-options', [
                    'selectedEmpType' => $empType,
                    'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType,
                ])
            </select>
        </div>
        <div class="work-card-field" data-field="contractor-cat">
            <label class="work-card-field-label">Grade of Licence<span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <select class="form-control work-contractor-cat" name="work_contractor_category[]" disabled>
                <option value="">—</option>
                @foreach (['ESA', 'EA', 'ESB', 'EB'] as $cat)
                    <option value="{{ $cat }}" {{ $contractorCat === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <span class="work-card-field-hint" data-hint="cat" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
        </div>
        <div class="work-card-field" data-field="licence-number">
            <label class="work-card-field-label">Licence <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <input type="text" class="form-control work-licence-number" name="work_licence_number[]" maxlength="40" autocomplete="off" disabled placeholder="e.g. ESA/12345" value="{{ $licenceNo }}">
            <span class="work-card-field-hint" data-hint="licence" style="display:none;"><i class="fa fa-info-circle"></i> Only for Electrical contractor</span>
        </div>
        <div class="work-card-field">
            <label class="work-card-field-label">Organisation <span class="req">*</span></label>
            <input type="text" class="form-control work-employer-input" name="work_employer_name[]" maxlength="120" autocomplete="off" disabled placeholder="Organisation name" value="{{ $orgName }}">
        </div>
        <div class="work-card-field">
            <label class="work-card-field-label">Address <span class="req">*</span></label>
            <input type="text" class="form-control work-org-address" name="work_organisation_address[]" maxlength="255" autocomplete="off" disabled placeholder="Street, City, State, PIN" value="{{ $orgAddress }}">
        </div>
        <div class="work-card-field">
            <label class="work-card-field-label">Designation <span class="req">*</span></label>
            <input type="text" class="form-control work-designation" name="designation[]" maxlength="80" autocomplete="off" disabled placeholder="e.g. Site Engineer" value="{{ $designation }}">
        </div>
        <div class="work-card-field" data-field="work-nature">
            <label class="work-card-field-label">Work Nature <span class="req">*</span></label>
            <select class="form-control work-nature" name="work_nature_of_work[]" disabled>
                <option value="">—</option>
                <option value="erection" {{ $nature === 'erection' ? 'selected' : '' }}>Erection</option>
                <option value="maintenance" {{ $nature === 'maintenance' ? 'selected' : '' }}>Maintenance</option>
                <option value="erection_maintenance" {{ $nature === 'erection_maintenance' ? 'selected' : '' }}>Erection &amp; Maintenance</option>
            </select>
        </div>
        <div class="work-card-field" data-field="voltage-level">
            <label class="work-card-field-label">Voltage Level <span class="req">*</span></label>
            <select class="form-control work-voltage" name="work_voltage_level[]" disabled>
                <option value="">—</option>
                <option value="up_to_650v" {{ $voltage === 'up_to_650v' ? 'selected' : '' }}>Up to 650V</option>
                <option value="650v_to_33kv" {{ $voltage === '650v_to_33kv' ? 'selected' : '' }}>Above 650V to 33KV</option>
                <option value="above_33kv" {{ $voltage === 'above_33kv' ? 'selected' : '' }}>Above 33KV</option>
            </select>
        </div>
        <div class="work-card-field" data-field="transformer-kva">
            <label class="work-card-field-label">Transformer kVA(max 1000kVA) <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <input type="number" class="form-control work-transformer-kva" name="work_transformer_kva[]" min="0" max="9999999" step="any" inputmode="decimal" autocomplete="off" disabled placeholder="e.g. 250" value="{{ $kva }}">
            <span class="work-card-field-hint" data-hint="kva" style="display:none;"><i class="fa fa-info-circle"></i> Not applicable for voltage up to 650V</span>
        </div>
        <div class="work-card-field">
            <label class="work-card-field-label">From date <span class="req">*</span></label>
            <input type="date" class="form-control work-date-from" name="work_date_from[]" value="{{ $workFromDate }}" title="From date" aria-label="Period of experience: from date" disabled>
        </div>
        <div class="work-card-field" data-field="to-date">
            <label class="work-card-field-label">To date <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            <input type="date" class="form-control work-date-to" name="work_date_to[]" value="{{ $workToDate }}" title="To date" aria-label="Period of experience: to date" disabled>
            <label class="work-card-till-toggle">
                <input type="checkbox" class="work-date-till" {{ $isTill ? 'checked' : '' }}>
                <span>Till date (currently working)</span>
            </label>
            <input type="hidden" class="work-date-till-hidden" name="work_to_till_date[]" value="{{ $isTill ? '1' : '0' }}">
        </div>
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
        </div>
        <div class="work-card-field">
            <label class="work-card-field-label">Supporting docs <span class="req">*</span></label>
            @if ($supportDoc !== '')
                <div class="work-doc-existing mb-1 text-center">
                    <a class="text-primary" href="{{ asset($supportDoc) }}" target="_blank" rel="noopener">
                        <i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View
                    </a>
                    <button type="button" class="btn btn-sm btn-danger ml-1 remove-work-doc-confirm" data-doc-kind="support">Remove</button>
                </div>
            @endif
            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                <input class="form-control work-doc-input" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
            </div>
            <span class="work-card-field-hint"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
        </div>
        <div class="work-card-field" data-field="relieve">
            <label class="work-card-field-label">Relieving Letter <span class="req">*</span> <span class="lock-icon" aria-hidden="true" style="display:none;"><i class="fa fa-lock"></i></span></label>
            @if ($relieveDoc !== '')
                <div class="work-relieve-existing mb-1 text-center">
                    <a class="text-primary" href="{{ asset($relieveDoc) }}" target="_blank" rel="noopener">
                        <i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View
                    </a>
                    <button type="button" class="btn btn-sm btn-danger ml-1 remove-work-relieve-confirm" data-doc-kind="relieve">Remove</button>
                </div>
            @endif
            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="work">
                <input class="form-control work-relieve-input" name="work_relieving_letter[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png" disabled>
            </div>
            <span class="work-card-field-hint" data-hint="relieve" style="display:none;"><i class="fa fa-info-circle"></i> Not required when "Till date" is selected</span>
            <span class="work-card-field-hint" data-hint="relieve-default"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
        </div>

        <div class="work-row-done-bar">
            <button type="button" class="work-row-done-btn" aria-label="Submit this entry and return to summary card">
                <i class="fa fa-check" aria-hidden="true"></i> Submit
            </button>
        </div>

        <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $totalExp }}">
        <input type="hidden" name="work_level[]" class="work-level-sync" value="{{ $orgName }}" tabindex="-1" aria-hidden="true">
        <input type="hidden" name="experience[]" class="experience-sync" value="{{ $totalExp }}" tabindex="-1" aria-hidden="true">
        <input type="hidden" name="work_id[]" value="{{ $workId }}">
        <input type="hidden" name="existing_work_document[]" value="{{ $supportDoc }}">
        <input type="hidden" name="existing_work_relieving_document[]" value="{{ $relieveDoc }}">
        <input type="hidden" name="removed_document_work[]" value="0">
        <input type="hidden" name="removed_document_work_relieving[]" value="0">
    </div>
</div>
<div class="work-row-date-validation" aria-live="polite"></div>
</div>
