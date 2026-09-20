@php
    $pathColumns = ['file_path', 'old_file_path'];
@endphp
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex flex-wrap align-items-center">
        <span class="font-weight-bold">cc_doc_log</span>
        <span class="badge badge-light text-dark ml-2">{{ $docLogs->count() }} rows</span>
        <span class="small text-muted ml-2">Filtered by this application_id</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm table-dv mb-0">
                <thead>
                    <tr>
                        @forelse($docLogColumns as $column)
                            <th class="text-nowrap">{{ $column }}</th>
                        @empty
                            <th>cc_doc_log</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @forelse($docLogs as $row)
                        @php $rowArray = (array) $row; @endphp
                        <tr>
                            @foreach($docLogColumns as $column)
                                @php
                                    $value = $rowArray[$column] ?? null;
                                    $isPath = in_array($column, $pathColumns, true);
                                    $url = $isPath ? $pathUrl($value) : null;
                                @endphp
                                <td class="small {{ $isPath ? '' : 'text-nowrap' }}">
                                    @if($value === null || $value === '')
                                        —
                                    @elseif($isPath)
                                        @if($url)
                                            <a href="{{ $url }}" target="_blank" rel="noopener">View</a>
                                        @endif
                                        <div class="text-muted">{{ $value }}</div>
                                    @elseif(is_bool($value))
                                        {{ $value ? '1' : '0' }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($docLogColumns), 1) }}" class="text-center text-muted py-4">
                                No cc_doc_log rows for this application_id.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
