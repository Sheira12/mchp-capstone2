/**
 * Reusable Live Search — Parish Management System
 *
 * Attaches to any form with [data-live-search] attribute.
 * Submits the form automatically after the user stops typing (300ms debounce).
 * Uses fetch() to get an HTML fragment or JSON, then replaces the target element.
 *
 * Usage (simple — auto-submits the form on input):
 *   <form method="GET" data-live-search data-target="#table-wrapper">
 *     <input type="text" name="search" data-live-input>
 *   </form>
 *   <div id="table-wrapper">...table...</div>
 *
 * The server returns the full page; we extract #table-wrapper from the response.
 */
(function () {
    'use strict';

    const DEBOUNCE = 300; // ms

    function debounce(fn, delay) {
        let t;
        return function (...args) {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    function initLiveSearch(form) {
        const targetSel = form.dataset.target || '#live-search-target';
        const minChars  = parseInt(form.dataset.min || '0', 10);
        const inputs    = form.querySelectorAll('input[data-live-input], select[data-live-input]');

        if (!inputs.length) return;

        const target = document.querySelector(targetSel);
        if (!target) return;

        async function doSearch() {
            const query  = new URLSearchParams(new FormData(form)).toString();
            const url    = form.action + (form.action.includes('?') ? '&' : '?') + query;

            // Show loading overlay
            target.style.opacity = '0.5';
            target.style.pointerEvents = 'none';

            try {
                const res  = await fetch(url, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }
                });
                const html = await res.text();
                const tmp  = document.createElement('div');
                tmp.innerHTML = html;
                const fresh = tmp.querySelector(targetSel);
                if (fresh) {
                    target.innerHTML = fresh.innerHTML;
                    // Re-init pagination links to also use live search
                    target.querySelectorAll('nav[role="navigation"] a').forEach(a => {
                        a.addEventListener('click', e => {
                            e.preventDefault();
                            const pUrl = new URL(a.href);
                            // Preserve current search params
                            new URLSearchParams(new FormData(form)).forEach((v, k) => {
                                pUrl.searchParams.set(k, v);
                            });
                            fetchAndSwap(pUrl.toString(), targetSel, target);
                        });
                    });
                }
            } catch (err) {
                console.warn('[LiveSearch] fetch error:', err);
            } finally {
                target.style.opacity = '';
                target.style.pointerEvents = '';
            }
        }

        const debouncedSearch = debounce(doSearch, DEBOUNCE);

        inputs.forEach(inp => {
            inp.addEventListener('input', e => {
                if (inp.type === 'text' && inp.value.length > 0 && inp.value.length < minChars) return;
                debouncedSearch();
            });
            // Selects fire immediately
            if (inp.tagName === 'SELECT') {
                inp.addEventListener('change', doSearch);
            }
            // Date inputs fire immediately
            if (inp.type === 'date') {
                inp.addEventListener('change', doSearch);
            }
        });

        // Prevent form from doing a normal submit — all searches happen live
        form.addEventListener('submit', e => {
            e.preventDefault();
            doSearch();
        });
    }

    async function fetchAndSwap(url, targetSel, target) {
        target.style.opacity = '0.5';
        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const html = await res.text();
            const tmp  = document.createElement('div');
            tmp.innerHTML = html;
            const fresh = tmp.querySelector(targetSel);
            if (fresh) target.innerHTML = fresh.innerHTML;
        } finally {
            target.style.opacity = '';
        }
    }

    // Init all forms on page load
    document.querySelectorAll('form[data-live-search]').forEach(initLiveSearch);

    // Re-init after Livewire/Turbo navigations if present
    document.addEventListener('livewire:load', () => {
        document.querySelectorAll('form[data-live-search]').forEach(initLiveSearch);
    });
})();
