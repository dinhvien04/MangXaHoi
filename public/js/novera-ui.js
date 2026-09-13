(function () {
    'use strict';

    function replaceBrand(value) {
        return String(value || '')
            .replace(/HANDBOOK/g, 'NOVERA')
            .replace(/Handbook/g, 'Novera')
            .replace(/handbook/g, 'novera');
    }

    function replaceText(selector) {
        document.querySelectorAll(selector).forEach(function (node) {
            if (node.childElementCount === 0) node.textContent = replaceBrand(node.textContent);
        });
    }

    function applyStaticBranding() {
        document.title = replaceBrand(document.title);

        document.querySelectorAll('.hb-brand-mark').forEach(function (node) {
            node.textContent = 'N';
        });

        [
            '.hb-brand-name',
            '.hb-auth-brand strong',
            '.hb-rail-heading',
            '.hb-cover-brand span',
            '.hb-state-eyebrow',
            '.hb-admin-sidebar-brand strong',
            '.hb-admin-topbar p',
            '.hb-settings-header p'
        ].forEach(replaceText);

        document.querySelectorAll('input[placeholder*="Handbook"], input[aria-label*="Handbook"], [aria-label*="Handbook"], [title*="Handbook"]').forEach(function (node) {
            ['placeholder', 'aria-label', 'title'].forEach(function (attribute) {
                if (node.hasAttribute(attribute)) node.setAttribute(attribute, replaceBrand(node.getAttribute(attribute)));
            });
        });

        document.querySelectorAll('.hb-auth-mobile-brand').forEach(function (node) {
            Array.from(node.childNodes).forEach(function (child) {
                if (child.nodeType === Node.TEXT_NODE) child.nodeValue = replaceBrand(child.nodeValue);
            });
        });

        document.querySelectorAll('.hb-about-row small').forEach(function (node) {
            if (/Handbook/i.test(node.textContent)) node.textContent = replaceBrand(node.textContent);
        });
    }

    function installCompatibilityAliases() {
        if (window.handbookFetch && !window.noveraFetch) window.noveraFetch = window.handbookFetch;
        if (window.showHandbookToast && !window.showNoveraToast) window.showNoveraToast = window.showHandbookToast;
    }

    function installShareHandlers() {
        if (typeof window.sharePost === 'function') {
            window.sharePost = function (postId, username) {
                const url = new URL(window.location.href);
                url.search = '?u=' + encodeURIComponent(username);
                url.hash = 'post-' + postId;
                const shareData = {title: 'Novera Social', text: 'Xem bài viết này trên Novera', url: url.toString()};
                if (navigator.share) {
                    navigator.share(shareData).catch(function () {});
                    return;
                }
                navigator.clipboard?.writeText(url.toString())
                    .then(function () { window.alert('Đã sao chép liên kết bài viết.'); })
                    .catch(function () { window.prompt('Sao chép liên kết bài viết:', url.toString()); });
            };
        }

        if (typeof window.shareProfilePost === 'function') {
            window.shareProfilePost = function (postId) {
                const url = new URL(window.location.href);
                url.hash = 'post-' + postId;
                const shareData = {title: 'Novera Social', text: 'Xem bài viết này trên Novera', url: url.toString()};
                if (navigator.share) {
                    navigator.share(shareData).catch(function () {});
                    return;
                }
                navigator.clipboard?.writeText(url.toString())
                    .then(function () { window.alert('Đã sao chép liên kết bài viết.'); })
                    .catch(function () { window.prompt('Sao chép liên kết:', url.toString()); });
            };
        }
    }

    function installAdminMobileMenu() {
        const toggle = document.getElementById('hb_admin_mobile_menu_toggle');
        const menu = document.getElementById('hb_admin_mobile_menu');
        if (!toggle || !menu) return;

        function setOpen(open) {
            menu.classList.toggle('is-open', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.classList.toggle('hb-admin-menu-open', open);
        }

        toggle.addEventListener('click', function () {
            setOpen(!menu.classList.contains('is-open'));
        });
        menu.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () { setOpen(false); });
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') setOpen(false);
        });
    }

    function boot() {
        applyStaticBranding();
        installCompatibilityAliases();
        installShareHandlers();
        installAdminMobileMenu();
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot, {once: true});
    else boot();
})();
