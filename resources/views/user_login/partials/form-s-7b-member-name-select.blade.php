{{-- §7b Name of member: bm_members from cc_board_details, filtered by date + organisation. --}}
@php
    $memberName = (string) ($memberName ?? '');
    $required = array_key_exists('required', get_defined_vars()) ? (bool) $required : true;
    $disabled = ! empty($disabled);
    $hasValidOrg = ! empty($hasValidOrg);
    $memberOptions = collect($boardMemberNames ?? [])
        ->filter(fn ($v) => (string) $v !== '')
        ->mapWithKeys(fn ($v) => [(string) $v => (string) $v])
        ->all();
    $memberSelectDisabled = $disabled || ! $hasValidOrg;
@endphp
<input type="hidden" class="work-board-member-name-sync" value="{{ $memberName }}" @if($alterationExistingRow ?? false) disabled @endif>
<select
    class="form-control work-board-member-name"
    name="work_board_member_name[]"
    autocomplete="off"
    aria-label="Name of member"
    @if($required) required @endif
    @if($memberSelectDisabled) disabled @endif
>
    @if (! $hasValidOrg)
        <option value="">Select organisation first</option>
    @else
        <option value="">Select member</option>
        @foreach ($memberOptions as $memberValue => $memberLabel)
            <option value="{{ $memberValue }}" {{ (string) $memberName === (string) $memberValue ? 'selected' : '' }}>{{ $memberLabel }}</option>
        @endforeach
        @if ($memberName !== '' && ! array_key_exists($memberName, $memberOptions))
            <option value="{{ $memberName }}" selected>{{ $memberName }}</option>
        @endif
    @endif
</select>
