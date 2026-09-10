@include('include.header')

<div class="fs-page-wrap" style="min-height:40vh;"></div>

@include('include.footer')

<script>
    window.formSAltLauncher = true;
    window.formSAltVerifyUrl = "{{ $alterVerifyUrl ?? route('form_s_alt.verify') }}";
    window.formSAltCertificatesUrl = "{{ $alterCertificatesUrl ?? route('form_s_alt.certificates') }}";
    window.dashboardUrl = "{{ route('dashboard') }}";
    window.formSAltCert = @json($form_code ?? 'S');
    window.formSAltCertLabel = @json($form_label ?? 'Supervisor Competency Certificate [Form S]');
    @if(session('alteration_error'))
    window.formSAltLauncherError = @json(session('alteration_error'));
    @endif
</script>
<script src="{{ url('assets/js/alteration.js') }}"></script>
