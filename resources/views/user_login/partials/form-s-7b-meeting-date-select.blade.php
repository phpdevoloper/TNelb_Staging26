{{-- §7b Date of Meeting: dropdown of distinct bm_date values from cc_board_details. --}}
@php
    $meetingDate = (string) ($meetingDate ?? '');
    $alterationExistingRow = ! empty($alterationExistingRow);
    $meetingDateOptions = collect($boardMeetingMaster ?? [])
        ->pluck('bm_date')
        ->filter(fn ($d) => (string) $d !== '')
        ->map(fn ($d) => (string) $d)
        ->unique()
        ->sortDesc()
        ->values()
        ->all();
    if ($meetingDate !== '' && ! in_array($meetingDate, $meetingDateOptions, true)) {
        $meetingDateOptions[] = $meetingDate;
    }
    $format7bDateLabel = static function (string $ymd): string {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $ymd, $m)) {
            return $m[3].'-'.$m[2].'-'.$m[1];
        }

        return $ymd;
    };
@endphp
<select
    class="form-control work-board-meeting-date"
    name="work_board_meeting_date[]"
    title="Date of Meeting"
    aria-label="Date of board meeting attended"
    autocomplete="off"
    required
    data-raw="{{ $meetingDate }}"
    @if($alterationExistingRow) disabled @endif
>
    <option value="">Select date</option>
    @foreach ($meetingDateOptions as $dateOpt)
        <option value="{{ $dateOpt }}" {{ (string) $meetingDate === (string) $dateOpt ? 'selected' : '' }}>
            {{ $format7bDateLabel((string) $dateOpt) }}
        </option>
    @endforeach
</select>
