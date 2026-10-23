@include('include.header')

<style>

    /* ============ Overlay (dimmed page behind, like the mockup) ============ */
    .register-form {
        position: relative;
        display: block;
        padding: 40px 16px 60px;
        background: #eef6fb;
        font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
    }

    .register-form > .auto-container {
        width: min(1530px, 100%);
        max-width: none !important;
        padding: 0;
        margin: auto;
    }

    /* ============ Card ============ */
    .tnelb-wiz {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 28px rgba(13, 71, 161, 0.12);
        overflow: hidden;
        color: #12263f;
    }

    /* ============ Header ============ */
    .tnelb-wiz-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 12px 28px 12px 28px;
        background: #f3f8fd;
        border-bottom: 1px solid #e2eaf2;
    }

    .tnelb-wiz-head-left {
        display: flex;
        align-items: center;
        gap: 22px;
        min-width: 0;
    }

    .tnelb-wiz-avatar {
        width: 64px;
        height: 64px;
        border-radius: 50%;
        background: #dcecfa;
        color: #1565d8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 26px;
        flex-shrink: 0;
    }

    .tnelb-wiz-head h2 {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #0e2038;
        line-height: 1.2;
    }

    .tnelb-wiz-head p {
        margin: 2px 0 0;
        font-size: 16px;
        color: #3d4f63;
    }

    .tnelb-wiz-close {
        width: 40px;
        height: 40px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #12263f;
        font-size: 30px;
        line-height: 1;
        border-radius: 50%;
        text-decoration: none;
    }

    .tnelb-wiz-close:hover {
        background: #e6eef6;
        color: #12263f;
        text-decoration: none;
    }

    /* ============ Stepper ============ */
    .tnelb-wiz-stepper {
        position: relative;
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        padding: 12px 24px 4px;
        margin: 0 auto;
    }

    .tnelb-wiz-step {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 6px;
        font-size: 16px;
        font-weight: 600;
        color: #3d4f63;
        position: relative;
        z-index: 1;
    }

    .tnelb-wiz-step span {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #a9b4c1;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        font-weight: 700;
        transition: background .3s;
    }

    .tnelb-wiz-step.is-active span,
    .tnelb-wiz-step.is-done span {
        background: #1565d8;
    }

    .tnelb-wiz-step.is-active,
    .tnelb-wiz-step.is-done {
        color: #12263f;
    }

    .tnelb-wiz-line {
        position: absolute;
        top: 32px;
        height: 3px;
        background: #d3dbe4;
        border-radius: 99px;
        overflow: hidden;
        z-index: 0;
    }

    .tnelb-wiz-line.l1 { left: calc(16.667% + 24px + 24px); right: calc(50% + 24px + 0px); }
    .tnelb-wiz-line.l2 { left: calc(50% + 24px + 24px); right: calc(16.667% + 24px - 0px); }

    .tnelb-wiz-line i {
        display: block;
        height: 100%;
        width: 0;
        background: #1565d8;
        transition: width .4s ease;
    }

    /* ============ Panels ============ */
    .tnelb-wiz-body {
        position: relative;
        display: grid;
        grid-template-columns: 1.23fr 1.22fr 1fr;
        gap: 18px;
        padding: 16px 22px 22px;
        align-items: stretch;
    }

    .tnelb-wiz-panel {
        background: #f7fbfe;
        border: 1px solid #e1eaf3;
        border-radius: 8px;
        padding: 18px 22px 22px;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }

    .tnelb-wiz-panel:not(.is-active) {
        user-select: none;
    }

    /* Optional: dim locked steps (uncomment)
    .tnelb-wiz-panel:not(.is-active) { opacity: .6; }
    */

    .tnelb-wiz-icon {
        width: 80px;
        height: 80px;
        margin: 0 auto 10px;
        border-radius: 50%;
        background: #d6e8f9;
        color: #1565d8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        flex-shrink: 0;
    }

    .tnelb-wiz-kicker {
        color: #0b63e0;
        font-weight: 600;
        font-size: 18px;
        text-align: center;
        line-height: 1.3;
    }

    .tnelb-wiz-panel h3 {
        margin: 0;
        font-size: 26px;
        font-weight: 800;
        color: #0e2038;
        text-align: center;
        line-height: 1.25;
    }

    .tnelb-wiz-lead {
        margin: 2px 0 0;
        font-size: 15px;
        color: #3d4f63;
        text-align: center;
    }

    /* Step 2 header is left-aligned beside the icon */
    .tnelb-wiz-headrow {
        display: flex;
        align-items: center;
        gap: 22px;
        margin-bottom: 18px;
    }

    .tnelb-wiz-headrow .tnelb-wiz-icon { margin: 0; }
    .tnelb-wiz-headrow .tnelb-wiz-kicker,
    .tnelb-wiz-headrow h3,
    .tnelb-wiz-headrow .tnelb-wiz-lead { text-align: left; }
    .tnelb-wiz-headrow .tnelb-wiz-kicker { font-size: 16px; }
    .tnelb-wiz-headrow h3 { font-size: 24px; }
    .tnelb-wiz-headrow .tnelb-wiz-lead { font-size: 14px; }

    /* ============ Fields ============ */
    .tnelb-wiz-field { margin-bottom: 14px; text-align: left; }

    .tnelb-wiz-field > label,
    .tnelb-wiz-label {
        display: block;
        font-size: 15px;
        font-weight: 700;
        color: #0e2038;
        margin-bottom: 6px;
    }

    .tnelb-wiz-field label b,
    .tnelb-wiz-label b { color: #e11d2e; font-weight: 700; }

    .tnelb-wiz .form-control {
        height: 42px;
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        box-shadow: none;
        font-size: 15px;
        color: #12263f;
        background-color: #fff;
        padding: 6px 12px;
    }

    .tnelb-wiz .form-control::placeholder { color: #8b98a7; }

    .tnelb-wiz textarea.form-control {
        height: auto;
        min-height: 86px;
        padding-top: 10px;
        resize: vertical;
    }

    .tnelb-wiz .form-control:focus {
        border-color: #1565d8;
        box-shadow: 0 0 0 3px rgba(21, 101, 216, .14);
    }

    .tnelb-wiz .text-danger {
        display: block;
        font-size: 12.5px;
        margin-top: 4px;
    }

    .tnelb-wiz-phone {
        display: grid;
        grid-template-columns: 118px 1fr;
        gap: 20px;
    }

    .tnelb-wiz-cc {
        height: 44px;
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 12px;
        font-size: 15px;
        color: #12263f;
    }

    .tnelb-wiz-cc small { font-size: 12px; color: #4a5b6c; }
    .tnelb-wiz-phone .form-control { height: 44px; }

    .tnelb-wiz-send {
        width: 100%;
        height: 48px;
        border: 0;
        border-radius: 4px;
        background: #0a5fe0;
        color: #fff;
        font-size: 16px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-top: 4px;
        cursor: pointer;
    }

    .tnelb-wiz-send:hover { background: #0852c2; }

    /* OTP */
    .tnelb-otp-wrap { margin-top: 38px; }

    .tnelb-otp-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
    }

    .tnelb-otp-boxes { display: flex; gap: 8px; }

    .tnelb-otp-boxes input {
        width: 46px;
        height: 48px;
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        background: #fff;
        text-align: center;
        font-size: 20px;
        font-weight: 700;
        color: #12263f;
        padding: 0;
    }

    .tnelb-otp-boxes input:focus {
        outline: 0;
        border-color: #1565d8;
        box-shadow: 0 0 0 3px rgba(21, 101, 216, .14);
    }

    .tnelb-otp-boxes input:disabled { background: #fff; cursor: not-allowed; }

    .tnelb-otp-resend {
        color: #3d4f63;
        font-size: 15px;
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .tnelb-otp-resend button {
        border: 0;
        background: none;
        color: #0a5fe0;
        font-weight: 700;
        padding: 0;
        cursor: pointer;
    }

    .tnelb-wiz-info {
        margin-top: auto;
        background: #e4f0fc;
        color: #0f3f78;
        border-radius: 4px;
        padding: 16px 18px;
        font-size: 15px;
        display: flex;
        gap: 12px;
        align-items: center;
    }

    .tnelb-wiz-info i { color: #1a7be6; font-size: 22px; }

    /* Gender */
    .tnelb-wiz-gender {
        display: flex;
        flex-wrap: wrap;
        gap: 12px 30px;
        align-items: center;
        min-height: 36px;
        padding-left: 4px;
    }

    .tnelb-wiz-gender label {
        margin: 0;
        font-size: 15px;
        font-weight: 600;
        color: #12263f;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }

    .tnelb-wiz-gender input {
        width: 20px;
        height: 20px;
        accent-color: #1565d8;
        margin: 0;
    }

    .tnelb-wiz-split {
        display: grid;
        grid-template-columns: 1.3fr 1fr;
        gap: 22px;
    }

    .tnelb-wiz-split.even { grid-template-columns: 1fr 1fr; }

    .tnelb-wiz-name {
        display: grid;
        grid-template-columns: 64px 1fr;
        gap: 8px;
    }

    .tnelb-wiz-name select.form-control { padding: 6px 4px 6px 10px; }

    .tnelb-input-icon { position: relative; }

    .tnelb-input-icon i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #5b6b7c;
        font-size: 16px;
    }

    .tnelb-input-icon .form-control { padding-left: 46px; }

    /* Nav buttons */
    #registerform {
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .tnelb-wiz-nav {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: auto;
        padding-top: 10px;
    }

    .tnelb-wiz-btn {
        min-width: 124px;
        height: 48px;
        padding: 0 22px;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        cursor: pointer;
        text-decoration: none;
    }

    .tnelb-wiz-btn.primary { background: #0a5fe0; color: #fff; border: 0; }
    .tnelb-wiz-btn.primary:hover { background: #0852c2; color: #fff; text-decoration: none; }
    .tnelb-wiz-btn.primary:disabled { opacity: .7; cursor: not-allowed; }
    .tnelb-wiz-btn.ghost { background: #fff; color: #12263f; border: 1px solid #cfd9e4; }
    .tnelb-wiz-btn.ghost:hover { background: #f3f7fb; }

    /* ============ Chevron connectors ============ */
    .tnelb-wiz-chevron {
        position: absolute;
        top: 50%;
        width: 48px;
        height: 48px;
        margin: -24px 0 0 -24px;
        border-radius: 50%;
        border: 1px solid #d3dde8;
        background: #fff;
        color: #0a5fe0;
        font-size: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(13, 40, 80, .12);
        padding: 0;
        z-index: 5;
    }

    .tnelb-wiz-chevron.c1 { left: calc(22px + (100% - 44px - 36px) * 0.3547 + 9px + 0px); }
    .tnelb-wiz-chevron.c2 { left: calc(22px + (100% - 44px - 36px) * 0.7094 + 18px + 9px); }

    button.tnelb-wiz-chevron { cursor: pointer; }
    button.tnelb-wiz-chevron:disabled { color: #a9b4c1; cursor: not-allowed; }
    button.tnelb-wiz-chevron:not(:disabled):hover { background: #eef5fd; }

    /* ============ Success panel ============ */
    .tnelb-success-art {
        position: relative;
        width: 240px;
        height: 150px;
        margin: 22px auto 6px;
        flex-shrink: 0;
    }

    .tnelb-success-mark {
        position: absolute;
        left: 50%;
        top: 50%;
        width: 98px;
        height: 98px;
        margin: -49px 0 0 -49px;
        border-radius: 50%;
        background: #27a85b;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 46px;
    }

    .tnelb-success-art em {
        position: absolute;
        width: 8px;
        height: 8px;
        transform: rotate(45deg);
    }

    .tnelb-success-card {
        background: #e9f6ee;
        border-radius: 6px;
        padding: 14px 20px;
        margin: 22px 0 18px;
        color: #1f5a3a;
    }

    .tnelb-success-card > div {
        display: grid;
        grid-template-columns: 26px 118px 14px 1fr;
        align-items: center;
        padding: 9px 0;
        font-size: 15px;
    }

    .tnelb-success-card > div i { color: #2b6a47; font-size: 17px; }
    .tnelb-success-card > div .sep { color: #6b8f7a; }
    .tnelb-success-card > div strong { color: #0e2038; font-weight: 500; word-break: break-all; }

    .tnelb-success-note {
        color: #0c8a3c;
        font-weight: 600;
        font-size: 15px;
        text-align: center;
        margin: 0 0 14px;
    }

    .tnelb-wiz-panel .tnelb-wiz-btn.block { width: 100%; }

    /* ============ Responsive ============ */
    @media (max-width: 1100px) {
        .tnelb-wiz-body { grid-template-columns: 1fr; padding: 14px; }
        .tnelb-wiz-chevron { display: none; }
        .tnelb-wiz-panel { min-height: 0; }
        .tnelb-wiz-panel:not(.is-active) { display: none; }
        .tnelb-wiz-panel { max-width: 640px; width: 100%; margin: 0 auto; }
        .tnelb-otp-wrap { margin-top: 24px; }
        .tnelb-wiz-info { margin-top: 18px; }
        .tnelb-wiz-nav { margin-top: 16px; }
        .tnelb-wiz-mobile-next { display: inline-flex !important; }
    }

    .tnelb-wiz-mobile-next { display: none; margin-top: 14px; }

    @media (max-width: 640px) {
        .register-form { padding: 20px 10px 40px; }
        .tnelb-wiz-head { padding: 10px 14px; }
    .tnelb-wiz-head-left { gap: 12px; }
        .tnelb-wiz-avatar { width: 46px; height: 46px; font-size: 20px; }
        .tnelb-wiz-head h2 { font-size: 22px; }
        .tnelb-wiz-head p { font-size: 12px; }
        .tnelb-wiz-stepper { padding: 12px 6px 0; }
        .tnelb-wiz-step { font-size: 12px; text-align: center; }
        .tnelb-wiz-step span { width: 32px; height: 32px; font-size: 14px; }
        .tnelb-wiz-line { top: 28px; }
        .tnelb-wiz-line.l1 { left: calc(16.667% + 22px); right: calc(50% + 22px); }
        .tnelb-wiz-line.l2 { left: calc(50% + 22px); right: calc(16.667% + 22px); }
        .tnelb-wiz-panel { padding: 16px 14px 18px; }
        .tnelb-wiz-split, .tnelb-wiz-split.even { grid-template-columns: 1fr; gap: 0; }
        .tnelb-wiz-phone { grid-template-columns: 96px 1fr; gap: 10px; }
        .tnelb-otp-boxes input { width: 38px; height: 44px; }
        .tnelb-wiz-headrow { gap: 14px; }
        .tnelb-wiz-headrow .tnelb-wiz-icon { width: 60px; height: 60px; font-size: 26px; }
        .tnelb-success-card > div { grid-template-columns: 24px 98px 10px 1fr; font-size: 13px; }
    }

    /* ============ Multi-step wizard: one step at a time ============ */
    .register-form > .auto-container { width: min(760px, 100%); }

    .tnelb-wiz-body {
        display: block;
        padding: 14px 22px 22px;
    }

    .tnelb-wiz-panel {
        width: 100%;
        max-width: none;
        margin: 0;
        min-height: 0;
    }

    .tnelb-wiz-panel:not(.is-active) { display: none; }

    .tnelb-wiz-panel.is-active { animation: wizIn .35s ease; }

    @keyframes wizIn {
        from { opacity: 0; transform: translateX(24px); }
        to   { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .tnelb-wiz-panel.is-active { animation: none; }
    }

    .tnelb-wiz-info { margin-top: 22px; }
    .tnelb-wiz-panel[data-panel="1"] .tnelb-wiz-nav { margin-top: 20px; }
    .tnelb-wiz-panel[data-panel="1"] .tnelb-wiz-nav .primary { margin-left: auto; }
    .tnelb-wiz-nav .tnelb-wiz-btn.primary:only-child { margin-left: auto; }
    #registerform { flex: none; }
    .tnelb-wiz-nav { margin-top: 12px; }
    .tnelb-otp-wrap { margin-top: 26px; }

    /* ============ Compact size ============ */
    .register-form { padding: 28px 16px 44px; }
    .register-form > .auto-container { width: min(600px, 100%); }

    .tnelb-wiz-stepper { padding: 10px 0 2px; }
    .tnelb-wiz-line { top: 25px; }
    .tnelb-wiz-line.l1 { left: calc(16.667% + 24px); right: calc(50% + 24px); }
    .tnelb-wiz-line.l2 { left: calc(50% + 24px); right: calc(16.667% + 24px); }

    @media (min-width: 641px) {
        .tnelb-wiz-head { padding: 10px 20px; }
        .tnelb-wiz-head-left { gap: 14px; }
        .tnelb-wiz-avatar { width: 48px; height: 48px; font-size: 20px; }
        .tnelb-wiz-head h2 { font-size: 19px; }
        .tnelb-wiz-head p { font-size: 13px; margin-top: 0; }

        .tnelb-wiz-step { font-size: 13px; gap: 4px; }
        .tnelb-wiz-step span { width: 32px; height: 32px; font-size: 14px; }

        .tnelb-wiz-body { padding: 12px 16px 16px; }
        .tnelb-wiz-panel { padding: 14px 18px 16px; }

        .tnelb-wiz-icon { width: 60px; height: 60px; font-size: 28px; margin-bottom: 6px; }
        .tnelb-wiz-kicker { font-size: 14px; }
        .tnelb-wiz-panel h3 { font-size: 20px; }
        .tnelb-wiz-lead { font-size: 13px; }
        .tnelb-wiz-panel[data-panel="1"] .tnelb-wiz-lead { margin-bottom: 18px !important; }

        .tnelb-wiz-headrow { gap: 14px; margin-bottom: 12px; }
        .tnelb-wiz-headrow .tnelb-wiz-kicker { font-size: 13px; }
        .tnelb-wiz-headrow h3 { font-size: 20px; }
        .tnelb-wiz-headrow .tnelb-wiz-lead { font-size: 12.5px; }

        .tnelb-wiz-field { margin-bottom: 10px; }
        .tnelb-wiz-field > label, .tnelb-wiz-label { font-size: 13.5px; margin-bottom: 4px; }
        .tnelb-wiz .form-control { height: 38px; font-size: 14px; padding: 4px 10px; }
        .tnelb-wiz textarea.form-control { height: auto; min-height: 64px; padding-top: 8px; }
        .tnelb-wiz .text-danger { font-size: 12px; margin-top: 2px; }

        .tnelb-wiz-phone { grid-template-columns: 100px 1fr; gap: 12px; }
        .tnelb-wiz-cc { height: 40px; font-size: 14px; padding: 0 10px; }
        .tnelb-wiz-phone .form-control { height: 40px; }
        .tnelb-wiz-send { height: 42px; font-size: 15px; }

        .tnelb-otp-wrap { margin-top: 18px; }
        .tnelb-otp-boxes { gap: 6px; }
        .tnelb-otp-boxes input { width: 40px; height: 42px; font-size: 18px; }
        .tnelb-otp-resend { font-size: 13px; }

        .tnelb-wiz-info { margin-top: 16px; padding: 11px 14px; font-size: 13px; }
        .tnelb-wiz-info i { font-size: 18px; }

        .tnelb-wiz-gender { gap: 8px 22px; min-height: 28px; }
        .tnelb-wiz-gender label { font-size: 14px; }
        .tnelb-wiz-gender input { width: 18px; height: 18px; }

        .tnelb-wiz-split { gap: 14px; }
        .tnelb-wiz-name { grid-template-columns: 58px 1fr; }
        .tnelb-input-icon .form-control { padding-left: 38px; }
        .tnelb-input-icon i { left: 11px; font-size: 14px; }

        .tnelb-wiz-btn { min-width: 108px; height: 42px; font-size: 15px; padding: 0 18px; }
        .tnelb-wiz-nav { margin-top: 8px; }

        .tnelb-success-art { transform: scale(.78); margin: -4px auto -18px; }
        .tnelb-success-card { padding: 8px 16px; margin: 14px 0 12px; }
        .tnelb-success-card > div { padding: 6px 0; font-size: 14px; grid-template-columns: 24px 112px 12px 1fr; }
        .tnelb-success-note { font-size: 14px; margin-bottom: 10px; }
    }

    /* ============ Fit-in-one-screen (no page scroll) ============ */
    .register-form { padding: 12px 16px 12px; }
    .register-form > .auto-container { width: min(700px, 100%); }

    .tnelb-wiz-stepper {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 8px 24px;
    }
    .tnelb-wiz-step { flex-direction: row; gap: 8px; font-size: 13px; white-space: nowrap; }
    .tnelb-wiz-step span { width: 28px; height: 28px; font-size: 13px; }
    .tnelb-wiz-line,
    .tnelb-wiz-line.l1,
    .tnelb-wiz-line.l2 { position: static; top: auto; left: auto; right: auto; flex: 1; min-width: 12px; }

    @media (max-width: 640px) {
        .tnelb-wiz-stepper { padding: 8px 12px; gap: 6px; }
        .tnelb-wiz-step:not(.is-active) { font-size: 0; gap: 0; }
    }

    @media (min-width: 641px) {
        .tnelb-wiz-head { padding: 8px 20px; }
        .tnelb-wiz-avatar { width: 40px; height: 40px; font-size: 17px; }
        .tnelb-wiz-head h2 { font-size: 17px; }
        .tnelb-wiz-head p { font-size: 12.5px; }

        .tnelb-wiz-body { padding: 4px 16px 14px; }
        .tnelb-wiz-panel { padding: 12px 18px 14px; }

        .tnelb-wiz-headrow { gap: 12px; margin-bottom: 10px; }
        .tnelb-wiz-icon { width: 44px; height: 44px; font-size: 20px; }
        .tnelb-wiz-headrow .tnelb-wiz-kicker { font-size: 12px; }
        .tnelb-wiz-headrow h3 { font-size: 18px; }
        .tnelb-wiz-headrow .tnelb-wiz-lead { font-size: 12px; margin: 0; }

        .tnelb-wiz-field { margin-bottom: 8px; }
        .tnelb-wiz-field > label, .tnelb-wiz-label { font-size: 13px; margin-bottom: 3px; }
        .tnelb-wiz .form-control { height: 36px; font-size: 13.5px; }
        .tnelb-wiz textarea.form-control { min-height: 50px; height: 50px; padding-top: 6px; resize: none; }
        .tnelb-wiz .text-danger { font-size: 11.5px; margin-top: 1px; }

        .tnelb-wiz-cc, .tnelb-wiz-phone .form-control { height: 38px; }
        .tnelb-wiz-send { height: 40px; font-size: 14px; margin-top: 0; }
        .tnelb-otp-wrap { margin-top: 12px; }
        .tnelb-otp-boxes input { width: 38px; height: 40px; font-size: 17px; }
        .tnelb-wiz-info { margin-top: 10px; padding: 9px 12px; font-size: 12.5px; }

        .tnelb-wiz-gender { min-height: 36px; gap: 6px 18px; padding-left: 0; }
        .tnelb-wiz-gender label { font-size: 13.5px; }
        .tnelb-wiz-gender input { width: 17px; height: 17px; }

        .tnelb-wiz-btn { height: 38px; min-width: 100px; font-size: 14px; }
        .tnelb-wiz-nav { margin-top: 6px; padding-top: 0; }

        /* Step 2 as a 2-column grid */
        #registerform {
            display: grid;
            grid-template-columns: 1fr 1fr;
            column-gap: 14px;
            align-items: start;
        }
        #registerform .tnelb-wiz-split { display: contents; }
        #registerform .f-address { grid-column: 1 / -1; order: 7; }
        #registerform #districtRow { grid-column: 1 / -1; order: 8; }
        #registerform .tnelb-wiz-nav { grid-column: 1 / -1; order: 9; }

        /* Step 3 */
        .tnelb-success-art { transform: scale(.6); margin: -14px auto -26px; }
        .tnelb-wiz-panel[data-panel="3"] .tnelb-wiz-kicker { font-size: 13px; }
        .tnelb-wiz-panel[data-panel="3"] h3 { font-size: 19px; }
        .tnelb-success-card { padding: 4px 16px; margin: 10px 0 8px; }
        .tnelb-success-card > div { padding: 5px 0; font-size: 13.5px; }
        .tnelb-success-note { font-size: 13px; margin-bottom: 8px; }
    }
</style>




<section class="register-form">
    <div class="auto-container">
        <div class="tnelb-wiz" id="registerWizard">

            <!-- Header -->
            <div class="tnelb-wiz-head">
                <div class="tnelb-wiz-head-left">
                    <div class="tnelb-wiz-avatar"><i class="fa fa-user-plus"></i></div>
                    <div>
                        <h2>Applicant Registration</h2>
                        <p>Create your account to access TNELB online services</p>
                    </div>
                </div>
            </div>

            <!-- Stepper -->
            <div class="tnelb-wiz-stepper">
                <div class="tnelb-wiz-step is-active" data-step-label="1"><span>1</span> Mobile Verification</div>
                <div class="tnelb-wiz-line"><i id="wizLine1"></i></div>
                <div class="tnelb-wiz-step" data-step-label="2"><span>2</span> Registration Form</div>
                <div class="tnelb-wiz-line"><i id="wizLine2"></i></div>
                <div class="tnelb-wiz-step" data-step-label="3"><span>3</span> Completed</div>
            </div>

            <!-- Panels -->
            <div class="tnelb-wiz-body">

                <!-- STEP 1 -->
                <div class="tnelb-wiz-panel is-active" data-panel="1">
                    <div class="tnelb-wiz-headrow">
                        <div class="tnelb-wiz-icon"><i class="fa fa-mobile"></i></div>
                        <div>
                            <div class="tnelb-wiz-kicker">Step 1 of 3</div>
                            <h3>Mobile Number Verification</h3>
                            <p class="tnelb-wiz-lead">Enter your mobile number and verify with OTP to proceed.</p>
                        </div>
                    </div>

                    <div class="tnelb-wiz-field">
                        <label for="PhoneNo">Mobile Number <b>*</b></label>
                        <div class="tnelb-wiz-phone">
                            <div class="tnelb-wiz-cc"><span style="display:inline-flex;align-items:center;gap:8px;"><svg width="26" height="18" viewBox="0 0 26 18" aria-hidden="true"><rect width="26" height="6" fill="#ff9933"/><rect y="6" width="26" height="6" fill="#fff"/><rect y="12" width="26" height="6" fill="#138808"/><circle cx="13" cy="9" r="2.3" fill="none" stroke="#000080" stroke-width=".8"/></svg> +91</span> <small class="fa fa-chevron-down"></small></div>
                            <input type="text" id="PhoneNo" name="PhoneNo" class="form-control" maxlength="10" inputmode="numeric" placeholder="Enter 10 digit mobile number">
                        </div>
                        <span id="PhoneNoError" class="text-danger"></span>
                    </div>

                    <button type="button" class="tnelb-wiz-send" id="sendOtpBtn">
                        <i class="fa fa-paper-plane"></i> Send OTP
                    </button>

                    <div class="tnelb-otp-wrap" id="otpSection">
                        <div class="tnelb-wiz-field">
                            <label>Enter OTP <b>*</b></label>
                            <div class="tnelb-otp-row">
                                <div class="tnelb-otp-boxes">
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                    <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" disabled>
                                </div>
                                <div class="tnelb-otp-resend">
                                    <span id="otpTimerWrap"><i class="fa fa-clock-o"></i> Resend OTP in <span id="otpTimer">00:30</span></span>
                                    <button type="button" id="resendOtpBtn" style="display:none;">Resend OTP</button>
                                </div>
                            </div>
                            <span id="OtpError" class="text-danger"></span>
                        </div>
                    </div>

                    <div class="tnelb-wiz-info">
                        <i class="fa fa-info-circle"></i>
                        <span>OTP will be sent to the entered mobile number via SMS.</span>
                    </div>
                    <div class="tnelb-wiz-nav">
                        <button type="button" class="tnelb-wiz-btn primary" id="step1Next" disabled>Next <i class="fa fa-arrow-right"></i></button>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div class="tnelb-wiz-panel" data-panel="2">
                    <div class="tnelb-wiz-headrow">
                        <div class="tnelb-wiz-icon"><i class="fa fa-user"></i></div>
                        <div>
                            <div class="tnelb-wiz-kicker">Step 2 of 3</div>
                            <h3>Registration Details</h3>
                            <p class="tnelb-wiz-lead">Fill in your personal information to create your account.</p>
                        </div>
                    </div>

                    <form id="registerform" autocomplete="off">
                        @csrf
                        <div class="tnelb-wiz-split">
                            <div class="tnelb-wiz-field">
                                <label for="first_name">First Name <b>*</b></label>
                                <div class="tnelb-wiz-name">
                                    <select id="salutation" name="salutation" class="form-control">
                                        <option value="Mr">Mr</option>
                                        <option value="Ms">Ms</option>
                                        <option value="Mrs">Mrs</option>
                                    </select>
                                    <input type="text" id="first_name" name="first_name" class="form-control" placeholder="Enter your first name">
                                </div>
                                <span id="salutationError" class="text-danger"></span>
                                <span id="FirstNameError" class="text-danger"></span>
                            </div>
                            <div class="tnelb-wiz-field">
                                <label for="lastname">Last Name <b>*</b></label>
                                <input type="text" id="lastname" name="lastname" class="form-control" placeholder="Enter your last name">
                                <span id="lastnameError" class="text-danger"></span>
                            </div>
                            <div class="tnelb-wiz-field">
                                <label for="date_of_birth">Date of Birth <b>*</b></label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control">
                                <span id="DateOfBirthError" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="tnelb-wiz-field">
                            <span class="tnelb-wiz-label">Gender <b>*</b></span>
                            <div class="tnelb-wiz-gender">
                                <label for="Male"><input type="radio" id="Male" name="gender" value="Male" checked> Male</label>
                                <label for="Female"><input type="radio" id="Female" name="gender" value="Female"> Female</label>
                                <label for="Transgender"><input type="radio" id="Transgender" name="gender" value="Transgender"> Transgender</label>
                            </div>
                            <span id="GenderError" class="text-danger"></span>
                        </div>

                        <div class="tnelb-wiz-field">
                            <label for="EmailAddress">E-mail Address <b>*</b></label>
                            <div class="tnelb-input-icon">
                                <i class="fa fa-envelope"></i>
                                <input type="email" id="EmailAddress" name="EmailAddress" class="form-control" placeholder="Enter your e-mail address">
                            </div>
                            <span id="EmailError" class="text-danger"></span>
                        </div>

                        <div class="tnelb-wiz-field f-address">
                            <label for="Address">Address <b>*</b></label>
                            <textarea rows="3" id="Address" name="Address" class="form-control" placeholder="Enter your address"></textarea>
                            <span id="AddressError" class="text-danger"></span>
                        </div>

                        <div class="tnelb-wiz-split">
                            <div class="tnelb-wiz-field">
                                <label for="state">Select State <b>*</b></label>
                                <select id="state" name="state" class="form-control">
                                    <option value="">--- Select State ---</option>
                                    <option value="Andhra Pradesh">Andhra Pradesh</option>
                                    <option value="Arunachal Pradesh">Arunachal Pradesh</option>
                                    <option value="Assam">Assam</option>
                                    <option value="Bihar">Bihar</option>
                                    <option value="Chhattisgarh">Chhattisgarh</option>
                                    <option value="Goa">Goa</option>
                                    <option value="Gujarat">Gujarat</option>
                                    <option value="Haryana">Haryana</option>
                                    <option value="Himachal Pradesh">Himachal Pradesh</option>
                                    <option value="Jharkhand">Jharkhand</option>
                                    <option value="Karnataka">Karnataka</option>
                                    <option value="Kerala">Kerala</option>
                                    <option value="Madhya Pradesh">Madhya Pradesh</option>
                                    <option value="Maharashtra">Maharashtra</option>
                                    <option value="Manipur">Manipur</option>
                                    <option value="Meghalaya">Meghalaya</option>
                                    <option value="Mizoram">Mizoram</option>
                                    <option value="Nagaland">Nagaland</option>
                                    <option value="Odisha">Odisha</option>
                                    <option value="Punjab">Punjab</option>
                                    <option value="Rajasthan">Rajasthan</option>
                                    <option value="Sikkim">Sikkim</option>
                                    <option value="Tamil Nadu">Tamil Nadu</option>
                                    <option value="Telangana">Telangana</option>
                                    <option value="Tripura">Tripura</option>
                                    <option value="Uttar Pradesh">Uttar Pradesh</option>
                                    <option value="Uttarakhand">Uttarakhand</option>
                                    <option value="West Bengal">West Bengal</option>
                                    <option value="Andaman and Nicobar Islands">Andaman and Nicobar Islands</option>
                                    <option value="Chandigarh">Chandigarh</option>
                                    <option value="Dadra and Nagar Haveli and Daman and Diu">Dadra and Nagar Haveli and Daman and Diu</option>
                                    <option value="Lakshadweep">Lakshadweep</option>
                                    <option value="Delhi">Delhi</option>
                                    <option value="Puducherry">Puducherry</option>
                                    <option value="Jammu and Kashmir">Jammu and Kashmir</option>
                                    <option value="Ladakh">Ladakh</option>
                                </select>
                                <span id="StateError" class="text-danger"></span>
                            </div>
                            <div class="tnelb-wiz-field">
                                <label for="pincode">Pincode <b>*</b></label>
                                <input type="text" id="pincode" name="pincode" maxlength="6" inputmode="numeric" class="form-control" placeholder="Enter pincode">
                                <span id="PincodeError" class="text-danger"></span>
                            </div>
                        </div>

                        <div class="tnelb-wiz-field" id="districtRow" style="display:none;">
                            <label for="district">Select District <b>*</b></label>
                            <select id="district" name="district" class="form-control">
                                <option value="">---Select District ---</option>
                                <option value="Ariyalur">Ariyalur</option>
                                <option value="Chengalpattu">Chengalpattu</option>
                                <option value="Chennai">Chennai</option>
                                <option value="Coimbatore">Coimbatore</option>
                                <option value="Cuddalore">Cuddalore</option>
                                <option value="Dharmapuri">Dharmapuri</option>
                                <option value="Dindigul">Dindigul</option>
                                <option value="Erode">Erode</option>
                                <option value="Kancheepuram">Kancheepuram</option>
                                <option value="Kanyakumari">Kanyakumari</option>
                                <option value="Karur">Karur</option>
                                <option value="Krishnagiri">Krishnagiri</option>
                                <option value="Madurai">Madurai</option>
                                <option value="Nagapattinam">Nagapattinam</option>
                                <option value="Namakkal">Namakkal</option>
                                <option value="Nilgiris">Nilgiris</option>
                                <option value="Perambalur">Perambalur</option>
                                <option value="Pudukkottai">Pudukkottai</option>
                                <option value="Ramanathapuram">Ramanathapuram</option>
                                <option value="Salem">Salem</option>
                                <option value="Sivagangai">Sivagangai</option>
                                <option value="Tenkasi">Tenkasi</option>
                                <option value="Thanjavur">Thanjavur</option>
                                <option value="The Nilgiris">The Nilgiris</option>
                                <option value="Theni">Theni</option>
                                <option value="Tirunelveli">Tirunelveli</option>
                                <option value="Tiruppur">Tiruppur</option>
                                <option value="Tiruvallur">Tiruvallur</option>
                                <option value="Tiruvannamalai">Tiruvannamalai</option>
                                <option value="Vellore">Vellore</option>
                                <option value="Viluppuram">Viluppuram</option>
                                <option value="Virudhunagar">Virudhunagar</option>
                            </select>
                            <span id="DistrictError" class="text-danger"></span>
                        </div>

                        <div class="tnelb-wiz-nav">
                            <button type="button" class="tnelb-wiz-btn ghost" id="step2Back"><i class="fa fa-arrow-left"></i> Back</button>
                            <button type="submit" class="tnelb-wiz-btn primary" id="step2Next">Next <i class="fa fa-arrow-right"></i></button>
                        </div>
                    </form>
                </div>

                <!-- STEP 3 -->
                <div class="tnelb-wiz-panel" data-panel="3">
                    <div class="tnelb-success-art">
                        <em style="left:54px;top:18px;background:#e8324a;"></em>
                        <em style="left:26px;top:56px;background:#1fa59a;"></em>
                        <em style="left:12px;top:88px;background:#f5a623;"></em>
                        <em style="left:50px;top:120px;background:#e8324a;"></em>
                        <em style="left:150px;top:6px;background:#1a73e8;"></em>
                        <em style="left:186px;top:44px;background:#f5a623;"></em>
                        <em style="left:214px;top:80px;background:#4a49b8;"></em>
                        <em style="left:178px;top:116px;background:#e8324a;"></em>
                        <div class="tnelb-success-mark"><i class="fa fa-check"></i></div>
                    </div>
                    <div class="tnelb-wiz-kicker">Step 3 of 3</div>
                    <h3>Registration Successful!</h3>
                    <p class="tnelb-wiz-lead">Your account has been created successfully.</p>

                    <div class="tnelb-success-card">
                        <div><i class="fa fa-user"></i><span>Name</span><span class="sep">:</span><strong id="successName">-</strong></div>
                        <div><i class="fa fa-phone"></i><span>Mobile Number</span><span class="sep">:</span><strong id="successMobile">-</strong></div>
                        <div><i class="fa fa-envelope"></i><span>E-mail</span><span class="sep">:</span><strong id="successEmail">-</strong></div>
                    </div>

                    <p class="tnelb-success-note">You can now sign in to access TNELB online services.</p>
                    <a href="{{ route('login') }}" class="tnelb-wiz-btn primary block">
                        <i class="fa fa-sign-in"></i> Proceed to Sign In
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<footer class="main-footer">
@include('include.footer')
<script src="{{ url('assets/js/register.js') }}?v={{ filemtime(public_path('assets/js/register.js')) }}"></script>