@php
    $panelId = $panelId ?? 'fsAltProofPanel';
    $inputId = $inputId ?? 'alteration_proof';
    $inputName = $inputName ?? 'alteration_proof';
    $label = $label ?? 'Supporting proof';
@endphp
<div class="fs-alt-proof-panel" id="{{ $panelId }}">
    <div class="fs-alt-proof-compact" data-proof-input="{{ $inputId }}">
        <div class="fs-alt-proof-compact__label">{{ $label }}<span class="fs-alt-proof-req" aria-hidden="true">*</span></div>
        <div class="fs-alt-proof-compact__bar">
            <input type="file"
                class="fs-alt-proof-input"
                name="{{ $inputName }}"
                id="{{ $inputId }}"
                accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png"
                tabindex="-1">
            <div class="fs-alt-proof-compact__idle">
                <label class="fs-alt-proof-browse" for="{{ $inputId }}">
                    <i class="fa fa-paperclip" aria-hidden="true"></i> Browse
                </label>
                <span class="fs-alt-proof-compact__hint">PDF/JPG/PNG · max 200 KB</span>
            </div>
            <div class="fs-alt-proof-compact__status" hidden>
                <span class="fs-alt-proof-file-icon" aria-hidden="true"><i class="fa fa-file-o"></i></span>
                <span class="fs-alt-proof-fname" title=""></span>
                <div class="fs-alt-proof-actions">
                    <a href="#" class="fs-alt-proof-view preview-link" target="_blank" rel="noopener noreferrer" title="View file">
                        <i class="fa fa-file-pdf-o" aria-hidden="true"></i><span>View</span>
                    </a>
                    <label class="fs-alt-proof-change" for="{{ $inputId }}" title="Change file" aria-label="Change file">
                        <i class="fa fa-refresh" aria-hidden="true"></i>
                    </label>
                    <button type="button" class="fs-alt-proof-clear" title="Remove file" aria-label="Remove file">
                        <i class="fa fa-times" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
