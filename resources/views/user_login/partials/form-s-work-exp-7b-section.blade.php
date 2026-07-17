{{-- Form S §7b board-member work container (separate from §7a previous-experience section). --}}
@php
    $exp_details = $exp_details ?? collect();
    $hideUploadWhenDocExists = ! empty($hideUploadWhenDocExists);
    $isAlterationMode = ! empty($isAlterationMode);
@endphp
<div class="work-exp-wrap" data-work-part="current">
    <div class="work-rows js-work-container"
        id="work-container-current"
        data-work-part="current"
        data-min-rows="1"
        data-max-rows="1">
        @if ($exp_details->isNotEmpty())
            @foreach ($exp_details as $index => $expRow)
                @include('user_login.partials.form-s-work-exp-7b-row', [
                    'expRow' => $expRow,
                    'rowIndex' => $index,
                    'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
                    'alterationExistingRow' => $isAlterationMode && $expRow,
                ])
            @endforeach
        @else
            @include('user_login.partials.form-s-work-exp-7b-row', [
                'expRow' => null,
                'rowIndex' => 0,
                'hideUploadWhenDocExists' => $hideUploadWhenDocExists,
                'alterationExistingRow' => false,
            ])
        @endif
    </div>
</div>
