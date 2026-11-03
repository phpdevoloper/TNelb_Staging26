@include('include.header')

<style>
    /* ============ CSS Custom Properties (Responsive Design Token) ============ */
    :root {
        /* Fluid sizing using clamp(min, preferred, max) */
        --padding-inline: clamp(10px, 3vw, 24px);
        --padding-block: clamp(16px, 5vh, 36px);
        --card-width: clamp(280px, 90vw, 640px);

        /* Typography scales fluidly with viewport */
        --font-kicker: clamp(12px, 1.8vw, 15px);
        --font-heading: clamp(16px, 2.8vw, 20px);
        --font-label: clamp(13px, 1.6vw, 14px);
        --font-body: clamp(13.5px, 1.8vw, 15px);
        --font-input: clamp(13.5px, 1.8vw, 14px);

        /* Spacing scales with viewport */
        --gap-field: clamp(9px, 1.5vw, 11px);
        --gap-component: clamp(10px, 2vw, 16px);
        --panel-padding: clamp(12px, 2.5vw, 16px);
        --header-padding: clamp(6px, 1.5vw, 9px);

        /* Touch targets (min 40-44px) */
        --input-height: clamp(36px, 9vw, 40px);
        --button-height: clamp(38px, 10vw, 42px);
        --otp-box-size: clamp(34px, 8vw, 40px);
    }

    /* ============ Base Styles ============ */
    .register-form {
        position: relative;
        display: block;
        padding: var(--padding-block) var(--padding-inline);
        background: #eef6fb;
        font-family: 'Inter', -apple-system, 'Segoe UI', Roboto, Arial, sans-serif;
        contain: layout style;
    }

    .register-form > .auto-container,
    .tnelb-wiz {
        display: flex;
        flex-direction: column;
    }

    .register-form > .auto-container {
        width: var(--card-width);
        max-width: 100%;
        padding: 0;
        margin: auto;
    }

    /* [hidden] must beat component display rules */
    .tnelb-wiz [hidden] {
        display: none !important;
    }

    /* Form-level error strip */
    .tnelb-wiz-alert {
        background: #fdecef;
        color: #8a1c2b;
    }

    .tnelb-wiz-alert i {
        color: #d6334c;
    }

    #registerform .tnelb-wiz-alert {
        grid-column: 1 / -1;
        order: 8;
    }

    /* ============ Card ============ */
    .tnelb-wiz {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 10px 28px rgba(13, 71, 161, 0.12);
        overflow: hidden;
        color: #12263f;
    }

    .tnelb-wiz > .tnelb-wiz-stepper {
        margin-inline: 0;
    }

    /* ============ Header ============ */
    .tnelb-wiz-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: clamp(12px, 2.5vw, 16px);
        padding: clamp(8px, 2vw, 12px) clamp(14px, 3vw, 28px);
        background: #f3f8fd;
        border-bottom: 1px solid #e2eaf2;
    }

    .tnelb-wiz-head-left {
        display: flex;
        align-items: center;
        gap: clamp(12px, 2.5vw, 22px);
        min-width: 0;
    }

    .tnelb-wiz-avatar {
        width: clamp(32px, 7vw, 48px);
        height: clamp(32px, 7vw, 48px);
        border-radius: 50%;
        background: #dcecfa;
        color: #1565d8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(14px, 3vw, 20px);
        flex-shrink: 0;
    }

    .tnelb-wiz-head h2 {
        margin: 0;
        font-size: clamp(18px, 3.5vw, 24px);
        font-weight: 700;
        color: #0e2038;
        line-height: 1.2;
    }

    .tnelb-wiz-head p {
        margin: clamp(2px, 0.5vh, 2px) 0 0;
        font-size: clamp(12.5px, 2vw, 16px);
        color: #3d4f63;
    }

    .tnelb-wiz-close {
        width: clamp(36px, 8vw, 40px);
        height: clamp(36px, 8vw, 40px);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: #12263f;
        font-size: clamp(24px, 5vw, 30px);
        line-height: 1;
        border-radius: 50%;
        text-decoration: none;
        flex-shrink: 0;
    }

    .tnelb-wiz-close:hover {
        background: #e6eef6;
        color: #12263f;
        text-decoration: none;
    }

    /* ============ Stepper ============ */
    .tnelb-wiz-stepper {
        position: relative;
        display: flex;
        align-items: center;
        gap: clamp(6px, 2vw, 10px);
        padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 24px) clamp(0px, 1vw, 4px);
        margin: 0 auto;
    }

    .tnelb-wiz-step {
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: clamp(6px, 1.5vw, 8px);
        font-size: clamp(13px, 2vw, 16px);
        font-weight: 600;
        color: #3d4f63;
        position: relative;
        z-index: 1;
        white-space: nowrap;
    }

    .tnelb-wiz-step span {
        width: clamp(26px, 5vw, 32px);
        height: clamp(26px, 5vw, 32px);
        border-radius: 50%;
        background: #a9b4c1;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(12px, 2vw, 14px);
        font-weight: 700;
        transition: background 0.3s ease;
        flex-shrink: 0;
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
        position: static;
        flex: 1;
        min-width: clamp(6px, 2vw, 12px);
        height: 3px;
        background: #d3dbe4;
        border-radius: 99px;
        overflow: hidden;
    }

    .tnelb-wiz-line i {
        display: block;
        height: 100%;
        width: 0;
        background: #1565d8;
        transition: width 0.4s ease;
    }

    /* ============ Panels (One-step-at-a-time wizard) ============ */
    .tnelb-wiz-body {
        position: relative;
        display: block;
        padding: clamp(10px, 2.5vw, 22px) clamp(12px, 3vw, 22px);
        align-items: stretch;
    }

    .tnelb-wiz-panel {
        background: #f7fbfe;
        border: 1px solid #e1eaf3;
        border-radius: 8px;
        padding: clamp(14px, 3vw, 22px) clamp(14px, 3vw, 24px);
        display: flex;
        flex-direction: column;
        min-width: 0;
        width: 100%;
        margin: 0;
        animation: wizIn 0.35s ease;
    }

    .tnelb-wiz-panel:not(.is-active) {
        display: none;
        user-select: none;
    }

    @keyframes wizIn {
        from { opacity: 0; transform: translateX(24px); }
        to   { opacity: 1; transform: none; }
    }

    @media (prefers-reduced-motion: reduce) {
        .tnelb-wiz-panel.is-active { animation: none; }
    }

    .tnelb-wiz-icon {
        width: clamp(44px, 10vw, 60px);
        height: clamp(44px, 10vw, 60px);
        margin: 0 auto clamp(5px, 1vw, 8px);
        border-radius: 50%;
        background: #d6e8f9;
        color: #1565d8;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(18px, 3.5vw, 28px);
        flex-shrink: 0;
    }

    .tnelb-wiz-kicker {
        color: #0b63e0;
        font-weight: 600;
        font-size: var(--font-kicker);
        text-align: center;
        line-height: 1.3;
    }

    .tnelb-wiz-panel h3 {
        margin: 0;
        font-size: clamp(18px, 3.5vw, 26px);
        font-weight: 800;
        color: #0e2038;
        text-align: center;
        line-height: 1.25;
    }

    .tnelb-wiz-lead {
        margin: clamp(2px, 0.5vh, 4px) 0 0;
        font-size: clamp(13.5px, 2vw, 16px);
        color: #3d4f63;
        text-align: center;
    }

    /* Step 2 header is left-aligned beside the icon */
    .tnelb-wiz-headrow {
        display: flex;
        align-items: center;
        gap: clamp(12px, 2.5vw, 22px);
        margin-bottom: clamp(12px, 2.5vw, 18px);
    }

    .tnelb-wiz-headrow .tnelb-wiz-icon { margin: 0; }
    .tnelb-wiz-headrow .tnelb-wiz-kicker,
    .tnelb-wiz-headrow h3,
    .tnelb-wiz-headrow .tnelb-wiz-lead { text-align: left; }
    .tnelb-wiz-headrow .tnelb-wiz-kicker { font-size: clamp(13px, 2vw, 17px); }
    .tnelb-wiz-headrow h3 { font-size: clamp(18px, 3.5vw, 26px); }
    .tnelb-wiz-headrow .tnelb-wiz-lead { font-size: clamp(13px, 2vw, 15px); }

    /* ============ Fields ============ */
    .tnelb-wiz-field {
        margin-bottom: var(--gap-field);
        text-align: left;
    }

    .tnelb-wiz-field > label,
    .tnelb-wiz-label {
        display: block;
        font-size: var(--font-label);
        font-weight: 700;
        color: #0e2038;
        margin-bottom: clamp(4px, 1vw, 6px);
    }

    .tnelb-wiz-field label b,
    .tnelb-wiz-label b {
        color: #e11d2e;
        font-weight: 700;
    }

    .tnelb-wiz .form-control {
        height: var(--input-height);
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        box-shadow: none;
        font-size: var(--font-input);
        color: #12263f;
        background-color: #fff;
        padding: clamp(5px, 1vw, 8px) clamp(10px, 2vw, 12px);
    }

    .tnelb-wiz .form-control::placeholder {
        color: #8b98a7;
        font-size: 1em;
    }

    .tnelb-wiz textarea.form-control {
        height: auto;
        min-height: clamp(56px, 15vw, 86px);
        padding-top: clamp(8px, 1.5vw, 10px);
        resize: vertical;
    }

    .tnelb-wiz .form-control:focus {
        border-color: #1565d8;
        box-shadow: 0 0 0 3px rgba(21, 101, 216, 0.14);
    }

    .tnelb-wiz .text-danger {
        display: block;
        font-size: clamp(12px, 1.5vw, 12.5px);
        margin-top: clamp(2px, 0.5vw, 4px);
    }

    .tnelb-wiz-phone {
        display: grid;
        grid-template-columns: clamp(85px, 20vw, 118px) 1fr;
        gap: clamp(8px, 2vw, 20px);
    }

    .tnelb-wiz-cc {
        height: clamp(40px, 11vw, 44px);
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        background: #fff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 clamp(10px, 2vw, 12px);
        font-size: clamp(14px, 2vw, 15px);
        color: #12263f;
    }

    .tnelb-wiz-cc small {
        font-size: clamp(11px, 1.5vw, 12px);
        color: #4a5b6c;
    }

    .tnelb-wiz-phone .form-control {
        height: clamp(40px, 11vw, 44px);
    }

    .tnelb-wiz-send {
        width: 100%;
        height: var(--button-height);
        border: 0;
        border-radius: 4px;
        background: #0a5fe0;
        color: #fff;
        font-size: clamp(15px, 2.2vw, 16px);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: clamp(8px, 1.5vw, 10px);
        margin-top: clamp(2px, 0.5vw, 4px);
        cursor: pointer;
    }

    .tnelb-wiz-send:hover {
        background: #0852c2;
    }

    /* OTP Section */
    /* .tnelb-otp-wrap {
        margin-top: clamp(16px, 4vw, 38px);
    } */

    #otpSection {
        visibility: hidden;
        opacity: 0;
        transform: translateY(-6px);
        transition: opacity 0.28s, transform 0.28s, visibility 0.28s;
    }

    #otpSection.is-visible {
        visibility: visible;
        opacity: 1;
        transform: translateY(0);
    }

    .tnelb-otp-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: clamp(8px, 2vw, 12px);
        flex-wrap: wrap;
    }

    .tnelb-otp-boxes {
        display: flex;
        gap: clamp(5px, 1.5vw, 8px);
    }

    .tnelb-otp-boxes input {
        width: var(--otp-box-size);
        height: var(--otp-box-size);
        border: 1px solid #cfd9e4;
        border-radius: 4px;
        background: #fff;
        text-align: center;
        font-size: clamp(17px, 3.5vw, 20px);
        font-weight: 700;
        color: #12263f;
        padding: 0;
    }

    .tnelb-otp-boxes input:focus {
        outline: 0;
        border-color: #1565d8;
        box-shadow: 0 0 0 3px rgba(21, 101, 216, 0.14);
    }

    .tnelb-otp-boxes input:disabled {
        background: #fff;
        cursor: not-allowed;
    }

    .tnelb-otp-resend {
        color: #3d4f63;
        font-size: clamp(13.5px, 2vw, 15px);
        white-space: nowrap;
        display: inline-flex;
        align-items: center;
        gap: clamp(6px, 1vw, 8px);
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
        padding: clamp(11px, 2.5vw, 10px) clamp(13px, 3vw, 10px);
        font-size: clamp(13.5px, 2vw, 12px);
        display: flex;
        gap: clamp(10px, 2vw, 12px);
        align-items: center;
    }

    .tnelb-wiz-info i {
        color: #1a7be6;
        font-size: clamp(18px, 3.5vw, 22px);
    }

    /* Gender */
    .tnelb-wiz-gender {
        display: flex;
        flex-wrap: wrap;
        gap: clamp(8px, 2vw, 12px) clamp(20px, 4vw, 30px);
        align-items: center;
        min-height: clamp(30px, 6vw, 36px);
        padding-left: clamp(0px, 0.5vw, 4px);
    }

    .tnelb-wiz-gender label {
        margin: 0;
        font-size: clamp(14px, 2vw, 15px);
        font-weight: 600;
        color: #12263f;
        display: inline-flex;
        align-items: center;
        gap: clamp(6px, 1.5vw, 8px);
        cursor: pointer;
    }

    .tnelb-wiz-gender input {
        width: clamp(16px, 2.5vw, 18px);
        height: clamp(16px, 2.5vw, 18px);
        accent-color: #1565d8;
        margin: 0;
    }

    .tnelb-wiz-split {
        display: grid;
        grid-template-columns: 1fr;
        gap: clamp(12px, 2.5vw, 22px);
    }

    .tnelb-wiz-split.even {
        grid-template-columns: 1fr;
    }

    .tnelb-wiz-name {
        display: grid;
        grid-template-columns: clamp(55px, 12vw, 64px) 1fr;
        gap: clamp(6px, 1.5vw, 8px);
    }

    .tnelb-wiz-name select.form-control {
        padding: clamp(5px, 1vw, 6px) clamp(4px, 0.8vw, 4px) clamp(5px, 1vw, 6px) clamp(8px, 1.5vw, 10px);
    }

    .tnelb-input-icon {
        position: relative;
    }

    .tnelb-input-icon i {
        position: absolute;
        left: clamp(10px, 2vw, 12px);
        top: 50%;
        transform: translateY(-50%);
        color: #5b6b7c;
        font-size: clamp(13px, 2vw, 16px);
    }

    .tnelb-input-icon .form-control {
        padding-left: clamp(36px, 8vw, 46px);
    }

    /* Nav buttons */
    #registerform {
        display: grid;
        grid-template-columns: 1fr 1fr;
        column-gap: clamp(12px, 2.5vw, 20px);
        align-items: start;
        flex: 1;
    }

    #registerform .tnelb-wiz-split {
        display: contents;
    }

    #registerform .f-address {
        grid-column: 1 / -1;
        order: 7;
    }

    #registerform #districtRow {
        grid-column: 1 / -1;
        order: 8;
    }

    #registerform .tnelb-wiz-nav {
        grid-column: 1 / -1;
        order: 9;
    }

    .tnelb-wiz-nav {
        display: flex;
        justify-content: flex-end;
        gap: clamp(8px, 2vw, 12px);
        margin-top: auto;
        padding-top: clamp(8px, 2vw, 10px);
    }

    .tnelb-wiz-btn {
        min-width: clamp(100px, 20vw, 124px);
        height: var(--button-height);
        padding: 0 clamp(14px, 3vw, 22px);
        border-radius: 4px;
        font-size: clamp(14px, 2vw, 16px);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: clamp(8px, 1.5vw, 10px);
        cursor: pointer;
        text-decoration: none;
        flex: 1;
    }

    .tnelb-wiz-btn.primary {
        background: #0a5fe0;
        color: #fff;
        border: 0;
    }

    .tnelb-wiz-btn.primary:hover {
        background: #0852c2;
        color: #fff;
        text-decoration: none;
    }

    .tnelb-wiz-btn.primary:disabled {
        opacity: 0.7;
        cursor: not-allowed;
    }

    .tnelb-wiz-btn.ghost {
        background: #fff;
        color: #12263f;
        border: 1px solid #cfd9e4;
    }

    .tnelb-wiz-btn.ghost:hover {
        background: #f3f7fb;
    }

    .tnelb-wiz-panel .tnelb-wiz-btn.block {
        width: 100%;
        flex: none;
    }

    /* ============ Chevron connectors (hidden on modern layout) ============ */
    .tnelb-wiz-chevron {
        display: none;
    }

    /* ============ Success Panel ============ */
    .tnelb-success-art {
        position: relative;
        width: clamp(140px, 30vw, 180px);
        height: clamp(88px, 20vw, 113px);
        margin: clamp(10px, 2vw, 16px) auto clamp(3px, 0.8vw, 5px);
        flex-shrink: 0;
    }

    .tnelb-success-mark {
        position: absolute;
        left: 50%;
        top: 50%;
        width: clamp(58px, 12vw, 74px);
        height: clamp(58px, 12vw, 74px);
        margin: clamp(-29px, -6vw, -37px) 0 0 clamp(-29px, -6vw, -37px);
        border-radius: 50%;
        background: #27a85b;
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: clamp(24px, 5vw, 36px);
    }

    .tnelb-success-art em {
        position: absolute;
        width: clamp(6px, 1.2vw, 8px);
        height: clamp(6px, 1.2vw, 8px);
        transform: rotate(45deg);
    }

    .tnelb-success-card {
        background: #e9f6ee;
        border-radius: 6px;
        padding: clamp(8px, 1.5vw, 10px) clamp(12px, 2.5vw, 16px);
        margin: clamp(10px, 2vw, 14px) 0 clamp(8px, 1.5vw, 12px);
        color: #1f5a3a;
    }

    .tnelb-success-card > div {
        display: grid;
        grid-template-columns: clamp(22px, 5vw, 26px) clamp(100px, 22vw, 118px) clamp(10px, 2vw, 14px) 1fr;
        align-items: center;
        padding: clamp(6px, 1.5vw, 9px) 0;
        font-size: clamp(13.5px, 2vw, 15px);
    }

    .tnelb-success-card > div i {
        color: #2b6a47;
        font-size: clamp(15px, 3vw, 17px);
    }

    .tnelb-success-card > div .sep {
        color: #6b8f7a;
    }

    .tnelb-success-card > div strong {
        color: #0e2038;
        font-weight: 500;
        word-break: break-all;
    }

    .tnelb-success-note {
        color: #0c8a3c;
        font-weight: 600;
        font-size: clamp(13.5px, 2vw, 15px);
        text-align: center;
        margin: 0 0 clamp(10px, 2vw, 14px);
    }

    /* .tnelb-wiz-panel[data-panel="1"] .tnelb-wiz-nav {
        margin-top: clamp(12px, 2.5vw, 20px);
    } */

    .tnelb-wiz-panel[data-panel="1"] .tnelb-wiz-nav .primary {
        margin-left: auto;
    }

    .tnelb-wiz-nav .tnelb-wiz-btn.primary:only-child {
        margin-left: auto;
    }

</style>




@php
    /*
     | Option lists for the two dropdowns. They live here because the register
     | view is rendered by RegisterController::register() without view data —
     | move them to a config/lookup table when the backend exposes them.
     */
    $states = [
        'Andhra Pradesh', 'Arunachal Pradesh', 'Assam', 'Bihar', 'Chhattisgarh', 'Goa', 'Gujarat',
        'Haryana', 'Himachal Pradesh', 'Jharkhand', 'Karnataka', 'Kerala', 'Madhya Pradesh',
        'Maharashtra', 'Manipur', 'Meghalaya', 'Mizoram', 'Nagaland', 'Odisha', 'Punjab',
        'Rajasthan', 'Sikkim', 'Tamil Nadu', 'Telangana', 'Tripura', 'Uttar Pradesh',
        'Uttarakhand', 'West Bengal', 'Andaman and Nicobar Islands', 'Chandigarh',
        'Dadra and Nagar Haveli and Daman and Diu', 'Lakshadweep', 'Delhi', 'Puducherry',
        'Jammu and Kashmir', 'Ladakh',
    ];

    $districts = [
        'Ariyalur', 'Chengalpattu', 'Chennai', 'Coimbatore', 'Cuddalore', 'Dharmapuri', 'Dindigul',
        'Erode', 'Kancheepuram', 'Kanyakumari', 'Karur', 'Krishnagiri', 'Madurai', 'Nagapattinam',
        'Namakkal', 'Nilgiris', 'Perambalur', 'Pudukkottai', 'Ramanathapuram', 'Salem',
        'Sivagangai', 'Tenkasi', 'Thanjavur', 'The Nilgiris', 'Theni', 'Tirunelveli', 'Tiruppur',
        'Tiruvallur', 'Tiruvannamalai', 'Vellore', 'Viluppuram', 'Virudhunagar',
    ];

    $salutations = ['Mr', 'Ms', 'Mrs'];
    $genders = ['Male', 'Female', 'Transgender'];
@endphp

<section class="register-form">
    <div class="auto-container">
        {{-- Endpoints are published as data-* hooks so register.js stays a plain static asset --}}
        <div class="tnelb-wiz"
             id="registerWizard"
             data-wizard
             data-otp-url="{{ route('generate-otp') }}"
             data-store-url="{{ route('register.store') }}"
             data-otp-length="6">

            <!-- Header -->
            <div class="tnelb-wiz-head">
                <div class="tnelb-wiz-head-left">
                    <div class="tnelb-wiz-avatar"><i class="fa fa-user-plus" aria-hidden="true"></i></div>
                    <div>
                        <h2>Applicant Registration</h2>
                        <p>Create your account to access TNELB online services</p>
                    </div>
                </div>
            </div>

            <!-- Stepper -->
            <div class="tnelb-wiz-stepper" role="list" aria-label="Registration progress">
                <div class="tnelb-wiz-step is-active" role="listitem" data-wiz-step="1"><span>1</span> Mobile Verification</div>
                <div class="tnelb-wiz-line" aria-hidden="true"><i data-wiz-line="1" id="wizLine1"></i></div>
                <div class="tnelb-wiz-step" role="listitem" data-wiz-step="2"><span>2</span> Registration Form</div>
                <div class="tnelb-wiz-line" aria-hidden="true"><i data-wiz-line="2" id="wizLine2"></i></div>
                <div class="tnelb-wiz-step" role="listitem" data-wiz-step="3"><span>3</span> Completed</div>
            </div>

            <!-- Panels -->
            <div class="tnelb-wiz-body">

                <!-- STEP 1 -->
                <section class="tnelb-wiz-panel is-active" data-panel="1" tabindex="-1" aria-labelledby="wizStep1Title">
                    <x-step-header
                        icon="mobile"
                        kicker="Step 1 of 3"
                        title="Mobile Number Verification"
                        heading-id="wizStep1Title"
                        lead="Enter your mobile number and verify with OTP to proceed." />

                    <x-form-field name="mobile" label="Mobile Number" for="PhoneNo" required>
                        <div class="tnelb-wiz-phone">
                            <div class="tnelb-wiz-cc" aria-hidden="true"><span style="display:inline-flex;align-items:center;gap:8px;"><svg width="26" height="18" viewBox="0 0 26 18" aria-hidden="true"><rect width="26" height="6" fill="#ff9933"/><rect y="6" width="26" height="6" fill="#fff"/><rect y="12" width="26" height="6" fill="#138808"/><circle cx="13" cy="9" r="2.3" fill="none" stroke="#000080" stroke-width=".8"/></svg> +91</span> <small class="fa fa-chevron-down"></small></div>
                            <input
                                type="tel"
                                id="PhoneNo"
                                name="PhoneNo"
                                class="form-control"
                                data-phone-input
                                data-filter="digits"
                                maxlength="10"
                                inputmode="numeric"
                                pattern="[6-9][0-9]{9}"
                                autocomplete="tel-national"
                                required
                                aria-required="true"
                                aria-describedby="mobile-error"
                                placeholder="Enter 10 digit mobile number">
                        </div>
                    </x-form-field>

                    <button type="button" class="tnelb-wiz-send" data-action="send-otp" data-send-otp
                            id="sendOtpBtn" aria-controls="otpSection">
                        <i class="fa fa-paper-plane" aria-hidden="true"></i> Send OTP
                    </button>

                    <x-otp-input :length="6" :seconds="30" />

                    <x-info-box>OTP will be sent to the entered mobile number via SMS.</x-info-box>

                    <div class="tnelb-wiz-nav">
                        <button type="button" class="tnelb-wiz-btn primary" data-action="verify-otp"
                                id="step1Next" disabled>Next <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
                    </div>
                </section>

                <!-- STEP 2 -->
                <section class="tnelb-wiz-panel" data-panel="2" tabindex="-1" aria-labelledby="wizStep2Title">
                    <x-step-header
                        icon="user"
                        kicker="Step 2 of 3"
                        title="Registration Details"
                        heading-id="wizStep2Title"
                        lead="Fill in your personal information to create your account." />

                    {{-- novalidate: register.js owns the messaging so errors render inline, not in native bubbles --}}
                    <form id="registerform" data-register-form autocomplete="off" novalidate>
                        @csrf
                        {{-- Mirrors the step-1 mobile number so FormData carries it to the API --}}
                        <input type="hidden" name="mobile" data-mobile-mirror value="{{ old('mobile') }}">

                        <div class="tnelb-wiz-split">
                            <x-form-field name="first_name" label="First Name" for="first_name" required>
                                <div class="tnelb-wiz-name">
                                    <select id="salutation" name="salutation" class="form-control"
                                            aria-label="Salutation" required aria-describedby="salutation-error">
                                        @foreach ($salutations as $salutation)
                                            <option value="{{ $salutation }}" @selected(old('salutation') === $salutation)>{{ $salutation }}</option>
                                        @endforeach
                                    </select>
                                    <input type="text" id="first_name" name="first_name" class="form-control"
                                           value="{{ old('first_name') }}" placeholder="Enter your first name"
                                           data-filter="letters" maxlength="50" required aria-required="true"
                                           autocomplete="given-name" aria-describedby="first_name-error">
                                </div>
                                <span class="text-danger" id="salutation-error" data-error="salutation" role="alert" aria-live="polite"></span>
                            </x-form-field>

                            <x-form-input
                                name="last_name"
                                label="Last Name"
                                placeholder="Enter your last name"
                                autocomplete="family-name"
                                maxlength="50"
                                data-filter="letters"
                                required />

                            <x-form-input
                                type="date"
                                name="date_of_birth"
                                label="Date of Birth"
                                autocomplete="bday"
                                min="1900-01-01"
                                max="{{ now()->toDateString() }}"
                                required />
                        </div>

                        <x-form-field name="gender" label="Gender" label-tag="span" required>
                            <div class="tnelb-wiz-gender" role="radiogroup" aria-label="Gender" aria-describedby="gender-error">
                                @foreach ($genders as $gender)
                                    <label for="{{ $gender }}">
                                        <input type="radio" id="{{ $gender }}" name="gender" value="{{ $gender }}"
                                               @checked(old('gender', 'Male') === $gender)> {{ $gender }}
                                    </label>
                                @endforeach
                            </div>
                        </x-form-field>

                        <x-form-input
                            type="email"
                            name="email"
                            label="E-mail Address"
                            placeholder="Enter your e-mail address"
                            autocomplete="email"
                            inputmode="email"
                            maxlength="100"
                            icon="envelope"
                            required />

                        <x-form-textarea
                            name="Address"
                            label="Address"
                            placeholder="Enter your address"
                            field-class="f-address"
                            :rows="3"
                            maxlength="500"
                            required />

                        <div class="tnelb-wiz-split">
                            <x-form-select
                                name="state"
                                label="Select State"
                                placeholder="--- Select State ---"
                                :options="$states"
                                data-state-select
                                required />

                            <x-form-input
                                name="pincode"
                                label="Pincode"
                                placeholder="Enter pincode"
                                inputmode="numeric"
                                pattern="[0-9]{6}"
                                maxlength="6"
                                autocomplete="postal-code"
                                data-filter="digits"
                                required />
                        </div>

                        {{-- Shown by register.js only when the selected state is Tamil Nadu --}}
                        <div id="districtRow" data-district-row hidden>
                            <x-form-select
                                name="district"
                                label="Select District"
                                placeholder="--- Select District ---"
                                :options="$districts"
                                data-district-select
                                disabled
                                required />
                        </div>

                        {{-- Non-field failures (network, 500, unexpected payload) surface here --}}
                        <x-info-box icon="exclamation-triangle" role="alert" class="tnelb-wiz-alert" data-form-alert hidden>
                            <span data-form-alert-text></span>
                        </x-info-box>

                        <div class="tnelb-wiz-nav">
                            <button type="button" class="tnelb-wiz-btn ghost" data-action="go-to-step" data-step="1" id="step2Back">
                                <i class="fa fa-arrow-left" aria-hidden="true"></i> Back
                            </button>
                            <button type="submit" class="tnelb-wiz-btn primary" id="step2Next">
                                Next <i class="fa fa-arrow-right" aria-hidden="true"></i>
                            </button>
                        </div>
                    </form>
                </section>

                <!-- STEP 3 -->
                <section class="tnelb-wiz-panel" data-panel="3" tabindex="-1" aria-labelledby="wizStep3Title">
                    <div class="tnelb-success-art" aria-hidden="true">
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

                    <x-step-header
                        :inline="false"
                        kicker="Step 3 of 3"
                        title="Registration Successful!"
                        heading-id="wizStep3Title"
                        lead="Your account has been created successfully." />

                    <div class="tnelb-success-card" data-success-card>
                        <div><i class="fa fa-user" aria-hidden="true"></i><span>Name</span><span class="sep">:</span><strong id="successName" data-success="name">-</strong></div>
                        <div><i class="fa fa-phone" aria-hidden="true"></i><span>Mobile Number</span><span class="sep">:</span><strong id="successMobile" data-success="mobile">-</strong></div>
                        <div><i class="fa fa-envelope" aria-hidden="true"></i><span>E-mail</span><span class="sep">:</span><strong id="successEmail" data-success="email">-</strong></div>
                    </div>

                    <p class="tnelb-success-note">You can now sign in to access TNELB online services.</p>
                    <a href="{{ route('login') }}" class="tnelb-wiz-btn primary block">
                        <i class="fa fa-sign-in" aria-hidden="true"></i> Proceed to Sign In
                    </a>
                </section>
            </div>
        </div>
    </div>
</section>

<footer class="main-footer">
@include('include.footer')
{{-- defer: the DOM is parsed before register.js runs, so no document.ready wrapper is needed --}}
<script src="{{ url('assets/js/register.js') }}?v={{ filemtime(public_path('assets/js/register.js')) }}" defer></script>
