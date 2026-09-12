(function ($) {
    'use strict';

    window.showHandbookToast = function (message, type) {
        const region = document.getElementById('handbook_toast_region');
        if (!region || !message) return;
        const toast = document.createElement('div');
        toast.className = 'hb-toast ' + (type === 'danger' ? 'is-danger' : type === 'success' ? 'is-success' : '');
        toast.setAttribute('role', 'status');
        const icon = document.createElement('i');
        icon.className = type === 'danger' ? 'bi bi-exclamation-circle-fill' : type === 'success' ? 'bi bi-check-circle-fill' : 'bi bi-info-circle-fill';
        const text = document.createElement('span');
        text.textContent = String(message);
        toast.append(icon, text);
        region.appendChild(toast);
        window.setTimeout(function () { toast.remove(); }, 3600);
    };

    function enhanceOtp(source) {
        if (!source || source.dataset.enhanced === '1') return;
        source.dataset.enhanced = '1';
        source.required = false;
        source.classList.add('hb-otp-source');

        const boxes = document.createElement('div');
        boxes.className = 'hb-otp-boxes';
        boxes.setAttribute('role', 'group');
        boxes.setAttribute('aria-label', 'Mã xác minh 6 chữ số');
        const inputs = [];

        function sync() {
            source.value = inputs.map(function (input) { return input.value; }).join('');
            inputs.forEach(function (input) { input.classList.toggle('is-filled', !!input.value); });
        }

        for (let i = 0; i < 6; i += 1) {
            const input = document.createElement('input');
            input.type = 'text';
            input.inputMode = 'numeric';
            input.maxLength = 1;
            input.className = 'hb-otp-box';
            input.autocomplete = i === 0 ? 'one-time-code' : 'off';
            input.setAttribute('aria-label', 'Chữ số ' + (i + 1));
            input.addEventListener('input', function () {
                input.value = input.value.replace(/\D/g, '').slice(-1);
                sync();
                if (input.value && inputs[i + 1]) inputs[i + 1].focus();
            });
            input.addEventListener('keydown', function (event) {
                if (event.key === 'Backspace' && !input.value && inputs[i - 1]) inputs[i - 1].focus();
                if (event.key === 'ArrowLeft' && inputs[i - 1]) { event.preventDefault(); inputs[i - 1].focus(); }
                if (event.key === 'ArrowRight' && inputs[i + 1]) { event.preventDefault(); inputs[i + 1].focus(); }
            });
            input.addEventListener('paste', function (event) {
                const pasted = (event.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
                if (!pasted) return;
                event.preventDefault();
                pasted.split('').forEach(function (digit, index) { if (inputs[index]) inputs[index].value = digit; });
                sync();
                (inputs[Math.min(pasted.length, 6) - 1] || inputs[0]).focus();
            });
            inputs.push(input);
            boxes.appendChild(input);
        }

        source.insertAdjacentElement('afterend', boxes);
        if (/^\d{6}$/.test(source.value)) {
            source.value.split('').forEach(function (digit, index) { inputs[index].value = digit; });
            sync();
        }

        const form = source.closest('form');
        if (form) {
            form.addEventListener('submit', function (event) {
                sync();
                if (!/^\d{6}$/.test(source.value)) {
                    event.preventDefault();
                    const firstEmpty = inputs.find(function (input) { return !input.value; }) || inputs[0];
                    firstEmpty.focus();
                    window.showHandbookToast('Vui lòng nhập đủ 6 chữ số xác minh.', 'danger');
                }
            });
        }
    }

    document.querySelectorAll('.hb-otp-input').forEach(enhanceOtp);

    document.querySelectorAll('[data-otp-expires]').forEach(function (node) {
        const expiresAt = Number(node.getAttribute('data-otp-expires')) || 0;
        if (!expiresAt) return;
        function tick() {
            const remaining = Math.max(0, expiresAt - Math.floor(Date.now() / 1000));
            const minutes = Math.floor(remaining / 60);
            const seconds = String(remaining % 60).padStart(2, '0');
            node.textContent = remaining > 0 ? ('Còn ' + String(minutes).padStart(2, '0') + ':' + seconds) : 'Mã đã hết hạn';
            node.classList.toggle('text-danger', remaining === 0);
            return remaining;
        }
        tick();
        const timer = window.setInterval(function () { if (tick() === 0) window.clearInterval(timer); }, 1000);
    });

    document.querySelectorAll('input[data-confirm-password]').forEach(function (confirmInput) {
        const form = confirmInput.closest('form');
        const password = form && form.querySelector('input[name="password"]');
        if (!form || !password) return;
        const error = document.createElement('div');
        error.className = 'hb-password-match-error';
        error.hidden = true;
        error.textContent = 'Hai mật khẩu chưa trùng khớp.';
        confirmInput.insertAdjacentElement('afterend', error);
        function validate() {
            const mismatch = !!confirmInput.value && confirmInput.value !== password.value;
            error.hidden = !mismatch;
            confirmInput.setCustomValidity(mismatch ? 'Hai mật khẩu chưa trùng khớp.' : '');
            return !mismatch;
        }
        confirmInput.addEventListener('input', validate);
        password.addEventListener('input', validate);
        form.addEventListener('submit', function (event) {
            if (!validate() || !confirmInput.value) {
                if (!confirmInput.value) confirmInput.setCustomValidity('Vui lòng nhập lại mật khẩu.');
                if (!confirmInput.checkValidity()) {
                    event.preventDefault();
                    confirmInput.reportValidity();
                }
            }
        });
    });

    const filterButtons = document.querySelectorAll('#notification_sidebar .hb-panel-filter button');
    const notificationItems = function () { return Array.from(document.querySelectorAll('#notification_sidebar .hb-notification-item')); };
    filterButtons.forEach(function (button, index) {
        button.addEventListener('click', function () {
            filterButtons.forEach(function (item) { item.classList.remove('is-active'); });
            button.classList.add('is-active');
            notificationItems().forEach(function (item) { item.hidden = index === 1 && !item.classList.contains('is-unread'); });
        });
    });

    const conversationSearch = document.getElementById('conversation_search');
    if (conversationSearch) {
        conversationSearch.addEventListener('input', function () {
            const needle = conversationSearch.value.trim().toLocaleLowerCase('vi');
            document.querySelectorAll('#chatlist .chatlist_item').forEach(function (row) {
                row.hidden = !!needle && !row.textContent.toLocaleLowerCase('vi').includes(needle);
            });
        });
    }

    window.reportPost = function (postId) {
        if (!window.confirm('Bạn có chắc muốn báo cáo bài viết này không?')) return;
        const body = new URLSearchParams({post_id: String(postId)});
        window.handbookFetch('?action=report_post', {method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:body.toString()})
            .then(async function (response) {
                const data = await response.json();
                if (!response.ok || !data.status) throw new Error(data.message || 'Không thể báo cáo bài viết này.');
                window.showHandbookToast(data.message || 'Đã gửi báo cáo tới quản trị viên.', 'success');
            })
            .catch(function (error) { window.showHandbookToast(error.message || 'Không thể báo cáo bài viết.', 'danger'); });
    };
})(window.jQuery);
