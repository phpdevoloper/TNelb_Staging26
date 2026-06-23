<tr class="education-fields">
    @if($row)
        <input type="hidden" name="education_id[{{ $index }}]" value="{{ $row->id }}">
    @endif
    <td><input type="text" name="education_level[{{ $index }}]" class="form-control form-control-sm" value="{{ old('education_level.'.$index, $row->education_level ?? '') }}"></td>
    <td><input type="text" name="institution_name[{{ $index }}]" class="form-control form-control-sm" value="{{ old('institution_name.'.$index, $row->institution_name ?? '') }}"></td>
    <td><input type="text" name="year_of_passing[{{ $index }}]" class="form-control form-control-sm" value="{{ old('year_of_passing.'.$index, $row->year_of_passing ?? '') }}"></td>
    <td><input type="text" name="grade[{{ $index }}]" class="form-control form-control-sm" value="{{ old('grade.'.$index, $row->grade ?? '') }}"></td>
    <td>
        @php
            $pendingKey = $row ? 'c_education:' . $row->id : null;
            $pendingReq = ($pendingAlterations ?? collect())[$pendingKey] ?? null;
        @endphp
        <input type="file" name="education_file[{{ $index }}]" class="form-control-file form-control-sm">
        @if($row?->file_name && !$pendingReq)
            <small class="text-success d-block mt-1">Active: {{ $row->file_name }}</small>
            <input type="text" class="form-control form-control-sm mt-1" name="education_alteration_reason[{{ $index }}]"
                   value="{{ old('education_alteration_reason.'.$index) }}"
                   placeholder="Alteration reason (required if replacing file)" maxlength="1000">
        @elseif($pendingReq)
            <small class="text-warning d-block mt-1">
                Pending alteration
                · <a href="{{ route('without-tmp.review.show', $pendingReq->id) }}">Review</a>
            </small>
        @elseif($row)
            <small class="text-muted d-block mt-1">Initial upload</small>
        @else
            <small class="text-muted d-block mt-1">Initial upload</small>
        @endif
    </td>
    <td class="text-center align-middle">
        @if($row)
            <button type="submit" class="btn btn-danger btn-sm" form="delete-education-{{ $row->id }}"
                    onclick="return confirm('Delete this education row?');" title="Delete saved row">
                <i class="fa fa-trash-o"></i>
            </button>
        @else
            <button type="button" class="btn btn-danger btn-sm btn-remove-education" title="Remove row">
                <i class="fa fa-trash-o"></i>
            </button>
        @endif
    </td>
</tr>
