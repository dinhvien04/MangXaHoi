(function ($) {
    'use strict';
    if (!$) return;
    let timer = null;
    const search = $('#search');
    const result = $('#search_result');

    search.on('focus', function () { result.show(); });
    $('#close_search').on('click', function () { result.hide(); search.trigger('focus'); });

    function renderUsers(users) {
        const root = $('#sra').empty();
        if (!users || !users.length) {
            root.append($('<p>').addClass('hb-empty-copy').text('Không tìm thấy người dùng nào.'));
            return;
        }
        users.forEach(function (user) {
            const href = '?u=' + encodeURIComponent(user.username || '');
            const row = $('<div>').addClass('hb-search-user-row');
            $('<img>').attr({src: 'public/images/profile/' + (user.profile_pic || 'default_profile.jpg'), alt: 'Ảnh đại diện của ' + ((user.first_name || '') + ' ' + (user.last_name || '')).trim()}).appendTo(row);
            const copy = $('<div>').addClass('hb-search-user-copy').appendTo(row);
            $('<a>').attr('href', href).text(((user.first_name || '') + ' ' + (user.last_name || '')).trim()).appendTo(copy);
            $('<span>').text('@' + (user.username || '')).appendTo(copy);
            $('<a>').attr('href', href).addClass('hb-search-user-open').text('Xem hồ sơ').appendTo(row);
            root.append(row);
        });
    }

    search.on('input', function () {
        const keyword = String(search.val() || '').trim();
        clearTimeout(timer);
        result.show();
        if (!keyword) {
            $('#sra').empty().append($('<p>').addClass('hb-empty-copy').text('Nhập tên hoặc tên người dùng'));
            return;
        }
        timer = setTimeout(function () {
            $.ajax({url: '?api=search', method: 'post', dataType: 'json', data: {keyword: keyword}})
                .done(function (response) { renderUsers(response.users || []); })
                .fail(function () { renderUsers([]); });
        }, 250);
    });

    $(document).on('click', function (event) { if (!$(event.target).closest('#searchform').length) result.hide(); });
    $(document).on('keydown', function (event) {
        if (event.key === 'Escape' && result.is(':visible')) { result.hide(); search.trigger('focus'); }
    });
})(window.jQuery);
