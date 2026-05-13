@include('include.header')

<style>
    .old-renewal-card {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e3e6f0;
    }

    .old-renewal-label {
        font-weight: 600;
        font-size: 14px;
    }

    .old-renewal-helper {
        font-size: 12px;
        color: #6c757d;
    }

    .old-renewal-verify-group .btn {
        white-space: nowrap;
    }
</style>

<section class="">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard')}}"><span class="fa fa-home"> </span> Dashboard</a></li>
            <li><a href="#"><span class=" fa fa-info-circle"> </span> Old Contractor Renewal</a></li>
        </ul>
    </div>
</section>

<section class="apply-form">
    <div class="auto-container">
        <div class="wrapper-box">
            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-8 col-12">
                    <div class="apply-card apply-card-info old-renewal-card">
                        <div class="apply-card-header" style="background-color: rgb(3 90 179); padding: 15px;">
                            <div class="text-center">
                                <h5 class="card-title_apply text-black" style="text-transform: uppercase;font-weight: 900;color: #ffffff;">
                                    Old Contractor Renewal
                                </h5>
                                <h6 class="card-title_apply text-black mt-2" style="font-size: 15px;color: #ffffff;">
                                    பழைய ஒப்பந்ததாரர் உரிமம் புதுப்பித்தல்
                                </h6>
                            </div>
                        </div>
                        <div class="apply-card-body">
                            <p class="mb-3 text-muted old-renewal-helper">
                                Select the licence, enter the licence number and expiry date, verify it, and then submit your renewal request.
                            </p>

                            @if(session('success'))
                                <div class="alert alert-success">{{ session('success') }}</div>
                            @endif

                            <form id="old_contractor_renewal_form"
                                  method="POST"
                                  action="{{ route('old_contractor_renewal.submit') }}"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-4 text-md-right">
                                            <label for="form_name" class="old-renewal-label">Forms <span style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <select class="form-control" id="form_name" name="form_name" required>
                                                <option value="" selected disabled>Select Form</option>
                                                @foreach($licences as $licence)
                                                    @php
                                                        $code = strtoupper((string) ($licence->cert_licence_code ?? ''));
                                                        $prefix = match (true) {
                                                            in_array($code, ['A', 'EA'], true) => 'EA',
                                                            in_array($code, ['SA', 'ESA'], true) => 'ESA',
                                                            in_array($code, ['SB', 'ESB'], true) => 'ESB',
                                                            $code === 'EB' => 'EB',
                                                            default => '',
                                                        };
                                                    @endphp
                                                    <option value="{{ $licence->id }}"
                                                            data-prefix="{{ $prefix }}">
                                                        {{ $licence->licence_name }} [{{ $licence->form_name }}]
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('form_name')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-4 text-md-right">
                                            <label class="old-renewal-label">Verification <span style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <div class="row">
                                                <div class="col-12 col-md-6 mb-2 mb-md-0">
                                                    <input type="text"
                                                           class="form-control text-box single-line"
                                                           id="licence_no"
                                                           name="licence_no"
                                                           maxlength="50"
                                                           value="{{ old('licence_no') }}"
                                                           placeholder="Licence number (e.g. EA01)"
                                                           required>
                                                    @error('licence_no')
                                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                                <div class="col-12 col-md-6 mt-2 mt-md-0">
                                                    <input type="date"
                                                           class="form-control text-box single-line"
                                                           id="expiry_date"
                                                           name="expiry_date"
                                                           value="{{ old('expiry_date') }}"
                                                           required>
                                                    @error('expiry_date')
                                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                                    @enderror
                                                </div>
                                            </div>
                                            <span id="licence_status" class="d-block mt-1"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-4 text-md-right">
                                            <label for="supporting_doc" class="old-renewal-label">Upload Licence <span style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="file" class="form-control text-box single-line" id="supporting_doc" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <span class="file-limit d-block mt-1">File type: PDF/JPG/PNG (Max 250 KB)</span>
                                            @error('supporting_doc')
                                                <small class="text-danger d-block mt-1">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-primary mr-2" id="verifyLicenceBtn">
                                            Verify
                                        </button>
                                        <button type="submit" class="btn btn-success btn-social" id="oldContractorSubmitBtn" disabled>
                                            Submit
                                        </button>
                                        <a href="{{ route('dashboard') }}" class="btn btn-secondary ml-2">Back</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="main-footer">
    @include('include.footer')
</footer>

<script>
    $(function () {
        var $verifyBtn   = $('#verifyLicenceBtn');
        var $submitBtn   = $('#oldContractorSubmitBtn');
        var $statusSpan  = $('#licence_status');
        var $formSelect  = $('#form_name');
        var $licInput    = $('#licence_no');
        var $expiryInput = $('#expiry_date');
        var $formEl      = $('#old_contractor_renewal_form');
        var token        = $('input[name="_token"]').val();
        var expiredMoreThanOneYear = false;

        if (!$verifyBtn.length || !$submitBtn.length || !$statusSpan.length ||
            !$formSelect.length || !$licInput.length || !$expiryInput.length || !$formEl.length || !token) {
            return;
        }

        function showExpiredPopup() {
            var msg = 'Your licence expired more than a year ago, so it will be treated as a new application. Please apply for a new licence.';
            var selectedOption = $formSelect.find('option:selected');
            var expectedPrefix = (selectedOption.data('prefix') || '').toString().toUpperCase();
            var applyUrl = null;
            var dashboardUrl = '{{ route('dashboard') }}';

            if (expectedPrefix === 'EA') {
                applyUrl = '{{ route('apply-form-a') }}';
            } else if (expectedPrefix === 'ESA') {
                applyUrl = '{{ route('apply-form-sa') }}';
            } else if (expectedPrefix === 'ESB') {
                applyUrl = '{{ route('apply-form-sb') }}';
            } else if (expectedPrefix === 'EB') {
                applyUrl = '{{ route('apply-form-b') }}';
            } else {
                applyUrl = dashboardUrl;
            }

            function withOldLicenceParams(url) {
                var licNo = ($licInput.val() || '').toString().trim();
                var expiry = ($expiryInput.val() || '').toString().trim(); // YYYY-MM-DD
                if (!licNo && !expiry) return url;

                var qs = [];
                if (licNo) qs.push('old_licence_no=' + encodeURIComponent(licNo));
                if (expiry) qs.push('old_expiry_date=' + encodeURIComponent(expiry));
                qs.push('from_old_renewal=1');

                return url + (url.indexOf('?') >= 0 ? '&' : '?') + qs.join('&');
            }

            var applyUrlWithParams = withOldLicenceParams(applyUrl);
            if (typeof Swal !== 'undefined' && Swal && typeof Swal.fire === 'function') {
                return Swal.fire({
                    icon: 'warning',
                    title: 'Not eligible for renewal',
                    text: msg,
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Apply',
                    cancelButtonText: 'No',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    allowEnterKey: false,
                    backdrop: true,
                }).then(function (result) {
                    if (result && result.isConfirmed) {
                        window.location.href = applyUrlWithParams;
                        return;
                    }
                    window.location.href = dashboardUrl;
                });
            }

            // Fallback (no SweetAlert): confirm + redirect
            if (confirm(msg + "\n\nGo to new application now?")) {
                window.location.href = applyUrlWithParams;
                return;
            }
            window.location.href = dashboardUrl;
        }

        function isExpiredMoreThanOneYear(expiryValue) {
            if (!expiryValue) return false;
            var expiry = new Date(expiryValue + 'T00:00:00');
            if (isNaN(expiry.getTime())) return false;

            var oneYearAfter = new Date(expiry.getTime());
            oneYearAfter.setFullYear(oneYearAfter.getFullYear() + 1);

            var today = new Date();
            today.setHours(0, 0, 0, 0);

            return today > oneYearAfter;
        }

        function resetVerification() {
            $statusSpan.text('');
            $statusSpan.attr('class', 'd-block mt-1');
            $submitBtn.prop('disabled', true);
            expiredMoreThanOneYear = false;
        }

        resetVerification();
        $formSelect.on('change', resetVerification);
        $licInput.on('input', resetVerification);
        $expiryInput.on('change', resetVerification);

        $formEl.on('submit', function (e) {
            var expiry = $expiryInput.val();
            // Certificate flow: show popup only on submit
            if (expiredMoreThanOneYear || isExpiredMoreThanOneYear(expiry)) {
                e.preventDefault();
                showExpiredPopup();
                return false;
            }
            return true;
        });

        $verifyBtn.on('click', function () {
            resetVerification();

            var formId         = $formSelect.val();
            var licNoRaw       = $licInput.val() || '';
            var licNo          = $.trim(licNoRaw);
            var selectedOption = $formSelect.find('option:selected');
            var expectedPrefix = (selectedOption.data('prefix') || '').toString().toUpperCase();

            if (!formId || !licNo) {
                $statusSpan.text('Please select licence type and enter licence number.');
                $statusSpan.addClass('text-danger');
                return;
            }

            if (expectedPrefix) {
                var upper = licNo.toUpperCase();
                // If user typed a different known prefix, fail fast
                var typedPrefix = (upper.match(/^[A-Z]+/) || [null])[0];
                if (typedPrefix && ['EA','ESA','ESB','EB'].indexOf(typedPrefix) >= 0 && typedPrefix !== expectedPrefix) {
                    $statusSpan.text('Invalid licence number for selected licence type.');
                    $statusSpan.addClass('text-danger');
                    return;
                }
            }

            $.ajax({
                url: '{{ route('old_contractor_renewal.verify') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    form_id: formId,
                    licence_no: licNo,
                })
            })
            .done(function (data, textStatus, jqXHR) {
                var ok = jqXHR.status >= 200 && jqXHR.status < 300;
                $statusSpan.attr('class', 'd-block mt-1');

                if (!ok || !data || !data.valid) {
                    $statusSpan.text((data && data.message) || 'Licence number is invalid.');
                    $statusSpan.addClass('text-danger');
                    $submitBtn.prop('disabled', true);
                    if (data && data.details && data.details.expiry_date) {
                        $expiryInput.val(data.details.expiry_date);
                    }
                    return;
                }

                $statusSpan.text(data.message || 'Licence verified successfully.');
                $statusSpan.addClass('text-success');
                if (data.details && data.details.expiry_date) {
                    $expiryInput.val(data.details.expiry_date);
                }

                expiredMoreThanOneYear = !!(data.details && data.details.expired_more_than_one_year);

                $submitBtn.prop('disabled', false);
            })
            .fail(function (jqXHR) {
                var message = 'Unable to verify now. Please try again.';
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    message = jqXHR.responseJSON.message;
                }
                $statusSpan.text(message);
                $statusSpan.addClass('text-danger');
                $submitBtn.prop('disabled', true);
            });
        });
    });
</script>

