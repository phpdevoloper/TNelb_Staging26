@props(['versionId', 'label' => null, 'class' => ''])

<a href="{{ route('document-version.sample.download', $versionId) }}"
   class="{{ $class }}"
   target="_blank"
   rel="noopener noreferrer"
   title="Open document">
    {{ $label ?? $slot }}
</a>
