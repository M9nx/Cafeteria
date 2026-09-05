(() => {
    'use strict';

    const tables = document.querySelectorAll('.reports-sortable-table');

    tables.forEach((table) => {
        const headers = table.querySelectorAll('th[data-sort-key]');
        const tbody = table.querySelector('tbody');

        if (!tbody) {
            return;
        }

        const getRows = () =>
            Array.from(tbody.querySelectorAll('tr[data-report-row]'));

        headers.forEach((header) => {
            header.setAttribute('tabindex', '0');
            header.setAttribute('role', 'button');
            header.setAttribute('aria-sort', 'none');

            const sort = () => {
                const rows = getRows();
                const key = header.dataset.sortKey || '';

                const currentDirection =
                    header.dataset.sortDirection === 'asc'
                        ? 'desc'
                        : 'asc';

                headers.forEach((otherHeader) => {
                    if (otherHeader !== header) {
                        otherHeader.dataset.sortDirection = '';
                        otherHeader.setAttribute('aria-sort', 'none');
                    }
                });

                header.dataset.sortDirection = currentDirection;
                header.setAttribute(
                    'aria-sort',
                    currentDirection === 'asc'
                        ? 'ascending'
                        : 'descending'
                );

                rows.sort((a, b) => {
                    const aCell = a.querySelector(
                        `[data-sort-value="${key}"]`
                    );

                    const bCell = b.querySelector(
                        `[data-sort-value="${key}"]`
                    );

                    const aValue = aCell?.textContent?.trim() ?? '';
                    const bValue = bCell?.textContent?.trim() ?? '';

                    return (
                        aValue.localeCompare(
                            bValue,
                            undefined,
                            {
                                numeric: true,
                                sensitivity: 'base',
                            }
                        ) *
                        (currentDirection === 'asc' ? 1 : -1)
                    );
                });

                rows.forEach((row) => {
                    tbody.appendChild(row);
                });
            };

            header.addEventListener('click', sort);

            header.addEventListener('keydown', (event) => {
                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    sort();
                }
            });
        });
    });

    const searchInput = document.querySelector(
        '[data-report-search]'
    );

    const searchStatus = document.querySelector(
        '[data-report-search-status]'
    );

    const searchEmpty = document.querySelector(
        '[data-report-search-empty]'
    );

    if (searchInput) {
        const rows = document.querySelectorAll(
            '[data-report-row]'
        );

        const updateSearch = () => {
            const searchTerm = searchInput.value
                .trim()
                .toLocaleLowerCase();

            let visibleRows = 0;

            rows.forEach((row) => {
                const rowText = row.textContent
                    .trim()
                    .toLocaleLowerCase();

                const matches =
                    searchTerm === '' ||
                    rowText.includes(searchTerm);

                row.hidden = !matches;

                if (matches) {
                    visibleRows += 1;
                }
            });

            if (searchStatus) {
                searchStatus.textContent =
                    `Showing ${visibleRows} report row(s) on this page.`;
            }

            if (searchEmpty) {
                searchEmpty.classList.toggle(
                    'd-none',
                    visibleRows !== 0
                );
            }
        };

        searchInput.addEventListener('input', updateSearch);
    }
})();