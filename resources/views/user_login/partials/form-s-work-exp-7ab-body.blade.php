@php
    use App\Support\FormSExperiencePartition;

    $exp_details = $exp_details ?? collect();
    $previousMaxRows = (int) ($previousMaxRows ?? 7);
    $showBoardMemberEmploymentType7a = false;

    $partition = FormSExperiencePartition::partition($exp_details);
    $previousExpDetails = $partition['previous'];
    $currentExpDetails = $partition['current'];
    $is7bBoardMemberPrefill = $partition['is7bBoardMemberPrefill'];
    $hideUploadWhenDocExists = !empty($hideUploadWhenDocExists);
    $isAlterationMode = !empty($isAlterationMode);
@endphp

<div class="fs-question-part">
    <div class="fs-question-part-hd">
        <span class="fs-section-num fs-section-num--sub">7a</span>
        <div>
            <div class="fs-section-title">Previous Work Experience</div>
            <div class="fs-section-tamil">முந்தைய பணி அனுபவ விவரங்கள்</div>
        </div>
    </div>
    @include('user_login.partials.form-s-work-exp-section', [
        'exp_details' => $previousExpDetails,
        'showBoardMemberEmploymentType' => $showBoardMemberEmploymentType7a,
        'workContainerId' => 'work-container-previous',
        'workAddBtnId' => 'work-exp-add-btn-previous',
        'workRowCountId' => 'work-exp-row-count-previous',
        'workSummaryTbodyId' => 'work-exp-summary-tbody-previous',
        'workMaxRows' => $previousMaxRows,
        'workMinRows' => $isAlterationMode ? 0 : 1,
        'workPart' => 'previous',
        'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
        'isAlterationMode' => $isAlterationMode,
    ])
</div>

<div class="fs-question-part fs-question-part--7b {{ $is7bBoardMemberPrefill ? 'fs-7b-mode-board' : 'fs-7b-mode-standard' }}" id="fs-7b-root">
    <div class="fs-question-part-hd fs-7b-hd">
        <span class="fs-section-num fs-section-num--sub">7b</span>
        <div class="fs-7b-hd-content">
            <div class="fs-7b-board-gate-row" role="group" aria-labelledby="fs-7b-board-gate-label">
                <div class="fs-7b-board-gate-label" id="fs-7b-board-gate-label">
                    <div class="fs-section-title">
                        Are you a Board member of TNELB or Ex board member of TNELB?
                        <span class="section-req">*</span>
                    </div>
                    <div class="fs-section-tamil">தமிழ்நாடு மின்சார வாரிய கோப்புறை / முன்னாள் கோப்புறை உறுப்பினரா?</div>
                </div>
                <div class="fs-segmented-toggle fs-7b-board-toggle" role="radiogroup" aria-label="Board member of TNELB or Ex board member">
                    <label class="fs-segmented-opt{{ $is7bBoardMemberPrefill ? '' : ' is-active' }}">
                        <input type="radio" name="current_work_board_member" id="current_work_board_member_no" value="no"{{ $is7bBoardMemberPrefill ? '' : ' checked' }}>
                        <span>No</span>
                    </label>
                    <label class="fs-segmented-opt{{ $is7bBoardMemberPrefill ? ' is-active' : '' }}">
                        <input type="radio" name="current_work_board_member" id="current_work_board_member_yes" value="yes"{{ $is7bBoardMemberPrefill ? ' checked' : '' }}>
                        <span>Yes</span>
                    </label>
                </div>
            </div>
        </div>
    </div>

    <div id="fs-7b-board-details" class="fs-7b-board-details{{ $is7bBoardMemberPrefill ? '' : ' d-none' }}">
        <div id="fs-7b-work-wrap" class="fs-7b-work-wrap">
            @include('user_login.partials.form-s-work-exp-section', [
                'exp_details' => $currentExpDetails,
                'workContainerId' => 'work-container-current',
                'workAddBtnId' => 'work-exp-add-btn-current',
                'workRowCountId' => 'work-exp-row-count-current',
                'workSummaryTbodyId' => 'work-exp-summary-tbody-current',
                'workMaxRows' => 1,
                'workMinRows' => 1,
                'workPart' => 'current',
                'showSummaryPanel' => false,
                'showAddRow' => false,
                'showBoardMemberEmploymentType' => true,
                'defaultTillDate' => true,
                'hideDuration' => true,
                'hideRemoveButton' => true,
                'hideBoardPanelNote' => true,
                'useBootstrapGrid' => true,
                'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
                'isAlterationMode' => $isAlterationMode,
            ])
        </div>
    </div>
</div>
