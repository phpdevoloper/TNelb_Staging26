<tr class="experience-fields">
    <td class="exp-serial text-center align-middle">{{ $serial ?? 1 }}</td>
    <td>
        <input type="hidden" name="experience_id[]" value="{{ $row['id'] ?? '' }}">
        <input type="text" class="form-control form-control-sm" name="company_name[]"
               value="{{ $row['company_name'] ?? '' }}" placeholder="Company / Contractor">
    </td>
    <td>
        @php
            $designation = $row['designation'] ?? '';
            $years = '';
            if (preg_match('/\(([\d.]+)\s*yrs\)$/i', $designation, $m)) {
                $years = $m[1];
                $designation = trim(preg_replace('/\s*\([\d.]+\s*yrs\)$/i', '', $designation));
            }
        @endphp
        <input type="text" class="form-control form-control-sm" name="years_of_experience[]"
               value="{{ $years }}" placeholder="Years">
    </td>
    <td>
        <input type="text" class="form-control form-control-sm" name="designation[]"
               value="{{ $designation }}" placeholder="Designation">
    </td>
    <td>
        @include('document-version.partials.document-upload-cell', [
            'row' => $row,
            'requestContext' => $requestContext ?? 'NEW',
            'fileInputName' => 'experience_document[]',
            'reasonInputName' => 'experience_alteration_reason[' . ($index ?? 0) . ']',
        ])
    </td>
    <td class="text-center align-middle">
        @if(!empty($row['id']))
            <button type="submit" class="btn btn-danger btn-sm" form="delete-experience-{{ $row['id'] }}"
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
