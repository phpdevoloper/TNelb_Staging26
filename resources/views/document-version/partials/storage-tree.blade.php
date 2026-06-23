@props(['nodes', 'depth' => 0, 'highlightFolder' => null, 'documentsByPath' => collect(), 'tempPrefix' => 'temp', 'permanentPrefix' => 'permanent'])

<ul class="storage-tree-list {{ $depth === 0 ? 'storage-tree-root' : '' }}">
    @foreach($nodes as $node)
        @php
            $isHighlight = $highlightFolder && (
                $node['name'] === $highlightFolder
                || str_contains($node['path'], '/' . $highlightFolder . '/')
                || str_contains($node['path'], $highlightFolder . '/')
            );
            $zone = str_starts_with($node['path'], $tempPrefix . '/')
                ? 'temp'
                : (str_starts_with($node['path'], $permanentPrefix . '/') ? 'permanent' : null);
        @endphp
        <li class="storage-tree-item storage-tree-depth-{{ $depth }} {{ $isHighlight ? 'storage-tree-highlight' : '' }}">
            @if($node['type'] === 'dir')
                <div class="storage-tree-row storage-tree-dir">
                    <i class="fa fa-folder text-warning mr-1"></i>
                    <strong>{{ $node['name'] }}/</strong>
                    @if($zone === 'temp')
                        <span class="badge badge-secondary badge-sm ml-1">TEMP</span>
                    @elseif($zone === 'permanent')
                        <span class="badge badge-success badge-sm ml-1">PERMANENT</span>
                    @endif
                    <span class="text-muted small ml-2">{{ $node['path'] }}/</span>
                </div>
                @if(!empty($node['children']))
                    @include('document-version.partials.storage-tree', [
                        'nodes' => $node['children'],
                        'depth' => $depth + 1,
                        'highlightFolder' => $highlightFolder,
                        'documentsByPath' => $documentsByPath,
                        'tempPrefix' => $tempPrefix,
                        'permanentPrefix' => $permanentPrefix,
                    ])
                @endif
            @else
                @php $doc = $documentsByPath->get($node['path']); @endphp
                <div class="storage-tree-row storage-tree-file">
                    <i class="fa fa-file-pdf-o text-danger mr-1"></i>
                    @if($doc)
                        <a href="{{ route('document-version.sample.download', $doc->id) }}"
                           target="_blank" rel="noopener noreferrer">{{ $node['name'] }}</a>
                    @else
                        <span>{{ $node['name'] }}</span>
                        <span class="badge badge-light badge-sm ml-1">orphan file</span>
                    @endif
                    <span class="text-muted small ml-2">{{ number_format($node['size'] / 1024, 1) }} KB</span>
                    <span class="text-muted small ml-2">{{ date('d M Y H:i', $node['modified']) }}</span>
                </div>
            @endif
        </li>
    @endforeach
</ul>

@once
@push('styles')
<style>
    .storage-tree-list { list-style: none; padding-left: 0; margin-bottom: 0; }
    .storage-tree-root > .storage-tree-item { border-bottom: 1px solid #eee; }
    .storage-tree-root > .storage-tree-item:last-child { border-bottom: none; }
    .storage-tree-list:not(.storage-tree-root) { padding-left: 1.25rem; border-left: 2px solid #e9ecef; margin-left: .35rem; margin-top: .25rem; }
    .storage-tree-row { padding: .35rem 0; font-size: .875rem; word-break: break-all; }
    .storage-tree-highlight > .storage-tree-row { background: #e8f4fd; margin: 0 -.5rem; padding-left: .5rem; padding-right: .5rem; border-radius: .25rem; }
    .storage-tree-dir { font-weight: 500; }
</style>
@endpush
@endonce
