{{--
    Soft info / helper strip used at the bottom of a wizard panel.

    <x-info-box>OTP will be sent to the entered mobile number via SMS.</x-info-box>
--}}
@props([
    'icon' => 'info-circle',
    'role' => 'note',
])

<div {{ $attributes->class(['tnelb-wiz-info']) }} @if ($role === 'alert') role="alert" @endif>
    @if ($icon)
        <i class="fa fa-{{ $icon }}" aria-hidden="true"></i>
    @endif
    <span>{{ $slot }}</span>
</div>
