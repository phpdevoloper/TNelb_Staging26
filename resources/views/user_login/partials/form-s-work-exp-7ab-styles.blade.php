    .fs-question-part + .fs-question-part {
        margin-top: 18px;
        padding-top: 18px;
        border-top: 1px dashed #d5deed;
    }
    .contractor-details-notice {
        --notice-bg: #eef6ff;
        --notice-border: #8bb8e8;
        --notice-accent: #0d6efd;
        --notice-text: #1a3a5c;
        --notice-icon: #0d6efd;
        display: block;
        background: var(--notice-bg);
        border: 1px solid var(--notice-border);
        border-left: 0.25rem solid var(--notice-accent);
        border-radius: var(--radius-md, 0.5rem);
        padding: var(--space-3, 0.75rem) var(--space-4, 1rem);
        margin: 0 0 0.875rem;
        font-size: var(--text-sm, 0.875rem);
        color: var(--notice-text);
        line-height: 1.45;
    }
    .contractor-details-notice.d-none {
        display: none !important;
    }
    .contractor-details-notice__title {
        display: flex;
        align-items: flex-start;
        gap: var(--space-2, 0.5rem);
        margin: 0 0 var(--space-2, 0.5rem);
        font-weight: 700;
    }
    .contractor-details-notice__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 1.5rem;
        height: 1.5rem;
        margin-top: 0.1rem;
        color: var(--notice-icon);
        font-size: 1.25rem;
        line-height: 1;
    }
    .contractor-details-notice__text {
        min-width: 0;
        flex: 1;
    }
    .contractor-details-notice__list {
        margin: 0;
        padding-left: 2.15rem;
    }
    .fs-question-part-hd {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 12px;
    }
    .fs-question-part-hd > .fs-section-num,
    .fs-question-part-hd > .fs-section-num--sub,
    .fs-question-part-hd .fs-section-num--sub {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        align-self: center;
        line-height: 1;
    }
    .fs-question-part-hd .fs-section-title {
        font-size: .84rem;
        margin: 0;
        line-height: 1.35;
        display: flex;
        align-items: center;
    }
    .fs-question-part-hd .fs-section-tamil {
        font-size: .76rem;
        margin: 0;
        line-height: 1.35;
    }
    .fs-section-num--sub {
        width: auto;
        min-width: 34px;
        height: 26px;
        padding: 0 10px;
        border-radius: 999px;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: 0.02em;
        line-height: 1;
    }
    .work-row-grid-span { grid-column: 1 / -1; }
    .fs-question-part--7b { position: relative; }
    .fs-7b-hd.fs-question-part-hd {
        align-items: center;
        margin-bottom: 8px;
    }
    .fs-7b-hd .fs-section-num--sub { align-self: center; }
    .fs-7b-hd-content { flex: 1; min-width: 0; }
    .fs-7b-board-gate-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
    }
    .fs-7b-board-gate-label { flex: 1; min-width: 0; }
    .fs-7b-board-gate-label .fs-section-title {
        margin-bottom: 1px;
        line-height: 1.35;
    }
    .fs-7b-board-gate-label .fs-section-tamil {
        font-size: .74rem;
        margin-top: 0;
        line-height: 1.35;
    }
    .fs-7b-board-gate-row .fs-segmented-toggle {
        flex-shrink: 0;
        align-self: center;
    }
    .fs-7b-board-toggle.fs-segmented-toggle {
        border-radius: 6px;
        box-shadow: none;
        align-items: center;
        line-height: 1;
        vertical-align: middle;
    }
    .fs-7b-board-toggle .fs-segmented-opt {
        display: flex;
        margin: 0;
        padding: 0;
        line-height: 1;
        cursor: pointer;
        user-select: none;
    }
    .fs-7b-board-toggle .fs-segmented-opt span {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 44px;
        padding: 3px 12px;
        font-size: .72rem;
        font-weight: 600;
        line-height: 1;
    }
    @media (max-width: 768px) {
        .fs-7b-hd.fs-question-part-hd { align-items: flex-start; }
        .fs-7b-board-gate-row { flex-direction: column; align-items: stretch; }
        .fs-7b-board-gate-row .fs-segmented-toggle { align-self: flex-end; }
    }
    .fs-7b-board-details { margin-top: 6px; }
    .fs-7b-work-wrap { margin-top: 4px; }
    .fs-7b-mode-board .fs-7b-work-wrap {
        padding: 0;
        background: transparent;
        border: 0;
        border-radius: 0;
    }
    #work-container-current .work-row-grid.row {
        display: flex;
        flex-wrap: wrap;
        grid-template-columns: none;
        --bs-gutter-x: 14px;
        --bs-gutter-y: 10px;
        gap: 0;
        padding: 10px 4px 6px;
        margin-right: 0;
        margin-left: 0;
    }
    #work-container-current .work-row-grid.row > .work-card-field,
    #work-container-current .work-row-grid.row > .work-board-member-panel {
        margin-bottom: 6px;
    }
    #work-container-current .work-board-member-panel.col-12 {
        padding-left: 0;
        padding-right: 0;
    }
    #work-container-current .work-board-member-panel .row {
        margin-left: calc(var(--bs-gutter-x, 0.5rem) * -0.5);
        margin-right: calc(var(--bs-gutter-x, 0.5rem) * -0.5);
        --bs-gutter-x: 14px;
        --bs-gutter-y: 10px;
    }
    #work-container-current [data-field="board-meeting-details"] textarea.form-control {
        min-height: 52px;
        resize: vertical;
    }
    #work-container-current .work-row-done-bar.col-12 {
        padding-left: 0;
        padding-right: 0;
        margin-top: 0;
        padding-bottom: 4px;
    }
    #work-container-current .work-card-field { gap: 5px; }
    #work-container-current .work-card-field .form-control { min-height: 36px; }
    #work-container-current .work-card-till-toggle {
        font-size: .66rem;
        padding: 4px 8px;
        margin-top: 5px;
    }
    #work-container-current .work-row.work-row--board-member [data-field="support-doc"] {
        grid-column: unset;
        padding-top: 0;
        border-top: 0;
        margin-top: 0;
        max-width: none;
    }
    #work-container-current .work-entry-block,
    #work-container-current .work-fields.work-row {
        background: transparent;
        border: 0;
        border-radius: 0;
        box-shadow: none;
    }
    #work-container-current .work-fields.work-row:hover {
        border-color: transparent;
        box-shadow: none;
    }
    #work-container-current .work-row-head { display: none !important; }
    #work-container-current .work-board-member-panel,
    #work-container-current .work-board-member-panel.col-12 {
        margin: 0;
        padding: 8px 0 4px;
        background: transparent;
        border: 0;
        box-shadow: none;
    }
    #work-container-current .work-board-member-panel .row { row-gap: 10px; }
    #work-container-current .work-rows { padding: 0; gap: 0; }
    #work-container-current .work-entry-block {
        margin-bottom: 0;
        gap: 0;
    }
    #work-container-current .work-row.is-complete.work-row--expanded {
        border: 0 !important;
        box-shadow: none !important;
        background: transparent !important;
    }
    /* 7b has no summary table — never hide the inline form when marked complete */
    #work-container-current .work-row.is-complete:not(.work-row--expanded) {
        display: block !important;
    }
    #work-container-current .work-row.is-complete.work-row--compact .work-row-grid,
    #work-container-current .work-row.is-complete.work-row--compact .work-row-grid.row {
        display: flex !important;
    }
    #work-container-current .work-row-grid.row {
        padding-bottom: 0;
    }
    #work-container-current .work-row-done-bar {
        display: none !important;
    }
    #work-container-current .work-entry-block > .work-row-date-validation {
        min-height: 0;
        padding: 0;
    }
    .fs-segmented-toggle {
        display: inline-flex;
        align-items: stretch;
        border: 1px solid #b8cfe8;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 1px 3px rgba(3, 90, 179, 0.06);
    }
    .fs-segmented-opt {
        position: relative;
        margin: 0;
        cursor: pointer;
        user-select: none;
    }
    .fs-segmented-opt input {
        position: absolute;
        opacity: 0;
        width: 0;
        height: 0;
        pointer-events: none;
    }
    .fs-7b-board-toggle .fs-segmented-opt input {
        margin: 0;
        padding: 0;
        border: 0;
    }
    .fs-7b-board-toggle .fs-segmented-opt span {
        color: #5a7299;
        background: #fff;
        border-right: 1px solid #e3eaf5;
    }
    .fs-7b-board-toggle .fs-segmented-opt:last-child span { border-right: 0; }
    .fs-7b-board-toggle .fs-segmented-opt.is-active span,
    .fs-7b-board-toggle .fs-segmented-opt input:checked + span {
        background: #035ab3;
        color: #fff;
    }
    .fs-7b-mode-board #work-container-current .work-card-field:has(.work-employment-type),
    #work-container-current [data-field="contractor-cat"],
    #work-container-current [data-field="licence-number"],
    #work-container-current [data-field="work-nature"],
    #work-container-current [data-field="voltage-level"],
    #work-container-current [data-field="transformer-kva"],
    #work-container-current [data-field="relieve"] {
        display: none !important;
    }
    .fs-7b-mode-standard #work-container-current option[value="board_member_tnelb"] {
        display: none;
    }
    #work-container-current .work-row-remove,
    #work-exp-summary-panel-current .work-row-remove {
        display: none !important;
    }
