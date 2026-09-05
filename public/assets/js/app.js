(() => {
    'use strict';

    const focusMainOnHashSkip = () => {
        if (window.location.hash === '#main-content') {
            const main = document.getElementById('main-content');

            if (main instanceof HTMLElement) {
                main.focus();
            }
        }
    };

    const enhanceDismissibleAlerts = () => {
        document.querySelectorAll('.alert-dismissible .btn-close').forEach((button) => {
            button.addEventListener('click', () => {
                const alert = button.closest('.alert');

                if (alert instanceof HTMLElement) {
                    alert.setAttribute('aria-hidden', 'true');
                }
            });
        });
    };

    const autoDismissFlashes = () => {
        document.querySelectorAll('[data-flash-auto-dismiss]').forEach((alert) => {
            if (!(alert instanceof HTMLElement)) {
                return;
            }

            if (alert.classList.contains('app-flash-danger')) {
                return;
            }

            const delay = Number(alert.dataset.flashAutoDismiss || '8000');

            if (!Number.isFinite(delay) || delay <= 0) {
                return;
            }

            window.setTimeout(() => {
                if (!alert.isConnected) {
                    return;
                }

                alert.classList.remove('show');
                window.setTimeout(() => alert.remove(), 200);
            }, delay);
        });
    };

    const markActiveNavLinks = () => {
        const path = window.location.pathname.replace(/\/+$/, '') || '/';
        const links = Array.from(
            document.querySelectorAll('.app-navbar .nav-link[href]'),
        ).filter((link) => link instanceof HTMLAnchorElement);

        let bestLink = null;
        let bestScore = -1;

        links.forEach((link) => {
            const href = link.getAttribute('href') || '';
            const normalized = href.replace(/\/+$/, '') || '/';
            let score = -1;

            if (normalized === '/') {
                score = path === '/' ? 1000 : -1;
            } else if (path === normalized) {
                score = normalized.length + 100;
            } else if (path.startsWith(`${normalized}/`)) {
                score = normalized.length;
            }

            link.classList.remove('is-active');
            link.removeAttribute('aria-current');

            if (score > bestScore) {
                bestScore = score;
                bestLink = link;
            }
        });

        if (bestLink instanceof HTMLAnchorElement && bestScore >= 0) {
            bestLink.classList.add('is-active');
            bestLink.setAttribute('aria-current', 'page');
        }
    };

    const bindConfirmPopups = () => {
        const modalElement = document.getElementById('appConfirmModal');

        if (!(modalElement instanceof HTMLElement) || typeof bootstrap === 'undefined') {
            return;
        }

        const titleEl = document.getElementById('appConfirmTitle');
        const messageEl = document.getElementById('appConfirmMessage');
        const acceptButton = document.getElementById('appConfirmAccept');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);

        let pendingForm = null;

        const openConfirm = (form) => {
            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            const message = form.getAttribute('data-confirm')
                || 'Are you sure you want to continue?';
            const title = form.getAttribute('data-confirm-title') || 'Please confirm';
            const confirmLabel = form.getAttribute('data-confirm-label') || 'Confirm';
            const tone = form.getAttribute('data-confirm-tone') || 'primary';

            pendingForm = form;
            modalElement.classList.toggle('is-danger', tone === 'danger');

            if (titleEl instanceof HTMLElement) {
                titleEl.textContent = title;
            }

            if (messageEl instanceof HTMLElement) {
                messageEl.textContent = message;
            }

            if (acceptButton instanceof HTMLButtonElement) {
                acceptButton.textContent = confirmLabel;
                acceptButton.className = tone === 'danger'
                    ? 'btn btn-danger'
                    : 'btn btn-primary';
            }

            modal.show();
        };

        document.addEventListener('submit', (event) => {
            const form = event.target;

            if (!(form instanceof HTMLFormElement)) {
                return;
            }

            if (!form.hasAttribute('data-confirm')) {
                return;
            }

            if (form.dataset.confirmAccepted === '1') {
                delete form.dataset.confirmAccepted;
                return;
            }

            event.preventDefault();
            openConfirm(form);
        });

        if (acceptButton instanceof HTMLButtonElement) {
            acceptButton.addEventListener('click', () => {
                if (!(pendingForm instanceof HTMLFormElement)) {
                    modal.hide();
                    return;
                }

                const form = pendingForm;
                pendingForm = null;
                form.dataset.confirmAccepted = '1';
                modal.hide();
                form.requestSubmit();
            });
        }

        modalElement.addEventListener('hidden.bs.modal', () => {
            pendingForm = null;
        });
    };

    const initLoginTransition = () => {
        const showLoginButton = document.getElementById('show-login-form');
        const loginStart = document.getElementById('login-start');
        const loginForm = document.getElementById('login-form');

        if (
            !(showLoginButton instanceof HTMLButtonElement) ||
            !(loginStart instanceof HTMLElement) ||
            !(loginForm instanceof HTMLFormElement)
        ) {
            return;
        }

        const revealForm = () => {
            loginStart.classList.add('d-none');
            loginForm.classList.remove('d-none');

            const emailInput = document.getElementById('email');

            if (emailInput instanceof HTMLInputElement) {
                emailInput.focus();
            }
        };

        showLoginButton.addEventListener('click', revealForm);

        const hasPrefill = Boolean(
            loginForm.querySelector('input[name="email"]')?.value
        );
        const hasErrors = document.querySelector('.app-form-errors, .app-flash-danger');

        if (hasPrefill || hasErrors) {
            revealForm();
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        focusMainOnHashSkip();
        enhanceDismissibleAlerts();
        autoDismissFlashes();
        markActiveNavLinks();
        bindConfirmPopups();
        initLoginTransition();
    });
})();
