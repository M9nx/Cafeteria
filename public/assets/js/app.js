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

        showLoginButton.addEventListener('click', () => {
            loginStart.classList.add('d-none');
            loginForm.classList.remove('d-none');

            const emailInput = document.getElementById('email');

            if (emailInput instanceof HTMLInputElement) {
                emailInput.focus();
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        focusMainOnHashSkip();
        enhanceDismissibleAlerts();
        initLoginTransition();
    });
})();