@php
    $selectedEmpType = $selectedEmpType ?? '';
    $showBoardMemberEmploymentType = $showBoardMemberEmploymentType ?? false;
@endphp
<option value="" disabled {{ $selectedEmpType === '' ? 'selected' : '' }}>Select type</option>
<option value="private_organisation" {{ $selectedEmpType === 'private_organisation' ? 'selected' : '' }}>Private organization</option>
<option value="electrical_contractor" {{ $selectedEmpType === 'electrical_contractor' ? 'selected' : '' }}>Electrical Contractor</option>
<option value="retired_employee" {{ $selectedEmpType === 'retired_employee' ? 'selected' : '' }}>Retired Employee</option>
<option value="govt_organisation" {{ $selectedEmpType === 'govt_organisation' ? 'selected' : '' }}>Government Organization</option>
<option value="apprenticeship" {{ $selectedEmpType === 'apprenticeship' ? 'selected' : '' }}>Apprenticeship</option>
@if ($showBoardMemberEmploymentType)
<option value="board_member_tnelb" {{ $selectedEmpType === 'board_member_tnelb' ? 'selected' : '' }}>Board member of TNELB or Ex board member of TNELB</option>
@endif
