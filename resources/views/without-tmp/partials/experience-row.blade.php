<tr class="experience-fields">
    @if($row)
        <input type="hidden" name="experience_id[{{ $index }}]" value="{{ $row->id }}">
    @endif
    <td><input type="text" name="company_name[{{ $index }}]" class="form-control form-control-sm" value="{{ old('company_name.'.$index, $row->company_name ?? '') }}"></td>
    <td><input type="text" name="years_of_experience[{{ $index }}]" class="form-control form-control-sm" value="{{ old('years_of_experience.'.$index, $row->years_of_experience ?? '') }}"></td>
    <td><input type="text" name="designation[{{ $index }}]" class="form-control form-control-sm" value="{{ old('designation.'.$index, $row->designation ?? '') }}"></td>
    <td>
        @php
            $pendingKey = $row ? 'c_experience:' . $row->id : null;
            $pendingReq = ($pendingAlterations ?? collect())[$pendingKey] ?? null;
        @endphp
        <input type="file" name="experience_file[{{ $index }}]" class="form-control-file form-control-sm">
        @if($row?->file_name && !$pendingReq)
            <small class="text-success d-block mt-1">
                Active: {{ $row->file_name }}
            </small>
            <input type="text" class="form-control form-control-sm mt-1" name="experience_alteration_reason[{{ $index }}]"
                   value="{{ old('experience_alteration_reason.'.$index) }}"
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
            <button type="submit" class="btn btn-danger btn-sm" form="delete-experience-{{ $row->id }}"
                    onclick="return confirm('Delete this experience row?');" title="Delete saved row">
                <i class="fa fa-trash-o"></i>
            </button>
        @else
            <button type="button" class="btn btn-danger btn-sm btn-remove-experience" title="Remove row">
                <i class="fa fa-trash-o"></i>
            </button>
        @endif
    </td>
</tr>
