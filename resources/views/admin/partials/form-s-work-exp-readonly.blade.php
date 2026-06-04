@php
    $expList = $workExperience ?? collect();
    if (is_array($expList)) {
        $expList = collect($expList);
    }
@endphp
<div class="work-exp-admin-readonly comp_certificate">
    @include('user_login.partials.form-s-work-exp-view', ['exp_details' => $expList])
</div>
