@include('user_login.edit_application_p')

<script>
    (function() {
        var $form = $('#competency_form_p');
        if (!$form.length) return;
        $('#declarationCheckbox').prop('checked', true);
    })();
</script>
