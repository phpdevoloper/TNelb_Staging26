/**
 * All admin pages using .secondary-nav: show an extra centered title from the
 * active breadcrumb (or data-nav-heading on .breadcrumbs-container). Wraps the
 * existing toggle + breadcrumb in .page-nav-bar__main. Skip with .page-nav-bar--skip on the header.
 */
(function () {
    'use strict';

    function titleFromBreadcrumb(header) {
        var active = header.querySelector('.breadcrumb-item.active');
        if (active) {
            var t = active.textContent.replace(/\s+/g, ' ').trim();
            if (t) {
                return t;
            }
        }
        var items = header.querySelectorAll('.breadcrumb .breadcrumb-item');
        if (!items.length) {
            return '';
        }
        var last = items[items.length - 1];
        var link = last.querySelector('a[href]');
        var raw = (link ? link.textContent : last.textContent).replace(/\s+/g, ' ').trim();
        if (raw === '/' || raw === '') {
            return '';
        }
        return raw;
    }

    function init() {
        document.querySelectorAll('.secondary-nav .breadcrumbs-container .header.navbar').forEach(function (header) {
            if (header.classList.contains('page-nav-bar--skip')) {
                return;
            }
            if (header.querySelector('.page-nav-bar__main')) {
                return;
            }

            var container = header.closest('.breadcrumbs-container');
            var titleText = '';
            if (container) {
                var dh = container.getAttribute('data-nav-heading');
                if (dh) {
                    titleText = dh.trim();
                }
            }
            if (!titleText) {
                titleText = titleFromBreadcrumb(header);
            }
            if (!titleText) {
                return;
            }

            header.classList.add('page-nav-bar');

            var main = document.createElement('div');
            main.className = 'page-nav-bar__main d-flex align-items-center flex-wrap w-100';
            while (header.firstChild) {
                main.appendChild(header.firstChild);
            }

            var heading = document.createElement('p');
            heading.className = 'page-nav-bar__heading mb-0';
            heading.setAttribute('aria-hidden', 'true');
            heading.textContent = titleText;

            header.appendChild(heading);
            header.appendChild(main);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
