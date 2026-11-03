{{--
    Generic field wrapper: label + control slot + error placeholder.
    Used directly for composite controls (phone group, radio group, …) and
    re-used internally by <x-form-input>, <x-form-select>, <x-form-textarea>.

    @param string|null $name     Field name — doubles as the JS error hook (data-error).
    @param string|null $label    Visible label text.
    @param string|null $for      id of the control the label points at (defaults to $name).
    @param bool        $required Adds the red asterisk.
    @param string      $labelTag "label" or "span" (use span for radio/checkbox groups).
    @param bool        $error    Render the error placeholder.
--}}
@props([
    'name' => null,
    'label' => null,
    'for' => null,
    'required' => false,
    'labelTag' => 'label',
    'error' => true,
])

<div {{ $attributes->class(['tnelb-wiz-field']) }}>
    @if ($label)
        @if ($labelTag === 'label')
            <label for="{{ $for ?? $name }}">
                {{ $label }}@if ($required) <b aria-hidden="true">*</b>@endif
            </label>
        @else
            <span class="tnelb-wiz-label">
                {{ $label }}@if ($required) <b aria-hidden="true">*</b>@endif
            </span>
        @endif
    @endif

    {{ $slot }}

    @if ($error && $name)
        {{-- isset() guard keeps the component usable outside the web middleware group (PDF/mail views).
             The span must stay whitespace-free: .text-danger is display:block, so a stray
             space would reserve a line box and nudge the layout for every field. --}}
        @php($fieldError = isset($errors) ? $errors->first($name) : '')
        <span class="text-danger" id="{{ $name }}-error" data-error="{{ $name }}" role="alert" aria-live="polite">{{ $fieldError }}</span>
    @endif
</div>
