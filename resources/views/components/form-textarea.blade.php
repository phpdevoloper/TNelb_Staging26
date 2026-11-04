{{--
    Reusable textarea: label + control + error.

    <x-form-textarea name="Address" label="Address" required rows="3"
                     placeholder="Enter your address" />
--}}
@props([
    'name',
    'label' => null,
    'id' => null,
    'value' => null,
    'rows' => 3,
    'placeholder' => null,
    'required' => false,
    'maxlength' => null,
    'fieldClass' => null,
])

@php($controlId = $id ?? $name)

<x-form-field :name="$name" :label="$label" :for="$controlId" :required="$required" :class="$fieldClass">
    <textarea
        id="{{ $controlId }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($placeholder) placeholder="{{ $placeholder }}" @endif
        @if ($required) required aria-required="true" @endif
        @if ($maxlength) maxlength="{{ $maxlength }}" @endif
        aria-describedby="{{ $name }}-error"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >{{ old($name, $value) }}</textarea>
</x-form-field>
