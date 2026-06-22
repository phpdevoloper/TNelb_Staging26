<tr class="education-fields">
    <td class="edu-serial text-center align-middle">{{ $serial ?? 1 }}</td>
    <td>
        <input type="hidden" name="education_id[]" value="{{ $row['id'] ?? '' }}">
        <select class="form-control form-control-sm" name="education_level[]">
            <option value="">Select Education</option>
            @php $level = $row['education_level'] ?? ''; @endphp
            <option value="DEE" @selected($level === 'DEE')>Diploma (Electrical Engineering)</option>
            <option value="BEE" @selected($level === 'BEE')>B.E (Electrical Engineering)</option>
            <option value="MEE" @selected($level === 'MEE')>M.E (Electrical Engineering)</option>
            <option value="AMIE" @selected($level === 'AMIE')>A pass in AMIE</option>
            <option value="OTHER" @selected($level === 'OTHER')>Other</option>
        </select>
    </td>
    <td>
        <input type="text" class="form-control form-control-sm" name="institution_name[]"
               value="{{ $row['institution_name'] ?? '' }}" placeholder="Institution / School">
    </td>
    <td>
        <select class="form-control form-control-sm" name="year_of_passing[]">
            <option value="">Select Year</option>
            @php
                $cert = $row['certificate_no'] ?? '';
                $yearVal = '';
                $gradeVal = '';
                if (preg_match('/^(\d{4})/', $cert, $m)) {
                    $yearVal = $m[1];
                }
                if (preg_match('/\|\s*(.+)$/', $cert, $gm)) {
                    $gradeVal = trim($gm[1]);
                }
            @endphp
            @for ($y = $currentYear; $y >= 1980; $y--)
                <option value="{{ $y }}" @selected((string) $yearVal === (string) $y)>{{ $y }}</option>
            @endfor
        </select>
    </td>
    <td>
        <input type="text" class="form-control form-control-sm" name="percentage_grade[]"
               placeholder="e.g. 85%" value="{{ $gradeVal }}">
        <input type="hidden" name="certificate_no[]" value="{{ $row['certificate_no'] ?? '' }}">
    </td>
    <td>
        @include('document-version.partials.document-upload-cell', [
            'row' => $row,
            'requestContext' => $requestContext ?? 'NEW',
            'fileInputName' => 'education_document[]',
            'reasonInputName' => 'education_alteration_reason[' . ($index ?? 0) . ']',
        ])
    </td>
    <td class="text-center align-middle">
        @if(!empty($row['id']))
            <button type="submit" class="btn btn-danger btn-sm" form="delete-education-{{ $row['id'] }}"
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
