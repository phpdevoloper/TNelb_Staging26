{{--
    Reusable text-style input: label + control + error.

    <x-form-input name="pincode" label="Pincode" required inputmode="numeric"
                  maxlength="6" pattern="[0-9]{6}" data-filter="digits" />

    Any extra attribute (data-*, min, max, step, …) is forwarded to the <input>.
--}}
@props([
    'name',
    'label' => null,
    'id' => null,
    'type' => 'text',
    'value' => null,
    'placeholder' => null,
    'required' => false,
    'pattern' => null,
    'inputmode' => null,
    'maxlength' => null,
    'autocomplete' => 'off',
    'icon' => null,
    'fieldClass' => null,
])

@php($controlId = $id ?? $name)

<x-form-field :name="$name" :label="$label" :for="$controlId" :required="$required" :class="$fieldClass">
    @if ($icon)
        <div class="tnelb-input-icon">
            <i class="fa fa-{{ $icon }}" aria-hidden="true"></i>
    @endif

    <input
        id="{{ $controlId }}"
        name="{{ $name }}"
        type="{{ $type }}"
        value="{{ old($name, $value) }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required aria-required="true" @endif
        @if ($pattern) pattern="{{ $pattern }}" @endif
        @if ($inputmode) inputmode="{{ $inputmode }}" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        autocomplete="{{ $autocomplete }}"
        aria-describedby="{{ $name }}-error"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >

    @if ($icon)
        </div>
    @endif
</x-form-field>
