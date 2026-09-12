{{--
    Shared Apple-style alert theming for SweetAlert2.
    Include once per page:  @include('partials.apple-alert')
    Then replace bare Swal.fire() calls with AppleAlert.* helpers.

    Requires SweetAlert2 to already be loaded on the page.
--}}
<style>
/* ============================================================
   AppleAlert — Apple-style alert theming for SweetAlert2
   ============================================================ */

/* ── Backdrop ────────────────────────────────────────────── */
.swal2-container.apple-alert-container {
    backdrop-filter: blur(20px) saturate(180%);
    -webkit-backdrop-filter: blur(20px) saturate(180%);
    background: rgba(15, 23, 42, 0.28);
    padding: 20px;
}
.swal2-container.apple-alert-container.swal2-backdrop-show {
    background: rgba(15, 23, 42, 0.28);
}

/* ── Popup card ──────────────────────────────────────────── */
.swal2-popup.apple-alert {
    border-radius: 20px;
    border: none;
    box-shadow:
        0 20px 60px rgba(15, 23, 42, 0.24),
        0 4px 16px rgba(15, 23, 42, 0.12),
        0 0 0 0.5px rgba(0, 0, 0, 0.04);
    padding: 28px 24px 20px;
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display",
                 "Helvetica Neue", Inter, system-ui, sans-serif;
    font-feature-settings: "cv02", "cv03", "cv04", "cv11";
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    max-width: 420px;
    min-width: 320px;
    width: auto;
    background: #FFFFFF;
    animation: appleAlertPop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
}
@keyframes appleAlertPop {
    0%   { transform: scale(0.92); opacity: 0; }
    60%  { transform: scale(1.015); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}

/* ── Icon ────────────────────────────────────────────────── */
.swal2-popup.apple-alert .swal2-icon {
    width: 56px;
    height: 56px;
    margin: 4px auto 14px;
    border-width: 0;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.swal2-popup.apple-alert .swal2-icon .swal2-icon-content {
    font-size: 28px;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 400;
}
.swal2-popup.apple-alert .swal2-icon.swal2-success {
    background: #E8F9EE;
    color: #34C759;
    border: none;
}
.swal2-popup.apple-alert .swal2-icon.swal2-success .swal2-success-ring {
    display: none;
}
.swal2-popup.apple-alert .swal2-icon.swal2-success [class^='swal2-success-line'] {
    background-color: #34C759;
}
.swal2-popup.apple-alert .swal2-icon.swal2-error {
    background: #FEECEC;
    color: #FF3B30;
    border: none;
}
.swal2-popup.apple-alert .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
    background-color: #FF3B30;
}
.swal2-popup.apple-alert .swal2-icon.swal2-warning {
    background: #FFF4E5;
    color: #FF9500;
    border: none;
}
.swal2-popup.apple-alert .swal2-icon.swal2-info {
    background: #E5F1FF;
    color: #007AFF;
    border: none;
}
.swal2-popup.apple-alert .swal2-icon.swal2-question {
    background: #EEF1F5;
    color: #8E8E93;
    border: none;
}

/* ── Title ───────────────────────────────────────────────── */
.swal2-popup.apple-alert .swal2-title {
    font-size: 18px;
    font-weight: 600;
    color: #0F172A;
    letter-spacing: -0.015em;
    line-height: 1.3;
    margin: 0 0 6px;
    padding: 0 8px;
}
.swal2-popup.apple-alert.swal2-icon-show .swal2-title {
    margin: 0 0 6px;
}

/* ── HTML / text body ────────────────────────────────────── */
.swal2-popup.apple-alert .swal2-html-container {
    font-size: 14px;
    font-weight: 400;
    color: #475569;
    line-height: 1.5;
    letter-spacing: -0.005em;
    margin: 0;
    padding: 0 8px;
}
.swal2-popup.apple-alert .swal2-html-container code {
    background: #F1F5F9;
    color: #0F172A;
    padding: 2px 6px;
    border-radius: 6px;
    font-family: "SF Mono", ui-monospace, Menlo, monospace;
    font-size: 13px;
    letter-spacing: 0;
}
.swal2-popup.apple-alert .swal2-html-container p {
    margin: 0 0 8px;
}
.swal2-popup.apple-alert .swal2-html-container p:last-child { margin-bottom: 0; }

/* ── Actions row ─────────────────────────────────────────── */
.swal2-popup.apple-alert .swal2-actions {
    margin: 22px 0 0;
    gap: 8px;
    width: 100%;
    justify-content: stretch;
    flex-wrap: nowrap;
}
.swal2-popup.apple-alert .swal2-actions:not(.swal2-loading) .swal2-styled[disabled] {
    opacity: 0.4;
}

/* ── Buttons ─────────────────────────────────────────────── */
.swal2-popup.apple-alert .swal2-styled {
    border: none;
    border-radius: 12px;
    padding: 11px 20px;
    font-size: 15px;
    font-weight: 600;
    letter-spacing: -0.005em;
    font-family: inherit;
    transition: all 0.15s ease;
    box-shadow: none;
    min-height: 44px;
    flex: 1;
    margin: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}
.swal2-popup.apple-alert .swal2-styled:focus { box-shadow: none; outline: none; }
.swal2-popup.apple-alert .swal2-styled:active { transform: scale(0.97); }

/* Primary (blue) — default confirm */
.swal2-popup.apple-alert .swal2-confirm {
    background: #007AFF;
    color: #FFFFFF;
}
.swal2-popup.apple-alert .swal2-confirm:hover { background: #0066D6; }

/* Destructive (red) — callers set via theme */
.swal2-popup.apple-alert .swal2-confirm.apple-destructive {
    background: #FF3B30;
}
.swal2-popup.apple-alert .swal2-confirm.apple-destructive:hover {
    background: #E63329;
}

/* Warning (orange) — for override prompts */
.swal2-popup.apple-alert .swal2-confirm.apple-warning {
    background: #FF9500;
}
.swal2-popup.apple-alert .swal2-confirm.apple-warning:hover {
    background: #E68600;
}

/* Success (green) — for confirmations that "commit" a save */
.swal2-popup.apple-alert .swal2-confirm.apple-success {
    background: #34C759;
}
.swal2-popup.apple-alert .swal2-confirm.apple-success:hover {
    background: #2DA84A;
}

/* Cancel — iOS-style tinted gray */
.swal2-popup.apple-alert .swal2-cancel {
    background: #F1F5F9;
    color: #0F172A;
}
.swal2-popup.apple-alert .swal2-cancel:hover { background: #E2E8F0; }

/* When only one button, keep it full width */
.swal2-popup.apple-alert .swal2-actions:not(:has(.swal2-cancel)) .swal2-confirm {
    flex: 1;
}

/* ── Loading state (spinner-only alerts) ────────────────── */
.swal2-popup.apple-alert.apple-loading {
    padding: 32px 24px;
    text-align: center;
}
.swal2-popup.apple-alert.apple-loading .swal2-title {
    font-size: 15px;
    font-weight: 500;
    color: #475569;
    margin-top: 14px;
}
.swal2-popup.apple-alert.apple-loading .swal2-loader {
    border-color: #007AFF transparent #007AFF transparent;
    border-width: 3px;
    width: 36px;
    height: 36px;
    margin: 0 auto;
}
.swal2-popup.apple-alert.apple-loading .swal2-html-container { display: none; }
.swal2-popup.apple-alert.apple-loading .swal2-actions { display: none; }

/* ── Inputs (if a caller uses Swal's input mode) ────────── */
.swal2-popup.apple-alert .swal2-input,
.swal2-popup.apple-alert .swal2-textarea,
.swal2-popup.apple-alert .swal2-select {
    border-radius: 12px;
    border: 1.5px solid #E2E8F0;
    font-family: inherit;
    font-size: 14px;
    padding: 10px 14px;
    color: #0F172A;
    margin: 14px 0 0;
    width: 100%;
    transition: all 0.15s;
}
.swal2-popup.apple-alert .swal2-input:focus,
.swal2-popup.apple-alert .swal2-textarea:focus,
.swal2-popup.apple-alert .swal2-select:focus {
    border-color: #007AFF;
    box-shadow: 0 0 0 3.5px rgba(0, 122, 255, 0.15);
    outline: none;
}

/* ── Toast (top-center, Apple-notification-style) ───────── */
.swal2-container.apple-toast-container {
    backdrop-filter: none;
    background: transparent;
    padding-top: 16px;
    align-items: flex-start;
    justify-content: center;
}
.swal2-popup.apple-toast {
    background: rgba(15, 23, 42, 0.92);
    color: #FFFFFF;
    border-radius: 14px;
    padding: 12px 18px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.24);
    font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", Inter, system-ui, sans-serif;
    font-size: 14px;
    font-weight: 500;
    letter-spacing: -0.005em;
    width: auto;
    max-width: 380px;
    min-width: 0;
    border: none;
    animation: appleToastIn 0.32s cubic-bezier(0.34, 1.56, 0.64, 1);
    backdrop-filter: blur(30px) saturate(180%);
    -webkit-backdrop-filter: blur(30px) saturate(180%);
}
@keyframes appleToastIn {
    from { transform: translateY(-20px) scale(0.94); opacity: 0; }
    to   { transform: translateY(0) scale(1); opacity: 1; }
}
.swal2-popup.apple-toast .swal2-title {
    font-size: 14px;
    font-weight: 500;
    color: #FFFFFF;
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center;
    gap: 8px;
    line-height: 1.35;
}
.swal2-popup.apple-toast .swal2-title .apple-toast-icon {
    font-size: 16px;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
}
.swal2-popup.apple-toast .swal2-html-container { display: none; }
.swal2-popup.apple-toast .swal2-actions { display: none; }
.swal2-popup.apple-toast .swal2-timer-progress-bar {
    background: rgba(255, 255, 255, 0.35);
    height: 2px;
    border-radius: 2px;
}
.swal2-popup.apple-toast.apple-toast-success .apple-toast-icon { color: #34C759; }
.swal2-popup.apple-toast.apple-toast-error   .apple-toast-icon { color: #FF453A; }
.swal2-popup.apple-toast.apple-toast-info    .apple-toast-icon { color: #0A84FF; }
.swal2-popup.apple-toast.apple-toast-warning .apple-toast-icon { color: #FF9F0A; }

/* ── Compact HTML helpers used inside alert bodies ──────── */
.apple-alert-list {
    list-style: none;
    margin: 12px 0 0;
    padding: 0;
    text-align: left;
    border-radius: 12px;
    overflow: hidden;
    background: #F8FAFC;
    border: 1px solid #E2E8F0;
}
.apple-alert-list li {
    padding: 10px 14px;
    font-size: 13px;
    color: #334155;
    border-bottom: 1px solid #E2E8F0;
    line-height: 1.4;
}
.apple-alert-list li:last-child { border-bottom: none; }
.apple-alert-list li strong { color: #0F172A; }
.apple-alert-list li em { color: #64748B; font-style: normal; }

.apple-alert-badge-row {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 10px;
    justify-content: center;
}
.apple-alert-badge {
    font-size: 11px;
    padding: 5px 10px;
    border-radius: 8px;
    background: #F1F5F9;
    color: #475569;
    font-weight: 600;
    border: 1px solid #E2E8F0;
    cursor: default;
}
.apple-alert-badge.interactive { cursor: pointer; transition: all .15s; }
.apple-alert-badge.interactive:hover {
    background: #007AFF;
    color: #FFF;
    border-color: #007AFF;
}

.apple-alert-code-block {
    display: inline-block;
    padding: 10px 18px;
    margin: 12px 0 0;
    background: #F1F5F9;
    border-radius: 10px;
    font-family: "SF Mono", ui-monospace, Menlo, monospace;
    font-size: 17px;
    font-weight: 600;
    letter-spacing: 3px;
    color: #0F172A;
    border: 1px solid #E2E8F0;
}

/* ── Reduced motion ─────────────────────────────────────── */
@media (prefers-reduced-motion: reduce) {
    .swal2-popup.apple-alert,
    .swal2-popup.apple-toast { animation: none; }
}
</style>

<script>
/* ============================================================
   AppleAlert — wrapper around SweetAlert2 with an iOS-style theme.
   Every helper returns the underlying Swal promise so callers can
   chain .then() as before.
   ============================================================ */
const AppleAlert = (function () {

    // Shared Swal mixin — every dialog we open goes through this so the
    // theme, container class, and animation stay consistent.
    const Dialog = Swal.mixin({
        customClass: {
            container: 'apple-alert-container',
            popup:     'apple-alert',
        },
        buttonsStyling: false,
        reverseButtons: false,
        showClass: { popup: '' },        // popup uses CSS keyframe, not Swal's anim
        hideClass: { popup: '' },
        heightAuto: false,
    });

    // Toasts are a separate mixin — top-center, no backdrop, small.
    const Toast = Swal.mixin({
        toast: true,
        position: 'top',
        showConfirmButton: false,
        timer: 2000,
        timerProgressBar: true,
        customClass: {
            container: 'apple-toast-container',
            popup:     'apple-toast',
        },
        showClass: { popup: '' },
        hideClass: { popup: '' },
        didOpen: (el) => {
            el.addEventListener('mouseenter', Swal.stopTimer);
            el.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    // ── Loading spinner ────────────────────────────────────────
    // Cancel-proof spinner-only alert. Call AppleAlert.close() to dismiss.
    function loading(title = 'Processing…') {
        return Dialog.fire({
            title: title,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
                // Add the loading class so our CSS can size the spinner.
                const popup = document.querySelector('.swal2-popup.apple-alert');
                if (popup) popup.classList.add('apple-loading');
            },
        });
    }

    function close() {
        Swal.close();
    }

    // ── Simple dialogs ─────────────────────────────────────────
    function success(title, text = null) {
        return Dialog.fire({
            icon: 'success',
            title: title,
            html: text ? text : undefined,
            confirmButtonText: 'OK',
            timer: 2200,
            timerProgressBar: true,
        });
    }

    function error(title, text = null) {
        return Dialog.fire({
            icon: 'error',
            title: title,
            html: text ? text : undefined,
            confirmButtonText: 'OK',
        });
    }

    function warning(title, text = null) {
        return Dialog.fire({
            icon: 'warning',
            title: title,
            html: text ? text : undefined,
            confirmButtonText: 'OK',
        });
    }

    function info(title, text = null) {
        return Dialog.fire({
            icon: 'info',
            title: title,
            html: text ? text : undefined,
            confirmButtonText: 'OK',
        });
    }

    // ── Generic confirm (blue primary) ─────────────────────────
    function confirm(title, text = null, opts = {}) {
        return Dialog.fire({
            title: title,
            html: text ? text : undefined,
            icon: opts.icon || undefined,
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Continue',
            cancelButtonText:  opts.cancelText  || 'Cancel',
            focusCancel: !!opts.focusCancel,
            width: opts.width || undefined,
        });
    }

    // ── Destructive confirm (red primary) ─────────────────────
    function destructive(title, text = null, opts = {}) {
        return Dialog.fire({
            title: title,
            html: text ? text : undefined,
            icon: opts.icon || undefined,
            showCancelButton: true,
            confirmButtonText: opts.confirmText || 'Delete',
            cancelButtonText:  opts.cancelText  || 'Cancel',
            focusCancel: opts.focusCancel !== false,
            width: opts.width || undefined,
            didOpen: () => {
                const btn = document.querySelector('.swal2-popup.apple-alert .swal2-confirm');
                if (btn) btn.classList.add('apple-destructive');
            },
        });
    }

    // ── Rich-HTML dialog (conflict override, save-run code, etc.) ─
    // Accepts a full options object; caller controls everything except
    // the theme. `theme` picks the confirm button colour.
    //   theme: 'primary' | 'destructive' | 'warning' | 'success'
    function rich(options = {}) {
        const theme = options.theme || 'primary';

        return Dialog.fire({
            title: options.title || '',
            html: options.html || '',
            icon: options.icon || undefined,
            showCancelButton: !!options.showCancelButton,
            confirmButtonText: options.confirmText || 'OK',
            cancelButtonText:  options.cancelText  || 'Cancel',
            focusCancel: options.focusCancel === true,
            width: options.width || undefined,
            allowOutsideClick: options.allowOutsideClick !== false,
            allowEscapeKey: options.allowEscapeKey !== false,
            didOpen: () => {
                if (theme !== 'primary') {
                    const btn = document.querySelector('.swal2-popup.apple-alert .swal2-confirm');
                    if (btn) btn.classList.add('apple-' + theme);
                }
                if (typeof options.didOpen === 'function') options.didOpen();
            },
            preConfirm: typeof options.preConfirm === 'function' ? options.preConfirm : undefined,
        });
    }

    // ── Toast notification ────────────────────────────────────
    // type: 'success' | 'error' | 'info' | 'warning'
    function toast(message, type = 'success', durationMs = 2000) {
        const icons = {
            success: '✓',
            error:   '✕',
            info:    'ⓘ',
            warning: '⚠',
        };
        const icon = icons[type] || icons.success;

        return Toast.fire({
            title: `<span class="apple-toast-icon">${icon}</span><span>${message}</span>`,
            timer: durationMs,
            customClass: {
                container: 'apple-toast-container',
                popup:     `apple-toast apple-toast-${type}`,
            },
        });
    }

    // ── Convenience wrappers used across the blades ───────────
    // These collapse the most common call patterns into one-liners
    // and keep the wording consistent.

    // "Are you sure you want to delete X? Yes / Cancel" → returns bool.
    async function confirmDelete(title, text) {
        const r = await destructive(title, text, { confirmText: 'Delete' });
        return !!r.isConfirmed;
    }

    // Fire-and-forget confirmation toast.
    function saved(message = 'Saved') { return toast(message, 'success'); }
    function deleted(message = 'Deleted') { return toast(message, 'success'); }
    function copied(message = 'Copied to clipboard') { return toast(message, 'info'); }
    function failed(message = 'Something went wrong') { return toast(message, 'error', 2600); }

    // ── Public surface ────────────────────────────────────────
    return {
        // Core
        loading, close,
        success, error, warning, info,
        confirm, destructive, rich, toast,
        // Convenience
        confirmDelete, saved, deleted, copied, failed,
        // Escape hatch: expose the underlying mixins if a caller ever
        // needs full control without losing the theme.
        Dialog, Toast,
    };
})();
</script>