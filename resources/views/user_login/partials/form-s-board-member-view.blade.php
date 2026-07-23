@php
    $boardMemberRows = $boardMemberRows ?? collect();
    if (is_array($boardMemberRows)) {
        $boardMemberRows = collect($boardMemberRows);
    }
    $isBoardMember = $boardMemberRows->isNotEmpty();
@endphp
<div class="board-member-qa-block">
    <div class="board-member-qa-head">
        <h6 class="asp-section-title">Board Member / Ex-Board Member of TNELB</h6>
        <span class="asp-qa-answer {{ $isBoardMember ? 'is-yes' : 'is-no' }}">
            {{ $isBoardMember ? 'Yes' : 'No' }}
        </span>
    </div>
    @if($isBoardMember)
        @foreach($boardMemberRows as $expRow)
            @php
                $isAltered = !empty($expRow->is_alteration_new);
                $meetingDetails = trim((string) ($expRow->board_meeting_details ?? ''));
                $meetingDate = $expRow->board_meeting_date ?? null;
                $supportDoc = (string) ($expRow->support_document ?? $expRow->upload_document ?? '');
                $supportDocUrl = !empty($expRow->support_document_url)
                    ? $expRow->support_document_url
                    : ($supportDoc !== '' ? competency_document_url($supportDoc, 'experience', (int) ($expRow->exp_id ?? 0), 'experience_doc') : null);
                $fromIso = $expRow->from_date ? \Carbon\Carbon::parse($expRow->from_date)->format('Y-m-d') : '';
                $toIso = $expRow->to_date ? \Carbon\Carbon::parse($expRow->to_date)->format('Y-m-d') : '';
                $isTill = $fromIso !== '' && $toIso === '';
                $periodText = '';
                if ($fromIso !== '') {
                    $periodText = 'From ' . format_date($fromIso);
                    if ($isTill) {
                        $periodText .= ' — Till date';
                    } elseif ($toIso !== '') {
                        $periodText .= ' — ' . format_date($toIso);
                    }
                }
            @endphp
            <div class="board-member-detail-wrap">
                @if($isAltered)
                    <div class="board-member-detail-alter-flag">
                        <span class="asp-alter-badge">ALTER</span>
                    </div>
                @endif
                <table class="table table-sm no-border-table board-member-detail-table">
                    <tbody>
                        <tr>
                            <th>Representing Organisation</th>
                            <td>{{ trim((string) ($expRow->org_name ?? $expRow->company_name ?? '')) !== '' ? ($expRow->org_name ?? $expRow->company_name) : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Details of the meeting</th>
                            <td>{{ $meetingDetails !== '' ? $meetingDetails : '—' }}</td>
                        </tr>
                        <tr>
                            <th>Date of meeting</th>
                            <td>{{ !empty($meetingDate) ? format_date($meetingDate) : '—' }}</td>
                        </tr>
                        @if($periodText !== '')
                        <tr>
                            <th>Period</th>
                            <td>{{ $periodText }}</td>
                        </tr>
                        @endif
                        <tr>
                            <th>Supporting document</th>
                            <td>
                                @if($supportDocUrl)
                                    <a href="{{ $supportDocUrl }}" target="_blank" rel="noopener noreferrer" class="doc-pdf-link text-primary" title="View document">
                                        <i class="fa fa-file-pdf-o text-danger"></i>
                                        <span>View Document</span>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif
</div>
