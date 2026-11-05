<style>
    .tab-content {
        padding: 0px 20px;
    }

    .table td {
        text-align: left;
    }

    table th {
        font-weight: 800 !important;
    }

    .officers_section {
        background: #4361ee;
        padding: 5px;
    }

    .officers_section h6 {
        color: #fff;
        font-size: 18px;
        font-weight: 600;
    }

    .text-info {
        color: #3b3f5c !important;
        font-size: 15px;
    }

    .file_view {
        color: #0082ff;
    }

    .file_view i {
        color: red;
    }

    .text-return {
        color: #6f42c1;
    }

    /* Default (Unchecked = Red) */
    .status-switch {
        background-color: #645e5e !important;
        border-color: #645e5e !important;
        cursor: pointer;
    }

    /* Checked = Green */
    .status-switch:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }

    /* Focus */
    .status-switch:focus {
        box-shadow: 0 0 0 0.25rem rgba(25, 135, 84, 0.25);
    }

    .checklist_chk .form-check-input:checked {
        background-color: #4361ee !important;
        border-color: #4361ee !important;
    }

    .dash-tl-overlay {
        position: fixed;
        inset: 0;
        z-index: 10050;
        background: rgba(10, 24, 48, 0.55);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 0.85rem;
        backdrop-filter: blur(2px);
    }

    .dash-tl-overlay.is-open {
        display: flex;
    }

    .dash-tl-panel {
        background: #eef3f9;
        width: min(52rem, 96vw);
        max-height: min(90vh, 46rem);
        display: flex;
        flex-direction: column;
        border-radius: 0.85rem;
        overflow: hidden;
        box-shadow: 0 1.1rem 2.8rem rgba(3, 90, 179, 0.22);
    }

    .dash-tl-header {
        background: linear-gradient(135deg, #035ab3 0%, #0472d9 100%);
        padding: 0.8rem 1.1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .dash-tl-title {
        margin: 0;
        font-size: 1.02rem;
        font-weight: 700;
        color: #fff;
        line-height: 1.3;
    }

    .dash-tl-subtitle {
        margin: 0.15rem 0 0;
        font-size: 0.76rem;
        color: rgba(255, 255, 255, 0.88);
        word-break: break-word;
    }

    .dash-tl-close {
        background: rgba(255, 255, 255, 0.14);
        border: none;
        color: #fff;
        width: 2.15rem;
        height: 2.15rem;
        border-radius: 50%;
        font-size: 1.3rem;
        line-height: 1;
        cursor: pointer;
        flex-shrink: 0;
    }

    .dash-tl-close:hover,
    .dash-tl-close:focus-visible {
        background: rgba(255, 255, 255, 0.28);
        outline: 2px solid #fff;
        outline-offset: 2px;
    }

    .dash-tl-body {
        position: relative;
        flex: 1;
        min-height: 0;
        overflow-y: auto;
        padding: 1rem 1.1rem 1.2rem;
        background: #eef3f9;
    }

    .dash-tl-loading {
        display: none;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 0.5rem;
        min-height: 10rem;
        color: #035ab3;
        font-weight: 600;
        font-size: 0.88rem;
    }

    .dash-tl-loading.is-visible {
        display: flex;
    }

    .dash-tl-footer {
        display: none;
        justify-content: flex-end;
        gap: 0.5rem;
        padding: 0.75rem 1.1rem;
        background: #fff;
        border-top: 1px solid #d7e2f0;
        flex-shrink: 0;
    }

    .dash-tl-overlay.is-approve .dash-tl-footer {
        display: flex;
    }

    body.dash-tl-open {
        overflow: hidden;
    }

    .staff-status-switch {
        width: 45px !important;
        height: 22px !important;
        cursor: pointer;
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }

    .staff-status-switch:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }

    .staff-status-switch {
        width: 45px !important;
        height: 22px !important;
        cursor: pointer;
        background-color: #dc3545 !important;
        border-color: #dc3545 !important;
    }

    .staff-status-switch:checked {
        background-color: #198754 !important;
        border-color: #198754 !important;
    }


    .approval-success-popup {
        border-radius: 14px !important;
        overflow: hidden !important;
        box-shadow: 0 20px 60px rgba(0, 0, 0, .20) !important;
    }


    /* -------------------------
   HEADER
------------------------- */

    .approval-header {
        display: flex;
        align-items: center;
        gap: 15px;

        padding: 22px 25px;

        background: linear-gradient(135deg,
                #198754,
                #157347);

        color: #fff;

        text-align: left;
    }


    .success-icon {
        width: 52px;
        height: 52px;

        border-radius: 50%;

        background: rgba(255, 255, 255, .20);

        display: flex;
        align-items: center;
        justify-content: center;

        font-size: 30px;
        font-weight: bold;
    }


    .approval-title {
        font-size: 21px;
        font-weight: 700;
    }


    .approval-subtitle {
        font-size: 13px;
        opacity: .9;

        margin-top: 4px;
    }


    /* -------------------------
   MAIN DETAILS
------------------------- */

    .details-section {
        padding: 22px 25px 5px;

        text-align: left;
    }


    .section-title {
        font-size: 16px;
        font-weight: 700;

        color: #343a40;

        margin-bottom: 12px;

        display: flex;
        align-items: center;
        gap: 8px;
    }


    .section-icon {
        font-size: 18px;
    }


    .details-card {
        border: 1px solid #dee2e6;

        border-radius: 10px;

        overflow: hidden;

        background: #f8f9fa;
    }


    .detail-row {
        display: flex;

        justify-content: space-between;
        align-items: center;

        padding: 14px 17px;

        border-bottom: 1px solid #dee2e6;
    }


    .detail-row:last-child {
        border-bottom: 0;
    }


    .detail-label {
        color: #6c757d;

        font-size: 13px;

        font-weight: 600;
    }


    .detail-value {
        color: #212529;

        font-size: 15px;

        font-weight: 600;

        text-align: right;
    }


    .licence-number {
        color: #198754;

        font-size: 17px;

        font-weight: 700;
    }


    .expiry-date {
        color: #dc3545;

        font-size: 16px;

        font-weight: 700;
    }


    /* -------------------------
   EXPIRY MESSAGE
------------------------- */

    .expiry-message {
        margin-top: 12px;

        padding: 12px 15px;

        background: #f1f8f4;

        border: 1px solid #b7dfc8;

        border-radius: 8px;

        color: #146c43;

        font-size: 13px;

        display: flex;

        align-items: center;

        gap: 9px;
    }


    .expiry-icon {
        width: 24px;
        height: 24px;

        border-radius: 50%;

        background: #198754;

        color: #fff;

        display: flex;

        align-items: center;
        justify-content: center;

        font-weight: bold;
    }

    .approval-success-popup {

        border-radius: 14px !important;

        overflow: hidden !important;

        box-shadow:
            0 20px 60px rgba(0, 0, 0, 0.20) !important;

    }


    /* =========================================
   HEADER
========================================= */

    .approval-header {

        display: flex;

        align-items: center;

        gap: 15px;

        padding: 22px 26px;

        background:
            linear-gradient(135deg,
                #198754,
                #157347);

        color: #ffffff;

        text-align: left;

    }


    .success-circle {

        width: 52px;

        height: 52px;

        min-width: 52px;

        border-radius: 50%;

        background:
            rgba(255, 255, 255, 0.20);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 30px;

        font-weight: 700;

    }


    .approval-title {

        font-size: 21px;

        font-weight: 700;

    }


    .approval-subtitle {

        font-size: 13px;

        opacity: 0.90;

        margin-top: 4px;

    }


    /* =========================================
   LICENCE DETAILS
========================================= */

    .licence-details {

        padding: 22px 26px 8px;

        text-align: left;

    }


    .section-heading {

        display: flex;

        align-items: center;

        gap: 8px;

        font-size: 16px;

        font-weight: 700;

        color: #343a40;

        margin-bottom: 12px;

    }


    .heading-icon {

        font-size: 18px;

    }


    .licence-card {

        border: 1px solid #dee2e6;

        border-radius: 10px;

        overflow: hidden;

        background: #f8f9fa;

    }


    .detail-row {

        display: flex;

        align-items: center;

        justify-content: space-between;

        gap: 20px;

        padding: 14px 18px;

        border-bottom: 1px solid #dee2e6;

    }


    .detail-row:last-child {

        border-bottom: none;

    }


    .detail-label {

        color: #6c757d;

        font-size: 13px;

        font-weight: 600;

    }


    .detail-value {

        color: #212529;

        font-size: 15px;

        font-weight: 600;

        text-align: right;

    }


    .licence-number {

        color: #198754;

        font-size: 17px;

        font-weight: 700;

    }


    .expiry-date {

        color: #dc3545;

        font-size: 16px;

        font-weight: 700;

    }


    /* =========================================
   EXPIRY MESSAGE
========================================= */

    .expiry-message {

        display: flex;

        align-items: center;

        gap: 9px;

        margin-top: 12px;

        padding: 12px 15px;

        background: #f1f8f4;

        border: 1px solid #b7dfc8;

        border-radius: 8px;

        color: #146c43;

        font-size: 13px;

    }


    .expiry-check {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 24px;

        height: 24px;

        min-width: 24px;

        border-radius: 50%;

        background: #198754;

        color: #ffffff;

        font-weight: 700;

    }


    /* =========================================
   DIGITISATION CARD
========================================= */

    .digitisation-card {

        margin: 18px 26px 5px;

        padding: 18px;

        border: 1px solid #cfe2ff;

        border-radius: 10px;

        background: #f4f8ff;

        text-align: left;

    }


    .digitisation-title {

        display: flex;

        align-items: center;

        gap: 8px;

        font-size: 16px;

        font-weight: 700;

        color: #0d6efd;

        margin-bottom: 14px;

    }


    .digitisation-icon {

        font-size: 19px;

    }


    /* =========================================
   MAPPING
========================================= */

    .mapping-wrapper {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 12px;

    }


    .mapping-item {

        flex: 1;

        padding: 15px 12px;

        border-radius: 8px;

        text-align: center;

    }


    .old-cl {

        background: #fff3cd;

        border: 1px solid #ffe69c;

    }


    .new-cl {

        background: #d1e7dd;

        border: 1px solid #a3cfbb;

    }


    .mapping-label {

        font-size: 10px;

        font-weight: 700;

        letter-spacing: .5px;

        color: #6c757d;

        margin-bottom: 7px;

    }


    .mapping-value {

        font-size: 16px;

        font-weight: 700;

        word-break: break-word;

    }


    .old-cl .mapping-value {

        color: #856404;

    }


    .new-cl .mapping-value {

        color: #146c43;

    }


    .mapping-arrow {

        font-size: 28px;

        font-weight: 700;

        color: #0d6efd;

    }


    /* =========================================
   NO MAPPING
========================================= */

    .digitisation-not-found {

        display: flex;

        align-items: center;

        justify-content: center;

        gap: 8px;

        margin: 18px 26px 5px;

        padding: 13px;

        border: 1px solid #dee2e6;

        border-radius: 8px;

        background: #f8f9fa;

        color: #6c757d;

        font-size: 13px;

    }


    .info-icon {

        display: flex;

        align-items: center;

        justify-content: center;

        width: 22px;

        height: 22px;

        border-radius: 50%;

        background: #6c757d;

        color: #ffffff;

        font-weight: 700;

    }


    /* =========================================
   OK BUTTON
========================================= */

    .approval-ok-button {

        min-width: 110px !important;

        padding: 10px 28px !important;

        border-radius: 7px !important;

        font-weight: 600 !important;

    }


    /* =========================================
   MOBILE
========================================= */

    @media (max-width: 600px) {

        .mapping-wrapper {

            flex-direction: column;

        }

        .mapping-item {

            width: 100%;

        }

        .mapping-arrow {

            transform: rotate(90deg);

        }

        .detail-row {

            flex-direction: column;

            align-items: flex-start;

            gap: 5px;

        }

        .detail-value {

            text-align: left;

        }

    }
</style>
