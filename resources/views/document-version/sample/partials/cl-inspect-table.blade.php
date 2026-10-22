@php
    $pathColumns = $pathColumns ?? [];
    $maskColumns = $maskColumns ?? [];
    $rows = $table['rows'] ?? collect();
    $columns = $table['columns'] ?? [];
    $emptyMessage = $emptyMessage ?? ('No rows for this application_id.');
@endphp
<div class="card shadow-sm mb-3">
    <div class="card-header bg-white d-flex flex-wrap align-items-center">
        <span class="font-weight-bold">{{ $title }}</span>
        <span class="badge badge-light text-dark ml-2">{{ $rows->count() }} rows</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm table-dv mb-0">
                <thead>
                    <tr>
                        @forelse($columns as $column)
                            <th class="text-nowrap">{{ $column }}</th>
                        @empty
                            <th>{{ $title }}</th>
                        @endforelse
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        @php $rowArray = (array) $row; @endphp
                        <tr>
                            @foreach($columns as $column)
                                @php
                                    $value = $rowArray[$column] ?? null;
                                    $isPath = in_array($column, $pathColumns, true)
                                        || preg_match('/(file|doc|path|proof|sign|pdf)$/i', (string) $column);
                                    $isMask = in_array($column, $maskColumns, true);
                                    $url = null;
                                    if ($isPath && $title === 'tnelb_temp_uploaded_documents') {
                                        $joined = trim((string) ($rowArray['file_path'] ?? ''), '/').'/'.ltrim((string) ($rowArray['file_name'] ?? ''), '/');
                                        $url = $column === 'file_name' ? $pathUrl($joined) : $pathUrl($value);
                                    } elseif ($isPath) {
                                        $url = $pathUrl($value);
                                    }
                                @endphp
                                <td class="small {{ $isPath ? '' : 'text-nowrap' }}">
                                    @if($value === null || $value === '')
                                        —
                                    @elseif($isMask)
                                        ••••
                                    @elseif($isPath)
                                        @if($url)
                                            <a href="{{ $url }}" target="_blank" rel="noopener">View</a>
                                        @endif
                                        <div class="text-muted">{{ $value }}</div>
                                    @elseif(is_bool($value))
                                        {{ $value ? '1' : '0' }}
                                    @else
                                        {{ is_scalar($value) ? $value : json_encode($value) }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ max(count($columns), 1) }}" class="text-center text-muted py-4">{{ $emptyMessage }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
