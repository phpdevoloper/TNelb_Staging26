@php
    use App\Support\FormSExperiencePartition;

    $expList = $workExperience ?? collect();
    if (is_array($expList)) {
        $expList = collect($expList);
    }

    $split = FormSExperiencePartition::splitBoardMember($expList);
    $standardRows = $split['standard'];
    $boardMemberRows = $split['boardMember'];
    $hideVoltageFields = !empty($hideVoltageFields);
@endphp
<style>
    .wx-renew-badge,
    .asp-renew-badge {
        display: inline-block;
        font-size: 0.65rem;
        font-weight: 700;
        padding: 0.1rem 0.45rem;
        border-radius: 4px;
        background: #ccfbf1;
        color: #0f766e;
        border: 1px solid #14b8a6;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        vertical-align: middle;
    }
    .wx-renewal-new-row > td {
        background: #f0fdfa !important;
        box-shadow: inset 3px 0 0 #14b8a6;
    }
</style>
<div class="work-exp-admin-readonly comp_certificate">
    @include('user_login.partials.form-s-work-exp-view', ['exp_details' => $standardRows, 'hideVoltageFields' => $hideVoltageFields])
</div>
@include('user_login.partials.form-s-board-member-view', ['boardMemberRows' => $boardMemberRows])
