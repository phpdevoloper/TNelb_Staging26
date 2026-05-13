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

        // Only apply lock/unlock behaviour when this is a returned application (app_status = QU)
        @if(isset($application_details->app_status) && $application_details->app_status === 'QU')
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
    })();
</script>

