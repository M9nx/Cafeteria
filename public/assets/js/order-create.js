(() => {
    'use strict';

    const root = document.querySelector('[data-order-create]');

    if (!(root instanceof HTMLElement)) {
        return;
    }

    const searchInput = document.getElementById('order-product-search');
    const emptyFilter = document.getElementById('order-filter-empty');
    const chips = Array.from(root.querySelectorAll('[data-category-filter]'));
    const cards = Array.from(root.querySelectorAll('.order-product-card'));

    let activeCategory = '';

    const applyFilters = () => {
        const query = searchInput instanceof HTMLInputElement
            ? searchInput.value.trim().toLowerCase()
            : '';

        let visible = 0;

        cards.forEach((card) => {
            if (!(card instanceof HTMLElement)) {
                return;
            }

            const name = (card.dataset.productName || '').toLowerCase();
            const category = card.dataset.productCategory || '';
            const matchesCategory = activeCategory === '' || category === activeCategory;
            const matchesQuery = query === '' || name.includes(query);
            const show = matchesCategory && matchesQuery;

            card.classList.toggle('d-none', !show);
            if (show) {
                visible += 1;
            }
        });

        if (emptyFilter instanceof HTMLElement) {
            emptyFilter.classList.toggle('d-none', visible > 0);
        }
    };

    chips.forEach((chip) => {
        chip.addEventListener('click', () => {
            activeCategory = chip.getAttribute('data-category-filter') || '';
            chips.forEach((item) => item.classList.toggle('is-active', item === chip));
            applyFilters();
        });
    });

    if (searchInput instanceof HTMLInputElement) {
        searchInput.addEventListener('input', applyFilters);
    }
})();
