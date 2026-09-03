document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('[data-order-details-toggle]');

    toggles.forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const targetId = toggle.getAttribute('aria-controls');

            if (!targetId) {
                return;
            }

            const details = document.getElementById(targetId);

            if (!details) {
                return;
            }

            const isHidden = details.hasAttribute('hidden');

            details.toggleAttribute('hidden', !isHidden);
            toggle.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
        });
    });
});