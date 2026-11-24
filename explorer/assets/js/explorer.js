/*
 * Projects Explorer: page behaviour.
 * Theme switch, live search in the current folder, and closing the sidebar folder list on phones.
 * Hooks use data-* attributes only.
 */
(function () {
    'use strict';

    var THEME_KEY = 'pv-theme';
    var root = document.documentElement;

    /* ---- Theme switch ---- */

    /** The theme showing now: the one picked with the button, or the system's. */
    function currentTheme() {
        var picked = root.getAttribute('data-theme');
        if (picked === 'light' || picked === 'dark') {
            return picked;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    }

    /** Button text says what clicking will do. */
    function updateThemeButton(button) {
        var next = currentTheme() === 'dark' ? 'light' : 'dark';
        button.textContent = next === 'dark' ? 'Dark theme' : 'Light theme';
        button.setAttribute('aria-label', 'Switch to ' + next + ' theme');
    }

    function setupTheme() {
        var button = document.querySelector('[data-theme-toggle]');
        if (!button) {
            return;
        }
        updateThemeButton(button);
        button.addEventListener('click', function () {
            var next = currentTheme() === 'dark' ? 'light' : 'dark';
            root.setAttribute('data-theme', next);
            try {
                localStorage.setItem(THEME_KEY, next);
            } catch (e) {
                // Storage can be blocked (private windows); the theme still changes for this visit.
            }
            updateThemeButton(button);
        });
    }

    /* ---- Search ---- */

    function setupSearch() {
        var input = document.querySelector('[data-search]');
        var emptyNotice = document.querySelector('[data-search-empty]');
        if (!input) {
            return;
        }
        var items = document.querySelectorAll('[data-item]');

        input.addEventListener('input', function () {
            var query = input.value.trim().toLowerCase();
            var shown = 0;
            items.forEach(function (item) {
                var match = query === '' || (item.getAttribute('data-search-text') || '').indexOf(query) !== -1;
                item.classList.toggle('is-hidden', !match);
                if (match) {
                    shown++;
                }
            });
            if (emptyNotice) {
                emptyNotice.classList.toggle('is-hidden', query === '' || shown > 0);
            }
        });
    }

    /* ---- Sidebar folder list: closed on narrow screens so the content comes first ---- */

    function setupSidebar() {
        var list = document.querySelector('[data-side-folders]');
        if (list && window.matchMedia('(max-width: 980px)').matches) {
            list.removeAttribute('open');
        }
    }

    setupTheme();
    setupSearch();
    setupSidebar();
})();
