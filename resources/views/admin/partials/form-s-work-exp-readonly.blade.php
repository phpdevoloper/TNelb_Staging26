@php
    use App\Support\FormSExperiencePartition;

    $expList = $workExperience ?? collect();
    if (is_array($expList)) {
        $expList = collect($expList);
    }

    $split = FormSExperiencePartition::splitBoardMember($expList);
    $standardRows = $split['standard'];
    $boardMemberRows = $split['boardMember'];
@endphp
<div class="work-exp-admin-readonly comp_certificate">
    @include('user_login.partials.form-s-work-exp-view', ['exp_details' => $standardRows])
</div>
@include('user_login.partials.form-s-board-member-view', ['boardMemberRows' => $boardMemberRows])
