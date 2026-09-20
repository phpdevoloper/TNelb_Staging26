@include('include.header')

<div class="fs-page-wrap" style="min-height:40vh;"></div>

@include('include.footer')

<script>
    window.formSAltLauncher = true;
    window.formSAltVerifyUrl = "{{ $alterVerifyUrl ?? route('form_w_alt.verify') }}";
    window.formSAltCertificatesUrl = "{{ $alterCertificatesUrl ?? route('form_w_alt.certificates') }}";
    window.dashboardUrl = "{{ route('dashboard') }}";
    window.formSAltCert = @json($form_code ?? 'W');
    window.formSAltCertLabel = @json($form_label ?? 'Wireman Competency Certificate [Form W]');
    @if(session('alteration_error'))
    window.formSAltLauncherError = @json(session('alteration_error'));
    @endif
</script>
<script src="{{ url('assets/js/alteration.js') }}"></script>
