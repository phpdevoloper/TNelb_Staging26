{{--
    Reusable select: label + options + error.

    $options accepts a plain list (value === label) or an associative
    array (value => label):

    <x-form-select name="state" label="Select State" required
                   placeholder="--- Select State ---" :options="$states" />
--}}
@props([
    'name',
    'label' => null,
    'id' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'required' => false,
    'fieldClass' => null,
])

@php
    $controlId = $id ?? $name;
    $current = old($name, $selected);
@endphp

<x-form-field :name="$name" :label="$label" :for="$controlId" :required="$required" :class="$fieldClass">
    <select
        id="{{ $controlId }}"
        name="{{ $name }}"
        @if ($required) required aria-required="true" @endif
        aria-describedby="{{ $name }}-error"
        {{ $attributes->merge(['class' => 'form-control']) }}
    >
        @if ($placeholder !== null)
            <option value="">{{ $placeholder }}</option>
        @endif

        @foreach ($options as $key => $text)
            @php($optionValue = is_int($key) ? $text : $key)
            <option value="{{ $optionValue }}" @selected((string) $current === (string) $optionValue)>{{ $text }}</option>
        @endforeach
    </select>
</x-form-field>
