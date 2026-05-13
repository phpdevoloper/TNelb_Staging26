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
            <li><a href="#"><span class=" fa fa-info-circle"> </span> Old Certificate Renewal</a></li>
        </ul>
    </div>
</section>

<section class="apply-form">
    <div class="auto-container">
        <div class="wrapper-box">
            <div class="row justify-content-center">
                <div class="col-lg-6 col-md-7 col-12">
                    <div class="apply-card apply-card-info old-renewal-card">
                        <div class="apply-card-header" style="background-color: rgb(3 90 179); padding: 15px;">
                            <div class="text-center">
                                <h5 class="card-title_apply text-black" style="text-transform: uppercase;font-weight: 900;color: #ffffff;">
                                    Old Certificate Renewal
                                </h5>
                                <h6 class="card-title_apply text-black mt-2" style="font-size: 15px;color: #ffffff;">
                                    பழைய சான்றிதழ் புதுப்பித்தல்
                                </h6>
                            </div>
                        </div>
                        <div class="apply-card-body">
                            <p class="mb-3 text-muted old-renewal-helper">
                                Select the licence, enter the certificate number and expiry date, verify it, and then submit your renewal request.
                            </p>
                            <form id="old_certificate_renewal_form"
                                  method="POST"
                                  action="{{ route('old_certificate_renewal.submit') }}"
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
                                                    <option value="{{ $licence->id }}"
                                                            data-prefix="{{ $licence->cert_licence_code }}">
                                                        {{ $licence->licence_name }} [{{ $licence->form_name }}]
                                                    </option>
                                                @endforeach
                                            </select>
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
                                                           id="certificate_no"
                                                           name="certificate_no"
                                                           maxlength="50"
                                                           placeholder="Certificate number"
                                                           required>
                                                </div>
                                                <div class="col-12 col-md-6 mt-2 mt-md-0">
                                                    <input type="date"
                                                           class="form-control text-box single-line"
                                                           id="expiry_date"
                                                           name="expiry_date"
                                                           required>
                                                </div>
                                            </div>
                                            <span id="certificate_status" class="d-block mt-1"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-3">
                                    <div class="row align-items-center">
                                        <div class="col-12 col-md-4 text-md-right">
                                            <label for="supporting_doc" class="old-renewal-label">Upload Ceritificate <span style="color: red;">*</span></label>
                                        </div>
                                        <div class="col-12 col-md-8">
                                            <input type="file" class="form-control text-box single-line" id="supporting_doc" name="supporting_doc" accept=".pdf,.jpg,.jpeg,.png" required>
                                            <span class="file-limit d-block mt-1">File type: PDF/JPG/PNG (Max 250 KB)</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-4">
                                    <div class="col-12 text-center">
                                        <button type="button" class="btn btn-primary mr-2" id="verifyCertificateBtn">
                                            Verify
                                        </button>
                                        <button type="submit" class="btn btn-success btn-social" id="oldCertSubmitBtn" disabled>
                                            Submit
                                        </button>
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
        var $verifyBtn   = $('#verifyCertificateBtn');
        var $submitBtn   = $('#oldCertSubmitBtn');
        var $statusSpan  = $('#certificate_status');
        var $formSelect  = $('#form_name');
        var $certInput   = $('#certificate_no');
        var $expiryInput = $('#expiry_date');
        var $formEl      = $('#old_certificate_renewal_form');
        var token        = $('input[name=\"_token\"]').val();

        if (!$verifyBtn.length || !$submitBtn.length || !$statusSpan.length ||
            !$formSelect.length || !$certInput.length || !$expiryInput.length || !$formEl.length || !token) {
            return;
        }

        function showExpiredPopup() {
            var msg = 'Your licence expired more than a year ago, so it will be treated as a new application. Please apply for a new licence.';
            var selectedOption = $formSelect.find('option:selected');
            var expectedPrefix = (selectedOption.data('prefix') || '').toString().toUpperCase();
            var applyUrl = null;
            var dashboardUrl = '{{ route('dashboard') }}';
            if (expectedPrefix === 'C') {
                applyUrl = '{{ route('apply-form-s') }}';
            } else if (expectedPrefix === 'B') {
                applyUrl = '{{ route('apply-form-w') }}';
            } else if (expectedPrefix === 'H') {
                applyUrl = '{{ route('apply-form-wh') }}';
            } else {
                applyUrl = dashboardUrl;
            }

            function withOldLicenceParams(url) {
                var certNo = ($certInput.val() || '').toString().trim();
                var expiry = ($expiryInput.val() || '').toString().trim(); // YYYY-MM-DD
                if (!certNo && !expiry) return url;

                var qs = [];
                if (certNo) qs.push('old_cert_no=' + encodeURIComponent(certNo));
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
            // expiryValue is YYYY-MM-DD from <input type="date">
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
        }

        resetVerification();

        // If user changes form, certificate number, or expiry date after verify,
        // force re-verification (date is part of the verification)
        $formSelect.on('change', resetVerification);
        $certInput.on('input', resetVerification);
        $expiryInput.on('change', resetVerification);

        // Submit-time guard: if expired > 1 year, treat as new application
        $formEl.on('submit', function (e) {
            var expiry = $expiryInput.val();
            if (isExpiredMoreThanOneYear(expiry)) {
                e.preventDefault();
                showExpiredPopup();
                return false;
            }
            return true;
        });

        $verifyBtn.on('click', function () {
            resetVerification();

            var formId         = $formSelect.val();
            var certNoRaw      = $certInput.val() || '';
            var certNo         = $.trim(certNoRaw);
            var expiry         = $expiryInput.val();
            var selectedOption = $formSelect.find('option:selected');
            var expectedPrefix = (selectedOption.data('prefix') || '').toString().toUpperCase();

            if (!formId || !certNo || !expiry) {
                $statusSpan.text('Please select form, enter certificate number and expiry date.');
                $statusSpan.addClass('text-danger');
                return;
            }

            if (expectedPrefix) {
                var pattern = new RegExp('^' + expectedPrefix + '[0-9]{2,}$');
                if (!pattern.test(certNo.toUpperCase())) {
                    $statusSpan.text('Certificate number must start with ' + expectedPrefix + ' followed by digits (e.g. ' + expectedPrefix + '01).');
                    $statusSpan.addClass('text-danger');
                    return;
                }
            }

            $.ajax({
                url: '{{ route('old_certificate_renewal.verify') }}',
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token
                },
                contentType: 'application/json',
                dataType: 'json',
                data: JSON.stringify({
                    form_id: formId,
                    certificate_no: certNo,
                    expiry_date: expiry
                })
            })
            .done(function (data, textStatus, jqXHR) {
                var ok = jqXHR.status >= 200 && jqXHR.status < 300;
                $statusSpan.attr('class', 'd-block mt-1');

                if (!ok || !data || !data.valid) {
                    $statusSpan.text((data && data.message) || 'Certificate number is invalid.');
                    $statusSpan.addClass('text-danger');
                    $submitBtn.prop('disabled', true);
                    return;
                }

                $statusSpan.text(data.message || 'Certificate verified successfully.');
                $statusSpan.addClass('text-success');
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

