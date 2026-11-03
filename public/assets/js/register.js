/**
 * TNELB — Applicant registration wizard (3 steps: mobile OTP → details → done).
 *
 * Plain ES2020+, no jQuery. Loaded with `defer`, so the DOM is ready by the
 * time this runs. All DOM hooks are `data-*` attributes published by
 * resources/views/register.blade.php; endpoints come from the
 * `[data-wizard]` element (this file is a static asset — never Blade-compiled,
 * so it must not contain `{{ ... }}` placeholders).
 */
(() => {
    'use strict';

    /** Client-side rules, evaluated against the FormData snapshot of step 2. */
    const VALIDATORS = [
        {
            field: 'first_name',
            message: 'First name is required.',
            test: ({ first_name: value = '' }) => value.trim().length > 0,
        },
        {
            field: 'last_name',
            message: 'Last name is required.',
            test: ({ last_name: value = '' }) => value.trim().length > 0,
        },
        {
            field: 'date_of_birth',
            message: 'Select a valid date of birth.',
            test: ({ date_of_birth: value = '' }) => value !== '' && new Date(value) <= new Date(),
        },
        {
            field: 'gender',
            message: 'Please select gender.',
            test: ({ gender = '' }) => gender.trim().length > 0,
        },
        {
            field: 'email',
            message: 'Enter a valid email address.',
            test: ({ email = '' }) => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.trim()),
        },
        {
            field: 'Address',
            message: 'Address is required.',
            test: ({ Address: value = '' }) => value.trim().length > 0,
        },
        {
            field: 'state',
            message: 'Please select a state.',
            test: ({ state = '' }) => state.trim().length > 0,
        },
        {
            field: 'district',
            message: 'Please select a district.',
            test: ({ state = '', district = '' }) => state !== 'Tamil Nadu' || district.trim().length > 0,
        },
        {
            field: 'pincode',
            message: 'Enter a valid 6-digit pincode.',
            test: ({ pincode = '' }) => /^\d{6}$/.test(pincode.trim()),
        },
    ];

    const INPUT_FILTERS = {
        digits: /[^0-9]/g,
        letters: /[^A-Za-z\s]/g,
    };

    const MOBILE_PATTERN = /^[6-9]\d{9}$/;

    const RegisterWizard = {
        step: 1,
        otpVerified: false,
        timerId: null,
        resizeFrame: null,
        root: null,
        urls: {},
        csrf: '',
        els: {},

        /* ------------------------------------------------------------------ */
        /* Bootstrap                                                           */
        /* ------------------------------------------------------------------ */

        init() {
            this.root = document.querySelector('[data-wizard]');
            if (!this.root) return;

            const { otpUrl = '', storeUrl = '' } = this.root.dataset;
            this.urls = { otp: otpUrl, store: storeUrl };
            this.csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            if (!this.urls.otp || !this.urls.store) {
                console.error('[register] Missing data-otp-url / data-store-url on [data-wizard].');
            }
            if (!this.csrf) {
                console.error('[register] Missing <meta name="csrf-token">; POST requests will be rejected.');
            }

            this.cacheElements();
            this.bindEvents();
            this.goToStep(1, { focus: false });
        },

        cacheElements() {
            const find = (selector) => this.root.querySelector(selector);
            const findAll = (selector) => [...this.root.querySelectorAll(selector)];

            this.els = {
                card: document.querySelector('.register-form > .auto-container'),
                panels: findAll('[data-panel]'),
                steps: findAll('[data-wiz-step]'),
                lines: findAll('[data-wiz-line]'),
                phone: find('[data-phone-input]'),
                sendBtn: find('[data-send-otp]'),
                otpSection: find('[data-otp-section]'),
                otpDigits: findAll('[data-otp-digit]'),
                otpTimer: find('[data-otp-timer]'),
                otpTimerWrap: find('[data-otp-timer-wrap]'),
                otpResend: find('[data-otp-resend]'),
                otpNext: find('[data-action="verify-otp"]'),
                form: find('[data-register-form]'),
                mobileMirror: find('[data-mobile-mirror]'),
                stateSelect: find('[data-state-select]'),
                districtRow: find('[data-district-row]'),
                districtSelect: find('[data-district-select]'),
                submitBtn: find('[data-register-form] [type="submit"]'),
                formAlert: find('[data-form-alert]'),
                formAlertText: find('[data-form-alert-text]'),
                success: {
                    name: find('[data-success="name"]'),
                    mobile: find('[data-success="mobile"]'),
                    email: find('[data-success="email"]'),
                },
            };
        },

        /** One listener per event type on the wizard root — works for markup added later too. */
        bindEvents() {
            this.root.addEventListener('click', (event) => this.onClick(event));
            this.root.addEventListener('input', (event) => this.onInput(event));
            this.root.addEventListener('keydown', (event) => this.onKeyDown(event));
            this.root.addEventListener('paste', (event) => this.onPaste(event));
            this.root.addEventListener('change', (event) => this.onChange(event));

            this.els.form?.addEventListener('submit', (event) => this.onSubmit(event));

        },

        /* ------------------------------------------------------------------ */
        /* Delegated events                                                    */
        /* ------------------------------------------------------------------ */

        onClick(event) {
            const trigger = event.target.closest('[data-action]');
            if (!trigger) return;

            const { action, step } = trigger.dataset;

            switch (action) {
                case 'send-otp':
                    this.sendOtp();
                    break;
                case 'verify-otp':
                    this.verifyOtp();
                    break;
                case 'go-to-step':
                    this.goToStep(Number(step));
                    break;
                default:
                    console.error(`[register] Unhandled data-action="${action}".`);
            }
        },

        onInput(event) {
            const field = event.target;

            if (field.matches('[data-otp-digit]')) {
                this.onOtpDigitInput(field);
                return;
            }

            this.applyFilter(field);

            if (field.matches('[data-phone-input]')) {
                this.otpVerified = false;
                this.setNextEnabled(false);
                this.clearError('mobile');
                return;
            }

            if (field.name) this.clearError(field.name);
            this.hideFormAlert();
        },

        onKeyDown(event) {
            const field = event.target;

            if (field.matches('[data-phone-input]') && event.key === 'Enter') {
                event.preventDefault();
                this.sendOtp();
                return;
            }

            if (!field.matches('[data-otp-digit]')) return;

            if (event.key === 'Backspace' && field.value === '') {
                event.preventDefault();
                this.focusSibling(field, -1);
            } else if (event.key === 'ArrowLeft') {
                this.focusSibling(field, -1);
            } else if (event.key === 'ArrowRight') {
                this.focusSibling(field, 1);
            }
        },

        onPaste(event) {
            if (!event.target.matches('[data-otp-digit]')) return;

            const pasted = (event.clipboardData?.getData('text') ?? '').replace(/\D/g, '');
            if (!pasted) return;

            event.preventDefault();

            const { otpDigits } = this.els;
            const start = otpDigits.indexOf(event.target);

            [...pasted].slice(0, otpDigits.length - start).forEach((digit, offset) => {
                otpDigits[start + offset].value = digit;
            });

            const filled = Math.min(start + pasted.length, otpDigits.length - 1);
            otpDigits[filled].focus();
            this.setNextEnabled(this.otpValue().length === otpDigits.length);
        },

        onChange(event) {
            if (event.target.matches('[data-state-select]')) {
                this.toggleDistrict(event.target.value === 'Tamil Nadu');
            }
        },

        /* ------------------------------------------------------------------ */
        /* Step 1 — mobile + OTP                                               */
        /* ------------------------------------------------------------------ */

        async sendOtp() {
            const { phone, sendBtn } = this.els;
            const mobile = phone.value.trim();

            this.clearError('mobile');
            this.clearError('otp');
            this.otpVerified = false;
            this.setNextEnabled(false);

            if (!MOBILE_PATTERN.test(mobile)) {
                this.showErrors([{ field: 'mobile', message: 'Enter a valid 10-digit mobile number starting with 6-9.' }]);
                phone.focus();
                return;
            }

            const body = new FormData();
            body.append('mobile', mobile);

            this.setBusy(sendBtn, true, '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Sending OTP…');

            try {
                const { ok, payload } = await this.post(this.urls.otp, body);

                if (!ok || payload.success !== true) {
                    const message = payload.errors?.mobile?.[0] ?? payload.message ?? 'Unable to send OTP.';
                    this.showErrors([{ field: 'mobile', message }]);
                    console.error(`[register] generate-otp failed: ${message}`);
                }
            } catch (error) {
                this.showErrors([{ field: 'mobile', message: 'Network problem — check your connection and try again.' }]);
                console.error('[register] generate-otp request error:', error);
            } finally {
                this.setBusy(sendBtn, false);

                // The OTP panel is revealed whatever the API answered, which is the
                // behaviour this screen shipped with: POST /generate-otp currently
                // replies 422 "OTP already generated" for every request, so gating the
                // reveal on `success` would dead-end the wizard. Move this call into the
                // `ok` branch above once the endpoint is fixed server-side.
                this.revealOtpSection();
                this.resetOtpDigits();
                this.startOtpTimer(Number(this.els.otpSection?.dataset.otpSeconds) || 30);
            }
        },

        verifyOtp() {
            const otp = this.otpValue();
            this.clearError('otp');

            if (otp.length !== this.els.otpDigits.length) {
                this.showErrors([{ field: 'otp', message: `Enter the ${this.els.otpDigits.length}-digit OTP.` }]);
                this.els.otpDigits.find(({ value }) => value === '')?.focus();
                return;
            }

            this.otpVerified = true;
            this.goToStep(2);
        },

        revealOtpSection() {
            const { otpSection, otpDigits } = this.els;
            if (!otpSection) return;

            otpSection.style.display = 'block';
            void otpSection.offsetHeight; // force reflow so the CSS fade-in runs
            otpSection.classList.add('is-visible');
            otpSection.setAttribute('aria-hidden', 'false');

            otpDigits.forEach((digit) => {
                digit.disabled = false;
            });
        },

        resetOtpDigits() {
            this.els.otpDigits.forEach((digit) => {
                digit.value = '';
            });
            this.els.otpDigits[0]?.focus();
        },

        otpValue() {
            return this.els.otpDigits.map(({ value }) => value).join('');
        },

        onOtpDigitInput(input) {
            input.value = input.value.replace(/\D/g, '').slice(0, 1);
            if (input.value) this.focusSibling(input, 1);

            this.clearError('otp');
            this.setNextEnabled(this.otpValue().length === this.els.otpDigits.length);
        },

        focusSibling(input, direction) {
            const { otpDigits } = this.els;
            const next = otpDigits[otpDigits.indexOf(input) + direction];
            next?.focus();
            next?.select?.();
        },

        setNextEnabled(enabled) {
            if (this.els.otpNext) this.els.otpNext.disabled = !enabled;
        },

        startOtpTimer(seconds) {
            const { otpTimer, otpTimerWrap, otpResend } = this.els;
            let remaining = seconds;

            window.clearInterval(this.timerId);
            otpTimerWrap?.removeAttribute('hidden');
            otpResend?.setAttribute('hidden', '');

            const tick = () => {
                const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                const secs = String(remaining % 60).padStart(2, '0');
                if (otpTimer) otpTimer.textContent = `${minutes}:${secs}`;

                if (remaining <= 0) {
                    window.clearInterval(this.timerId);
                    otpTimerWrap?.setAttribute('hidden', '');
                    otpResend?.removeAttribute('hidden');
                    return;
                }

                remaining -= 1;
            };

            tick();
            this.timerId = window.setInterval(tick, 1000);
        },

        /* ------------------------------------------------------------------ */
        /* Step 2 — registration details                                       */
        /* ------------------------------------------------------------------ */

        toggleDistrict(show) {
            const { districtRow, districtSelect } = this.els;
            if (!districtRow || !districtSelect) return;

            if (show) {
                districtRow.removeAttribute('hidden');
                districtSelect.disabled = false;
            } else {
                districtRow.setAttribute('hidden', '');
                districtSelect.disabled = true;
                districtSelect.value = '';
                this.clearError('district');
            }
        },

        async onSubmit(event) {
            event.preventDefault();

            const { form, phone, mobileMirror, submitBtn } = this.els;

            this.clearErrors();
            if (mobileMirror && phone) mobileMirror.value = phone.value.trim();

            const data = Object.fromEntries(new FormData(form).entries());
            const failures = VALIDATORS
                .filter(({ test }) => !test(data))
                .map(({ field, message }) => ({ field, message }));

            if (failures.length > 0) {
                this.showErrors(failures);
                this.focusField(failures[0].field);
                return;
            }

            this.setBusy(submitBtn, true, '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Saving…');

            try {
                const { ok, payload } = await this.post(this.urls.store, new FormData(form));

                if (ok && payload.success === true) {
                    this.fillSuccessCard(data);
                    this.goToStep(3);
                    return;
                }

                const serverErrors = Object.entries(payload.errors ?? {})
                    .map(([field, messages]) => ({ field, message: messages?.[0] ?? 'Invalid value.' }));

                if (serverErrors.length > 0) {
                    this.showErrors(serverErrors);
                    this.focusField(serverErrors[0].field);
                } else {
                    this.showFormAlert(payload.message ?? 'Registration could not be saved. Please try again.');
                }

                console.error('[register] register.store rejected the submission:', payload);
            } catch (error) {
                this.showFormAlert('Network problem — check your connection and try again.');
                console.error('[register] register.store request error:', error);
            } finally {
                this.setBusy(submitBtn, false);
            }
        },

        fillSuccessCard(data) {
            const {
                salutation = '',
                first_name: firstName = '',
                last_name: lastName = '',
                mobile = '',
                email = '',
            } = data;

            const { name, mobile: mobileEl, email: emailEl } = this.els.success;

            if (name) name.textContent = [salutation, firstName, lastName].filter(Boolean).join(' ');
            if (mobileEl) mobileEl.textContent = mobile ? `+91 ${mobile}` : '-';
            if (emailEl) emailEl.textContent = email || '-';
        },

        /* ------------------------------------------------------------------ */
        /* Errors                                                              */
        /* ------------------------------------------------------------------ */

        showErrors(errors) {
            errors.forEach(({ field, message }) => {
                const slot = this.root.querySelector(`[data-error="${field}"]`);

                if (slot) {
                    slot.textContent = message;
                } else {
                    this.showFormAlert(message);
                    console.error(`[register] No [data-error="${field}"] placeholder for: ${message}`);
                }

                this.root
                    .querySelectorAll(`[name="${field}"]`)
                    .forEach((control) => control.setAttribute('aria-invalid', 'true'));
            });

            // A rejected mobile number belongs to step 1 — send the user back there.
            if (errors.some(({ field }) => field === 'mobile') && this.step !== 1) {
                this.goToStep(1);
            }
        },

        clearError(field) {
            const slot = this.root.querySelector(`[data-error="${field}"]`);
            if (slot) slot.textContent = '';

            this.root
                .querySelectorAll(`[name="${field}"]`)
                .forEach((control) => control.removeAttribute('aria-invalid'));
        },

        clearErrors() {
            this.root.querySelectorAll('[data-error]').forEach((slot) => {
                slot.textContent = '';
            });
            this.root.querySelectorAll('[aria-invalid]').forEach((control) => {
                control.removeAttribute('aria-invalid');
            });
            this.hideFormAlert();
        },

        showFormAlert(message) {
            const { formAlert, formAlertText } = this.els;
            if (!formAlert || !formAlertText) return;

            formAlertText.textContent = message;
            formAlert.removeAttribute('hidden');
        },

        hideFormAlert() {
            this.els.formAlert?.setAttribute('hidden', '');
        },

        focusField(field) {
            const control = this.root.querySelector(`[name="${field}"]:not([disabled])`);
            control?.focus();
        },

        /* ------------------------------------------------------------------ */
        /* Wizard chrome                                                       */
        /* ------------------------------------------------------------------ */

        goToStep(step, { focus = true } = {}) {
            if (!Number.isInteger(step) || step < 1) {
                console.error(`[register] Ignoring invalid step: ${step}`);
                return;
            }

            this.step = step;

            this.els.panels.forEach((panel) => {
                const isActive = Number(panel.dataset.panel) === step;
                panel.classList.toggle('is-active', isActive);
                panel.inert = !isActive; // locked panels stay laid out but unfocusable
            });

            this.els.steps.forEach((marker) => {
                const index = Number(marker.dataset.wizStep);
                const bullet = marker.querySelector('span');

                marker.classList.toggle('is-active', index === step);
                marker.classList.toggle('is-done', index < step);

                if (index === step) {
                    marker.setAttribute('aria-current', 'step');
                } else {
                    marker.removeAttribute('aria-current');
                }

                if (bullet) {
                    bullet.innerHTML = index < step ? '<i class="fa fa-check" aria-hidden="true"></i>' : String(index);
                }
            });

            const [line1, line2] = this.els.lines;
            if (line1) line1.style.width = step > 1 ? '100%' : '60%';
            if (line2) line2.style.width = step > 2 ? '100%' : step === 2 ? '60%' : '0';

            if (focus) {
                this.els.panels.find((panel) => Number(panel.dataset.panel) === step)?.focus({ preventScroll: true });
            }
        },


        /* ------------------------------------------------------------------ */
        /* Helpers                                                             */
        /* ------------------------------------------------------------------ */

        /**
         * POST a FormData payload and always resolve to a predictable shape.
         * @returns {Promise<{ok: boolean, status: number, payload: Object}>}
         */
        async post(url, body) {
            const response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body,
            });

            let payload = {};

            try {
                payload = await response.json();
            } catch (error) {
                console.error(`[register] ${url} returned a non-JSON body (HTTP ${response.status}).`, error);
            }

            return { ok: response.ok, status: response.status, payload: payload ?? {} };
        },

        applyFilter(input) {
            const pattern = INPUT_FILTERS[input.dataset.filter];
            if (!pattern) return;

            const limit = input.maxLength > 0 ? input.maxLength : input.value.length;
            const cleaned = input.value.replace(pattern, '').slice(0, limit);
            if (cleaned === input.value) return;

            const caret = input.selectionStart;
            input.value = cleaned;

            if (input.type === 'text' || input.type === 'tel') {
                const position = Math.min(caret ?? cleaned.length, cleaned.length);
                input.setSelectionRange(position, position);
            }
        },

        setBusy(button, busy, busyLabel = '') {
            if (!button) return;

            if (busy) {
                button.dataset.idleLabel ??= button.innerHTML;
                if (busyLabel) button.innerHTML = busyLabel;
                button.disabled = true;
                button.setAttribute('aria-busy', 'true');
                return;
            }

            if (button.dataset.idleLabel) button.innerHTML = button.dataset.idleLabel;
            button.disabled = false;
            button.removeAttribute('aria-busy');
        },
    };

    // The <script> tag is deferred, so the DOM is already parsed here; the guard
    // keeps the file safe if it is ever loaded without `defer`.
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => RegisterWizard.init(), { once: true });
    } else {
        RegisterWizard.init();
    }
})();
