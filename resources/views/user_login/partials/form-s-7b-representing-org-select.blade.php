{{-- Shared §7b Representing Organisation dropdown (temporary sample options). --}}
@php
    $orgName = (string) ($orgName ?? '');
    $required = array_key_exists('required', get_defined_vars()) ? (bool) $required : true;
    $disabled = ! empty($disabled);
    $representingOrgOptions = [
        'TANGEDCO' => 'TANGEDCO',
        'TANTRANSCO' => 'TANTRANSCO',
        'TNEB Limited' => 'TNEB Limited',
        'Tamil Nadu Generation and Distribution Corporation' => 'Tamil Nadu Generation and Distribution Corporation',
        'Private Contractor / Firm' => 'Private Contractor / Firm',
        'Central Government PSU' => 'Central Government PSU',
        'State Government Department' => 'State Government Department',
    ];
@endphp
<select
    class="form-control work-employer-input"
    name="work_employer_name[]"
    autocomplete="off"
    aria-label="Representing Organisation"
    @if($required) required @endif
    @if($disabled) disabled @endif
>
    <option value="">Select organisation</option>
    @foreach ($representingOrgOptions as $orgValue => $orgLabel)
        <option value="{{ $orgValue }}" {{ (string) $orgName === (string) $orgValue ? 'selected' : '' }}>{{ $orgLabel }}</option>
    @endforeach
    @if ($orgName !== '' && ! array_key_exists($orgName, $representingOrgOptions))
        <option value="{{ $orgName }}" selected>{{ $orgName }}</option>
    @endif
</select>
