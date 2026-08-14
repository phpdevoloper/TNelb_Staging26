{{-- Form S §7b only: board-member work row (not shared with §7a). --}}
@php
    $hasRow = isset($expRow) && $expRow;
    $orgName = $hasRow ? (string) ($expRow->org_name ?? $expRow->company_name ?? '') : '';
    if ($hasRow && $orgName === '' && ! empty($expRow->emp_cate)) {
        $orgName = (string) $expRow->emp_cate;
    }
    $orgAddress = $hasRow ? (string) ($expRow->org_address ?? '') : '';
    $designation = $hasRow ? (string) ($expRow->designation ?? '') : '';
    $totalExp = $hasRow ? (string) ($expRow->total_exp ?? $expRow->experience ?? '') : '';
    $supportDoc = $hasRow ? (string) ($expRow->support_document ?? $expRow->upload_document ?? '') : '';
    $relieveDoc = $hasRow ? (string) ($expRow->releive_document ?? $expRow->relieve_document ?? '') : '';
    $workId = $hasRow ? (string) ($expRow->exp_id ?? $expRow->id ?? '') : '';
    $meetingDetails = $hasRow ? (string) ($expRow->board_meeting_details ?? '') : '';
    $meetingDate = ($hasRow && ! empty($expRow->board_meeting_date))
        ? calendar_date_ymd($expRow->board_meeting_date)
        : '';
    $boardMeetingMaster = collect($boardMeetingMaster ?? []);
    $meetingNosForDate = $meetingDate !== ''
        ? $boardMeetingMaster
            ->filter(fn ($r) => (string) ($r['bm_date'] ?? '') === $meetingDate)
            ->pluck('bm_no')
            ->map(fn ($n) => (string) $n)
            ->unique()
            ->values()
            ->all()
        : [];
    if ($meetingDetails !== '' && ! in_array((string) $meetingDetails, $meetingNosForDate, true)) {
        $meetingNosForDate[] = (string) $meetingDetails;
    }
    /* Org options only for the selected meeting date (never the full master list). */
    $boardRepresentingOrgs = $meetingDate !== '' && count($meetingNosForDate) > 0
        ? $boardMeetingMaster
            ->filter(fn ($r) => (string) ($r['bm_date'] ?? '') === $meetingDate)
            ->pluck('bm_member')
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all()
        : [];
    $hasValidMeetingDate = $meetingDate !== '' && count($meetingNosForDate) > 0;
    $rowIndex = $rowIndex ?? 0;
    $hideUploadWhenDocExists = ! empty($hideUploadWhenDocExists);
    $alterationExistingRow = ! empty($alterationExistingRow);
    $hideSupportUpload = $hideUploadWhenDocExists && $supportDoc !== '';
    $storedRowClass = $workId !== '' ? ' is-complete work-row--expanded work-row--board-member' : ' work-row--board-member';
    if ($alterationExistingRow) {
        $storedRowClass .= ' fs-alt-existing-work';
    }
@endphp
<div class="work-entry-block">
<div class="work-fields work-row{{ $storedRowClass }}" data-row-index="{{ $rowIndex }}">
    {{-- Always board member for §7b; hidden so gate UI owns the question. --}}
    {{-- Sync field only; apply7bBoardToggle enables + requires it when Board = Yes. --}}
    <select class="form-control work-employment-type d-none" name="work_employment_type[]" aria-hidden="true" tabindex="-1">
        <option value="board_member_tnelb" selected>Board member of TNELB or Ex board member of TNELB</option>
    </select>

    {{-- Keep parallel [] indexes for fields §7a rows post; unused in §7b UI. --}}
    <input type="hidden" class="work-contractor-category-sync" name="work_contractor_category[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-licence-number-sync" name="work_licence_number[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-nature" name="work_nature_of_work[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-voltage" name="work_voltage_level[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-transformer-kva" name="work_transformer_kva[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-date-from" name="work_date_from[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-date-to" name="work_date_to[]" value="" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-date-till-hidden" name="work_to_till_date[]" value="0" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" class="work-duration-y" value="">
    <input type="hidden" class="work-duration-m" value="">
    <input type="hidden" class="work-duration-d" value="">

    <div class="work-row-grid row g-2">
        <div class="work-board-member-panel col-12">
            <div class="row g-2">
                <div class="col-12 col-md-4 work-card-field work-board-meeting-field" data-field="board-meeting-date">
                    <label class="work-card-field-label">Date of Meeting <span class="req">*</span></label>
                    <input type="date" class="form-control work-board-meeting-date" name="work_board_meeting_date[]" value="{{ $meetingDate }}" title="Date of Meeting" aria-label="Date of board meeting attended" required data-raw="{{ $meetingDate }}">
                    @if ($meetingDate !== '' && count($meetingNosForDate) === 0)
                        <span class="error-message text-danger d-block mt-1 work-board-meeting-date-error" role="alert">No meeting for this date</span>
                    @endif
                </div>
                <div class="col-12 col-md-4 work-card-field work-board-meeting-field" data-field="board-meeting-details">
                    <label class="work-card-field-label">Details of the meeting attended <span class="req">*</span></label>
                    <select class="form-control work-board-meeting-details" name="work_board_meeting_details[]" autocomplete="off" required @if($meetingDate === '' || count($meetingNosForDate) === 0) disabled @endif>
                        @if ($meetingDate === '' || count($meetingNosForDate) === 0)
                            <option value="">Select date first</option>
                        @else
                            <option value="">Select</option>
                            @foreach ($meetingNosForDate as $meetingOpt)
                                <option value="{{ $meetingOpt }}" {{ (string) $meetingDetails === (string) $meetingOpt ? 'selected' : '' }}>{{ $meetingOpt }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-12 col-md-4 work-card-field" data-field="organisation">
                    <label class="work-card-field-label">Representing Organisation<span class="req">*</span></label>
                    @include('user_login.partials.form-s-7b-representing-org-select', [
                        'orgName' => $orgName,
                        'required' => true,
                        'disabled' => $alterationExistingRow,
                        'boardRepresentingOrgs' => $boardRepresentingOrgs,
                        'hasValidMeetingDate' => $hasValidMeetingDate,
                    ])
                </div>
                <div class="col-12 col-md-4 work-card-field" data-field="organisation-address">
                    <label class="work-card-field-label">Address <span class="req">*</span></label>
                    <textarea class="form-control work-org-address" name="work_organisation_address[]" rows="3" maxlength="255" autocomplete="off" required placeholder="Street, City, State, PIN">{{ $orgAddress }}</textarea>
                </div>
                <div class="col-12 col-md-4 work-card-field" data-field="designation">
                    <label class="work-card-field-label">Designation <span class="req">*</span></label>
                    <input type="text" class="form-control work-designation" name="designation[]" maxlength="80" autocomplete="off" required placeholder="e.g. Site Engineer" value="{{ $designation }}">
                </div>
                <div class="col-12 col-md-4 work-card-field" data-field="support-doc">
                    <label class="work-card-field-label">Supporting docs</label>
                    @if ($supportDoc !== '')
                        <div class="work-doc-existing mb-1 text-center">
                            <a class="text-primary" href="{{ competency_document_url($supportDoc, 'experience', (int) ($expRow->id ?? $expRow->exp_id ?? 0), 'experience_doc') }}" target="_blank" rel="noopener">
                                <i class="fa fa-file-pdf-o" style="color:#d9534f;"></i> View Document
                            </a>
                            <button type="button" class="btn btn-sm btn-danger ml-1 remove-work-doc-confirm" data-doc-kind="support">Remove</button>
                        </div>
                    @endif
                    <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined{{ $hideSupportUpload ? ' d-none work-upload-hidden-until-remove' : '' }}" data-upload-kind="work" data-doc-field="support">
                        <input class="form-control work-doc-input" name="work_document[]" type="file" accept=".pdf,application/pdf,.jpg,.jpeg,.png,image/jpeg,image/png">
                    </div>
                    <span class="work-card-field-hint{{ $hideSupportUpload ? ' d-none work-upload-hint-hidden-until-remove' : '' }}"><i class="fa fa-info-circle"></i> PDF / JPG / PNG, 5-200 KB</span>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" class="work-experience-total-hidden" name="work_experience_total[]" value="{{ $totalExp }}" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" name="work_level[]" class="work-level-sync" value="{{ $orgName }}" tabindex="-1" aria-hidden="true" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" name="experience[]" class="experience-sync" value="{{ $totalExp }}" tabindex="-1" aria-hidden="true" @if($alterationExistingRow) disabled @endif>
    <input type="hidden" name="work_exp_section[]" value="current" @if($alterationExistingRow) disabled @endif>
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
