{{--
    One <tr> of the Form S education table. Used by form-s-education-section for saved/blank rows
    and for the <template> that form_s_education.js clones when "Add row" is clicked, so the
    markup exists in exactly one place.

    Receives: $eduRow (object|null), $eduIdx (int, or '__IDX__' inside the template),
              $eduSerial (int|string) plus the section's $eduMode / $eduLevels / $eduTracksIds / ... (shared scope).
--}}
@php
    $eduSaved = $eduRow !== null;
    $eduLocked = $eduLockSaved && $eduSaved && (int) data_get($eduRow, 'id', 0) > 0;
    $eduLevel = data_get($eduRow, 'educational_level');
    $eduLevel = $eduLevel === 'EX' ? 'EXS' : $eduLevel; // legacy code for Ex Serviceman
    $eduDoc = data_get($eduRow, 'upload_document');
    $eduRowId = (int) data_get($eduRow, 'id', 0);
    $eduMonth = data_get($eduRow, 'month_of_passing') ?? data_get($eduRow, 'month_passing') ?? '';
    $eduMonth = $eduMonth !== '' ? str_pad((string) ((int) $eduMonth), 2, '0', STR_PAD_LEFT) : '';
    $eduYear = data_get($eduRow, 'year_of_passing');
    $eduFileName = $eduTracksIds ? 'education_document[' . $eduIdx . ']' : 'education_document[]';
@endphp
<tr class="education-fields {{ $eduTracksIds ? 'text-center' : '' }} {{ $eduLocked ? 'fs-renew-existing-edu' : '' }}"
    @if ($eduTracksIds) data-edu-index="{{ $eduIdx }}" @endif
    @if ($eduLocked) data-renew-locked="1" @endif>
    <td class="edu-serial text-center">{{ $eduSerial }}</td>
    <td>
        <select class="form-control" name="educational_level[]">
            {{-- Not disabled: a disabled selected option is dropped from the POST and shifts educational_level[] out of step with the other row arrays. --}}
            <option value="" {{ empty($eduLevel) ? 'selected' : '' }}>Select Education</option>
            @foreach ($eduLevels as $eduValue => $eduLabel)
                <option value="{{ $eduValue }}" {{ $eduLevel === $eduValue ? 'selected' : '' }}>{{ $eduLabel }}</option>
            @endforeach
        </select>
    </td>
    <td>
        <input type="text" class="form-control" name="institute_name[]"
            @unless ($eduTracksIds) maxlength="80" @endunless
            value="{{ data_get($eduRow, 'institute_name', '') }}">
    </td>
    <td>
        <select name="month_of_passing[]" class="form-control">
            <option value="">Select Month</option>
            @for ($m = 1; $m <= 12; $m++)
                @php $mv = str_pad((string) $m, 2, '0', STR_PAD_LEFT); @endphp
                <option value="{{ $mv }}" {{ $eduMonth === $mv ? 'selected' : '' }}>{{ date('M', mktime(0, 0, 0, $m, 1)) }}</option>
            @endfor
        </select>
    </td>
    <td>
        <select name="year_of_passing[]" class="form-control">
            {{-- Renewal/alteration use "" (browser "required" can block it, arrays stay aligned); new/digitization keep "0". --}}
            <option value="{{ $eduTracksIds ? '' : '0' }}" {{ empty($eduYear) ? 'selected' : '' }}>Select Year</option>
            @for ($year = $eduYearMax; $year >= 1980; $year--)
                <option value="{{ $year }}" {{ (string) $eduYear === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
            @endfor
        </select>
    </td>
    <td>
        <input type="text" class="form-control certificate-input" name="certificate_no[]" maxlength="20"
            @if (! $eduLocked) required @endif
            value="{{ data_get($eduRow, 'certificate_no') ?? data_get($eduRow, 'percentage', '') }}">
        <span class="error text-danger certificate-error" style="font-size:.75rem;"></span>
    </td>
    <td>
        @if ($eduTracksIds)
            <div class="file-section text-center">
                @if (! empty($eduDoc))
                    <div class="edu-doc-container d-flex align-items-center justify-content-center">
                        <a class="text-primary" target="_blank"
                            href="{{ competency_document_url($eduDoc, 'education', $eduRowId, 'certificate', $eduApplicationId > 0 ? [$eduApplicationId] : []) ?? '#' }}">
                            <i class="fa fa-file-pdf-o" style="color: red"></i> View Document
                        </a>
                        @if ($eduDocRemovable && ! $eduLocked && ! $eduRowsLocked)
                            <button type="button" class="btn btn-sm btn-danger ml-2 remove-doc_edu_confirm">Remove</button>
                        @endif
                    </div>
                    <div class="edu-doc-input d-none">
                        <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                            <input type="file" class="form-control education-file" name="{{ $eduFileName }}" accept=".pdf,application/pdf">
                        </div>
                    </div>
                @else
                    <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined" data-upload-kind="education">
                        <input type="file" class="form-control education-file" name="{{ $eduFileName }}" accept=".pdf,application/pdf">
                    </div>
                @endif
            </div>
        @else
            <div class="form-s-file-upload-wrap form-s-file-upload-wrap--combined">
                <input type="file" class="form-control education-file" name="{{ $eduFileName }}" accept=".pdf,application/pdf">
            </div>
        @endif
    </td>
    <td class="form-s-actions-cell text-center p-1">
        <div class="form-s-actions-stack">
            @if ($eduRowsLocked)
                {{-- Section locked (returned application): no remove button at all, not just CSS-disabled --}}
            @elseif ($eduLocked)
                {{-- Existing qualification cannot be removed on renewal --}}
                <span class="text-muted small" title="Existing qualification">—</span>
            @elseif ($eduHasDelete && $eduSaved && $eduRowId > 0)
                <button type="button" class="btn btn-danger btn-sm remove-education remove_edu py-1 px-2"
                    data-edu_id="{{ $eduRowId }}" data-url="{{ route('delete_education') }}" title="Remove row">
                    <i class="fa fa-trash-o"></i>
                </button>
            @elseif ($eduTracksIds)
                <button type="button" class="btn btn-danger btn-sm remove-education py-1 px-2" title="Remove row">
                    <i class="fa fa-trash-o"></i>
                </button>
            @else
                <button type="button" class="btn-tbl-remove remove-education py-1 px-2" title="Remove row">
                    <i class="fa fa-trash-o"></i>
                </button>
            @endif
        </div>
        @if ($eduTracksIds)
            {{-- Keep ids inside a cell: bare inputs between <tr>s are invalid table markup. --}}
            <input type="hidden" name="edu_id[]" value="{{ $eduSaved ? $eduRowId : '' }}">
            <input type="hidden" name="existing_document[]" value="{{ $eduDoc ?? '' }}">
            <input type="hidden" class="removed-document-edu" name="removed_document[]" value="0">
        @endif
    </td>
</tr>
