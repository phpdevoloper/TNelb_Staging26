{{-- Shared §7b Representing Organisation dropdown (date-dependent from cc_board_details). --}}
@php
    $orgName = (string) ($orgName ?? '');
    $required = array_key_exists('required', get_defined_vars()) ? (bool) $required : true;
    $disabled = ! empty($disabled);
    $hasValidMeetingDate = ! empty($hasValidMeetingDate);
    $representingOrgOptions = collect($boardRepresentingOrgs ?? [])
        ->filter(fn ($v) => (string) $v !== '')
        ->mapWithKeys(fn ($v) => [(string) $v => (string) $v])
        ->all();
    $orgSelectDisabled = $disabled || ! $hasValidMeetingDate;
    /* Pick the option matching the saved value ignoring case / extra spaces (as CC_Board_Details::findOrganisation). */
    $selectedOrg = $orgName !== ''
        ? \App\Models\CC_Board_Details::findOrganisation($representingOrgOptions, $orgName)
        : null;
    $selectedOrg = $selectedOrg ?? $orgName;
@endphp
<select
    class="form-control work-employer-input"
    name="work_employer_name[]"
    autocomplete="off"
    aria-label="Representing Organisation"
    @if($required) required @endif
    @if($orgSelectDisabled) disabled @endif
>
    @if (! $hasValidMeetingDate)
        <option value="">Select date first</option>
    @else
        <option value="">Select organisation</option>
        @foreach ($representingOrgOptions as $orgValue => $orgLabel)
            <option value="{{ $orgValue }}" {{ $selectedOrg === (string) $orgValue ? 'selected' : '' }}>{{ $orgLabel }}</option>
        @endforeach
        @if ($orgName !== '' && ! array_key_exists($selectedOrg, $representingOrgOptions))
            <option value="{{ $orgName }}" selected>{{ $orgName }}</option>
        @endif
    @endif
</select>
