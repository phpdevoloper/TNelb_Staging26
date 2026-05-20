@include('user_login.edit_application_p')

<script>
    (function() {
        var $form = $('#competency_form_p');
        var $editBtn = $('#editBtn');
        var $wrap = $('#actionButtonsWrap');
        var $cancelBtn = $('#cancelBtn');
        var $submitBtn = $('#DraftBtn'); // used as Submit Corrections on returned Form P

        function lockForm() {
            $form.find('input').not('[type="hidden"]').prop('readonly', true).prop('disabled', false);
            $form.find('input[type="file"]').prop('readonly', false).prop('disabled', true);
            $form.find('textarea').prop('readonly', true);
            $form.find('select').prop('disabled', true);
            $form.find('button').not('#editBtn, #cancelBtn, #DraftBtn').prop('disabled', true);
            $('#declarationCheckbox').prop('checked', true).prop('disabled', true);
        }

        function unlockForm() {
            $form.find('input').not('[type="hidden"]').prop('readonly', false);
            $form.find('input[type="file"]').prop('disabled', false);
            $form.find('textarea').prop('readonly', false);
            $form.find('select').prop('disabled', false);
            $form.find('button').not('#editBtn, #cancelBtn, #DraftBtn').prop('disabled', false);
            $('#declarationCheckbox').prop('checked', true).prop('disabled', false);
        }

        function lockSection($sec) {
            $sec.find('input').not('[type="hidden"]').prop('readonly', true).prop('disabled', false);
            $sec.find('input[type="file"]').prop('disabled', true);
            $sec.find('textarea').prop('readonly', true);
            $sec.find('select').prop('disabled', true);
            $sec.find('button').not('#editBtn, #cancelBtn, #DraftBtn').prop('disabled', true);
        }

        function unlockSection($sec) {
            $sec.find('input').not('[type="hidden"]').prop('readonly', false);
            $sec.find('input[type="file"]').prop('disabled', false);
            $sec.find('textarea').prop('readonly', false);
            $sec.find('select').prop('disabled', false);
            $sec.find('button').not('#editBtn, #cancelBtn, #DraftBtn').prop('disabled', false);
        }

        function applyPartialReturnedLocks() {
            var allowed = @json($returnedFormPSectionKeys ?? []);
            if (!allowed.length) {
                return;
            }
            $('.fs-section[data-section-key]').each(function () {
                var key = $(this).attr('data-section-key');
                if (!key) return;
                if (allowed.indexOf(key) === -1) {
                    lockSection($(this));
                } else {
                    unlockSection($(this));
                }
            });
            $('#declarationCheckbox').prop('checked', true).prop('disabled', false);
        }

        @if(isset($application_details->app_status) && $application_details->app_status === 'QU')
            @if(!empty($returnedIsPartialEdit ?? false))
                lockForm();
                applyPartialReturnedLocks();
                $editBtn.hide();
                $wrap.show();
                $cancelBtn.off('click.returnedP').on('click.returnedP', function() {
                    lockForm();
                    applyPartialReturnedLocks();
                    $wrap.hide();
                    $editBtn.show();
                });
                $editBtn.off('click.returnedP').on('click.returnedP', function() {
                    unlockForm();
                    applyPartialReturnedLocks();
                    $editBtn.hide();
                    $wrap.show();
                });
            @else
                lockForm();

                $editBtn.on('click', function() {
                    unlockForm();
                    $editBtn.hide();
                    $wrap.show();
                });

                $cancelBtn.on('click', function() {
                    lockForm();
                    $wrap.hide();
                    $editBtn.show();
                });
            @endif
        @endif
    })();
</script>
