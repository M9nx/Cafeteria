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

    document.addEventListener('DOMContentLoaded', () => {
        focusMainOnHashSkip();
        enhanceDismissibleAlerts();
    });
})();
