(() => {
    'use strict';

    const root = document.querySelector('[data-catalog-root]');
    const panels = document.querySelector('[data-catalog-panels]');

    if (!(root instanceof HTMLElement) || !(panels instanceof HTMLElement)) {
        return;
    }

    let activeRequest = null;

    const setLoading = (isLoading) => {
        panels.classList.toggle('is-loading', isLoading);
        panels.setAttribute('aria-busy', isLoading ? 'true' : 'false');
    };

    const replaceSection = (selector, html) => {
        if (typeof html !== 'string' || html === '') {
            return;
        }

        const current = panels.querySelector(selector);

        if (!(current instanceof HTMLElement)) {
            return;
        }

        const template = document.createElement('template');
        template.innerHTML = html.trim();
        const next = template.content.firstElementChild;

        if (next) {
            current.replaceWith(next);
        }
    };

    const loadCatalog = async (url, { push = true } = {}) => {
        if (activeRequest) {
            activeRequest.abort();
        }

        const controller = new AbortController();
        activeRequest = controller;
        setLoading(true);

        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                signal: controller.signal,
            });

            if (!response.ok) {
                throw new Error(`Catalog request failed (${response.status})`);
            }

            const payload = await response.json();
            replaceSection('#catalog-available, [data-catalog-section="available"]', payload.available);
            replaceSection('#catalog-curated, [data-catalog-section="curated"]', payload.curated);

            if (push) {
                window.history.pushState({ catalogAjax: true }, '', payload.url || url);
            }
        } catch (error) {
            if (error && error.name === 'AbortError') {
                return;
            }

            window.location.assign(url);
        } finally {
            if (activeRequest === controller) {
                activeRequest = null;
                setLoading(false);
            }
        }
    };

    panels.addEventListener('click', (event) => {
        const link = event.target instanceof Element
            ? event.target.closest('[data-catalog-ajax]')
            : null;

        if (!(link instanceof HTMLAnchorElement)) {
            return;
        }

        if (event.defaultPrevented || event.button !== 0) {
            return;
        }

        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
            return;
        }

        event.preventDefault();
        loadCatalog(link.href, { push: true });
    });

    window.addEventListener('popstate', () => {
        loadCatalog(window.location.href, { push: false });
    });
})();
