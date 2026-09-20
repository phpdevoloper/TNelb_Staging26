@include('include.header')
<!-- About section -->

<style>
    hr {
        margin-top: 2px;
        margin-bottom: 5px;
        border: 0;
        border-top: 1px solid rgba(0, 0, 0, .1);
    }

    .form-group {
        margin-bottom: 0px;
    }

    #success {
        background: green;
    }

    #error {
        background: red;
    }

    #warning {
        background: coral;
    }

    #info {
        background: cornflowerblue;
    }

    #question {
        background: grey;
    }

    /* .swal2-popup.swal2-modal.swal2-show {
        width: 100%;
    } */

    .swal2-popup li {
        font-size: 15px;
        margin-bottom: 8px;
    }


    .swal2-popup li {
        font-size: 15px;
        margin-bottom: 8px;
    }

    .swal2-popup li ul {
        margin-left: 15px;
    }

    #license_date_change .is-invalid {
        border-color: #dc3545;
    }

    #license_date_change .field-error {
        display: block;
        min-height: 18px;
        font-size: 13px;
        margin-top: 4px;
    }
</style>

<section class="">
    <div class="container">
        <ul id="breadcrumb">
            <li><a href="{{ route('dashboard') }}"><span class="fa fa-home"> </span> Dashboard</a></li>
            <li><a href="#"><span class=" fa fa-info-circle"> </span> License Date Change</a></li>

        </ul>
    </div>
</section>
<section class="apply-form">
    <div class="auto-container">
        <div class="wrapper-box">
            <div class="row">
                <div class="col-lg-12 col-12">
                    <div class="apply-card apply-card-info" data-select2-id="14">
                        <div class="apply-card-header" style="background-color: #70c6ef  !important;">
                            <div class="row">
                                <div class="col-6 col-lg-8">
                                    <h5 class="card-title_apply text-black text-left"> License Date Change </h5>
                                </div>



                            </div>

                        </div>
                        <div class="apply-card-body">

                            <form id="license_date_change" enctype="multipart/form-data">
                                @csrf
                                <div class="row align-items-center">
                                    <div class="col-md-4 mb-3">
                                        <label for="certificate_type" class="form-label">Certificate Type</label>
                                        <select class="form-control" name="certificate_type" id="certificate_type">
                                            <option value="">Select Certificate Type</option>
                                            <option value="S">Certificate C</option>
                                            <option value="W">Certificate W</option>
                                            <option value="WH">Certificate WH</option>
                                            <option value="P">Certificate P</option>
                                        </select>
                                        <span class="error text-danger field-error" id="certificate_type_error"></span>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="license_number" class="form-label">Certificate No</label>
                                        <select class="form-control" name="license_number" id="license_number" disabled>
                                            <option value="">Select Certificate Type first</option>
                                        </select>
                                        <span class="error text-danger field-error" id="license_number_error"></span>
                                    </div>

                                    <div class="col-md-4 mb-3">
                                        <label for="expiry" class="form-label">Valid To Date</label>
                                        <input type="date" class="form-control" name="expires_at" id="expiry">
                                        <span class="error text-danger field-error" id="expires_at_error"></span>
                                    </div>
                                </div>

                                <div class="text-center mt-4">
                                    <button type="submit" class="btn btn-success px-4 py-2">Save Valid To Date</button>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>

                <div class="col-lg-12 col-12">
                    <div class="apply-card apply-card-info" data-select2-id="14">
                        <div class="apply-card-header" style="background-color: #70c6ef  !important;">
                            <div class="row">
                                <div class="col-6 col-lg-8">
                                    <h5 class="card-title_apply text-black text-left"> Current Date Change </h5>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="apply-card-body">
                        <form id="current_date_change" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-12 col-md-12">
                                    <div class="form-group">
                                        <div class="row align-items-center">
                                            <div class="col-12 col-md-6">
                                                <div class="row align-items-center">
                                                    <div class="col-12 col-md-3">
                                                        <label for="Name">Date </label>
                                                    </div>
                                                    <div class="col-12 col-md-8 pd-left-40">
                                                        <input type="date" class="form-control" name="current_date"
                                                            id="current_date">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @csrf
                                        <div class="row mt-5">
                                            <div class="offset-md-5 col-12 col-md-6">
                                                <div class="form-group">
                                                    <button type="submit" class="btn btn-success">
                                                        change Date
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div id="draftModal" class="overlay-bg" style="display: none;">
    <div class="otp-modal">
        <h5>Your Application Details Saved Successfully</h5>
        <br>
        <button onclick="closeDraftModal()">OK</button>
    </div>
</div>

</div>

<footer class="main-footer">

    @include('include.footer')
    <script>
        $(document).ready(function() {

            const $certificateType = $('#certificate_type');
            const $licenseNumber = $('#license_number');
            const $expiry = $('#expiry');

            function clearFieldError($field) {
                $field.removeClass('is-invalid');
                $('#' + $field.attr('name') + '_error').text('');
            }

            function showFieldError(name, message) {
                const $field = $('[name="' + name + '"]');
                $field.addClass('is-invalid');
                $('#' + name + '_error').text(message || '');
            }

            function clearLicenseFormErrors() {
                $('#license_date_change .is-invalid').removeClass('is-invalid');
                $('#license_date_change .field-error').text('');
            }

            function resetLicenseDropdown(placeholder) {
                $licenseNumber.html('<option value="">' + placeholder + '</option>').val('').prop('disabled', true);
                $expiry.val('');
                clearFieldError($licenseNumber);
                clearFieldError($expiry);
            }

            function loadExpiryDate() {
                const certificateType = $certificateType.val();
                const licenseNumber = $licenseNumber.val();
                clearFieldError($licenseNumber);
                if (!certificateType || !licenseNumber) {
                    $expiry.val('');
                    return;
                }

                $.get(BASE_URL + '/get-license-expiry/' + encodeURIComponent(certificateType) + '/' + encodeURIComponent(licenseNumber), function(data) {
                    $expiry.val(data.valid_to || '');
                }).fail(function() {
                    $expiry.val('');
                });
            }

            $certificateType.on('change', function() {
                clearFieldError($certificateType);
                const certificateType = $(this).val();
                if (!certificateType) {
                    resetLicenseDropdown('Select Certificate Type first');
                    return;
                }

                resetLicenseDropdown('Loading...');
                $.get(BASE_URL + '/get-license-numbers/' + encodeURIComponent(certificateType), function(data) {
                    const certificates = data.certificates || [];
                    let options = '<option value="">Select Certificate No</option>';
                    certificates.forEach(function(item) {
                        const number = $('<div>').text(item.certificate_no || '').html();
                        options += '<option value="' + number + '">' + number + '</option>';
                    });
                    $licenseNumber.html(options).prop('disabled', certificates.length === 0);
                    if (certificates.length === 0) {
                        $licenseNumber.html('<option value="">No certificates found</option>');
                    }
                    $expiry.val('');
                }).fail(function() {
                    resetLicenseDropdown('Unable to load certificates');
                    showFieldError('certificate_type', 'Unable to load certificates for this type.');
                });
            });

            $licenseNumber.on('change', loadExpiryDate);
            $expiry.on('change input', function() {
                clearFieldError($expiry);
            });

            function validateLicenseDateForm() {
                const errors = {};
                const certificateType = $certificateType.val();
                const licenseNumber = $licenseNumber.val();
                const expiresAt = $expiry.val();

                if (!certificateType) {
                    errors.certificate_type = 'Please select a certificate type.';
                }
                if (!licenseNumber) {
                    errors.license_number = 'Please select a certificate number.';
                }
                if (!expiresAt) {
                    errors.expires_at = 'Please choose a valid to date.';
                }

                return errors;
            }

            // ✅ Submit form via AJAX
            $('#license_date_change').on('submit', function(e) {
                e.preventDefault();
                clearLicenseFormErrors();

                const clientErrors = validateLicenseDateForm();
                if (Object.keys(clientErrors).length) {
                    $.each(clientErrors, function(name, message) {
                        showFieldError(name, message);
                    });
                    return;
                }

                const $wasDisabled = $licenseNumber.prop('disabled');
                $licenseNumber.prop('disabled', false);

                $.ajax({
                    url: "{{ route('update-license-expiry') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Updated!',
                            text: response.message,
                            confirmButtonColor: '#198754',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = "{{ route('dashboard') }}";
                            }
                        });
                    },
                    error: function(xhr) {
                        const payload = xhr.responseJSON || {};
                        const errors = payload.errors || {};

                        if (xhr.status === 422 && Object.keys(errors).length) {
                            $.each(errors, function(name, messages) {
                                showFieldError(name, Array.isArray(messages) ? messages[0] : messages);
                            });
                            return;
                        }

                        if (xhr.status === 404) {
                            showFieldError('license_number', payload.message || 'License not found.');
                            return;
                        }

                        showFieldError('expires_at', payload.message || 'Something went wrong. Please try again.');
                    },
                    complete: function() {
                        $licenseNumber.prop('disabled', $wasDisabled);
                    }
                });
            });

            $('#current_date_change').on('submit', function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('updateCurrentDate') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        alert(response.message);
                    },
                    error: function(xhr) {
                        alert(xhr.responseJSON?.message || 'Something went wrong');
                    }
                });
            });

        });
    </script>
