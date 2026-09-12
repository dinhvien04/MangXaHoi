(function ($) {
    'use strict';
    if (!$) return;

    function call(button, api, userId, done) {
        const control = $(button);
        control.prop('disabled', true);
        $.ajax({url: '?api=' + api, method: 'post', dataType: 'json', data: {user_id: userId}})
            .done(function (response) {
                if (response.status) done(response);
                else control.prop('disabled', false);
            })
            .fail(function () {
                control.prop('disabled', false);
                if (window.showHandbookToast) window.showHandbookToast('Không thể thực hiện thao tác.', 'danger'); else alert('Không thể thực hiện thao tác.');
            });
    }

    $('.verify_user_btn').on('click', function () {
        const button = this;
        call(button, 'verify_user', $(button).data('userId'), function () { $(button).text('Hoạt động').removeClass('is-warning').addClass('is-success').prop('disabled', true); });
    });
    $('.block_user_btn').on('click', function () {
        const button = this;
        call(button, 'block_user', $(button).data('userId'), function () { $(button).hide(); $(button).siblings('.unblock_user_btn').show().prop('disabled', false); });
    });
    $('.unblock_user_btn').on('click', function () {
        const button = this;
        call(button, 'unblock_user', $(button).data('userId'), function () { $(button).hide(); $(button).siblings('.block_user_btn').show().prop('disabled', false); });
    });

    const table = document.querySelector('.hb-admin-table tbody');
    if (!table) return;

    const overlay = document.createElement('div');
    overlay.className = 'hb-admin-detail-overlay';
    overlay.innerHTML = '<section class="hb-admin-detail-card" role="dialog" aria-modal="true" aria-labelledby="hbAdminDetailTitle"><header class="hb-admin-detail-head"><h2 id="hbAdminDetailTitle">Chi tiết người dùng</h2><button class="hb-admin-detail-close" type="button" aria-label="Đóng"><i class="fas fa-times"></i></button></header><dl class="hb-admin-detail-body"></dl></section>';
    document.body.appendChild(overlay);
    const body = overlay.querySelector('.hb-admin-detail-body');
    const closeButton = overlay.querySelector('.hb-admin-detail-close');

    function closeDetail() {
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
    }
    function openDetail(row) {
        const cells = row.querySelectorAll('td');
        if (cells.length < 5) return;
        const values = [
            ['ID', cells[0].textContent.trim()],
            ['Người dùng', cells[1].textContent.replace(/\s+/g, ' ').trim()],
            ['Email', cells[2].textContent.trim()],
            ['Trạng thái', cells[3].textContent.replace(/\s+/g, ' ').trim()],
            ['Vai trò', cells[4].textContent.replace(/\s+/g, ' ').trim()]
        ];
        body.replaceChildren();
        values.forEach(function (entry) {
            const dt = document.createElement('dt'); dt.textContent = entry[0];
            const dd = document.createElement('dd'); dd.textContent = entry[1];
            body.append(dt, dd);
        });
        overlay.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        closeButton.focus();
    }

    table.querySelectorAll('tr').forEach(function (row) {
        const actions = row.querySelector('.hb-admin-table-actions');
        if (!actions) return;
        const detail = document.createElement('button');
        detail.type = 'button';
        detail.className = 'hb-admin-action hb-admin-detail-trigger';
        detail.innerHTML = '<i class="fas fa-eye"></i> Chi tiết';
        detail.addEventListener('click', function () { openDetail(row); });
        actions.appendChild(detail);
    });

    closeButton.addEventListener('click', closeDetail);
    overlay.addEventListener('click', function (event) { if (event.target === overlay) closeDetail(); });
    document.addEventListener('keydown', function (event) { if (event.key === 'Escape' && overlay.classList.contains('is-open')) closeDetail(); });
})(window.jQuery);
