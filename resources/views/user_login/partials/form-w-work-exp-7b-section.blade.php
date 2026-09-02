{{-- Form W §7b: one-time board-member form (never a multi-row experience list). --}}
@php
    use App\Models\CC_Board_Details;
    use App\Support\FormSExperiencePartition;

    $exp_details = collect($exp_details ?? []);
    $hideUploadWhenDocExists = ! empty($hideUploadWhenDocExists);
    $isAlterationMode = ! empty($isAlterationMode);
    $lockExistingRows = ! empty($lockExistingRows) || $isAlterationMode;

    // Prefer an explicit board-member row; never render more than one §7b form.
    $boardRow = $exp_details->first(fn ($row) => FormSExperiencePartition::isBoardMemberRow($row));

    $boardMeetingMaster = CC_Board_Details::masterForFormS7b();
@endphp
<script type="application/json" id="fs-7b-board-master-json">@json($boardMeetingMaster)</script>
<div class="work-exp-wrap" data-work-part="current">
    <div class="work-rows js-work-container"
        id="work-container-current"
        data-work-part="current"
        data-min-rows="1"
        data-max-rows="1">
        @include('user_login.partials.form-w-work-exp-7b-row', [
            'expRow' => $boardRow,
            'rowIndex' => 0,
            'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
            'alterationExistingRow' => $lockExistingRows && $boardRow,
            'boardMeetingMaster' => $boardMeetingMaster,
        ])
    </div>
</div>
