(function () {
    'use strict';
    const meta = document.querySelector('meta[name="csrf-token"]');
    const token = meta ? meta.getAttribute('content') : '';
    window.handbookCsrfToken = token;

    function ensureFormToken(form) {
        if (!form || String(form.method || 'get').toLowerCase() !== 'post' || !token) return;
        let input = form.querySelector('input[name="csrf_token"]');
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'csrf_token';
            form.appendChild(input);
        }
        input.value = token;
    }

    document.querySelectorAll('form').forEach(ensureFormToken);
    document.addEventListener('submit', function (event) {
        ensureFormToken(event.target);
    }, true);

    if (window.jQuery) {
        window.jQuery.ajaxPrefilter(function (options) {
            const method = String(options.type || options.method || 'GET').toUpperCase();
            if (!/^(GET|HEAD|OPTIONS)$/.test(method) && token) {
                options.headers = options.headers || {};
                options.headers['X-CSRF-Token'] = token;
            }
        });
    }

    window.handbookFetch = function (url, options) {
        const next = Object.assign({ credentials: 'same-origin' }, options || {});
        const method = String(next.method || 'GET').toUpperCase();
        next.headers = Object.assign({}, next.headers || {});
        if (!/^(GET|HEAD|OPTIONS)$/.test(method) && token) {
            next.headers['X-CSRF-Token'] = token;
        }
        return window.fetch(url, next);
    };
})();
