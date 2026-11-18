{{--
    OTP block: N single-digit boxes + resend timer + error placeholder.
    Hidden (visibility, so no layout shift) until register.js adds .is-visible.

    <x-otp-input :length="6" :seconds="30" />
--}}
@props([
    'id' => 'otpSection',
    'name' => 'otp',
    'label' => 'Enter OTP',
    'length' => 6,
    'seconds' => 30,
])

@php($initialTimer = sprintf('%02d:%02d', intdiv($seconds, 60), $seconds % 60))

<div class="tnelb-otp-wrap" id="{{ $id }}" data-otp-section data-otp-seconds="{{ $seconds }}" aria-hidden="true">
    <x-form-field :name="$name" :label="$label" label-tag="span" required>
        <div class="tnelb-otp-row">
            <div class="tnelb-otp-boxes" role="group" aria-label="{{ $label }} — {{ $length }} digits">
                @for ($i = 1; $i <= $length; $i++)
                    <input
                        type="text"
                        class="otp-digit"
                        data-otp-digit="{{ $i }}"
                        inputmode="numeric"
                        pattern="[0-9]*"
                        maxlength="1"
                        autocomplete="{{ $i === 1 ? 'one-time-code' : 'off' }}"
                        aria-label="OTP digit {{ $i }} of {{ $length }}"
                        aria-describedby="{{ $name }}-error"
                        disabled
                    >
                @endfor
            </div>

            <div class="tnelb-otp-resend">
                <span data-otp-timer-wrap id="otpTimerWrap">
                    <i class="fa fa-clock-o" aria-hidden="true"></i>
                    Resend OTP in <span data-otp-timer id="otpTimer" aria-live="off">{{ $initialTimer }}</span>
                </span>
                <button type="button" data-action="send-otp" data-otp-resend id="resendOtpBtn" hidden>
                    Resend OTP
                </button>
            </div>
        </div>
    </x-form-field>
</div>
