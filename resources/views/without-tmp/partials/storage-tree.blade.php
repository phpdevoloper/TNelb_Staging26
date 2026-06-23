@props(['nodes', 'depth' => 0])

<ul class="list-unstyled {{ $depth === 0 ? '' : 'pl-3 border-left' }}">
    @foreach($nodes as $node)
        <li class="py-1">
            @if($node['type'] === 'dir')
                <i class="fa fa-folder text-warning"></i> <strong>{{ $node['name'] }}/</strong>
                @if(!empty($node['children']))
                    @include('without-tmp.partials.storage-tree', ['nodes' => $node['children'], 'depth' => $depth + 1])
                @endif
            @else
                <i class="fa fa-file-o"></i>
                <a href="{{ route('without-tmp.download', ['path' => $node['path'], 'name' => $node['name']]) }}" target="_blank">{{ $node['name'] }}</a>
                <span class="text-muted small">({{ number_format($node['size'] / 1024, 1) }} KB)</span>
            @endif
        </li>
    @endforeach
</ul>
