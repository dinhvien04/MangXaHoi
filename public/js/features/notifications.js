(function ($) {
    'use strict';
    if (!$) return;
    $('#show_not').on('click', function () {
        $.ajax({url: '?api=notifications_read', method: 'post', dataType: 'json'})
            .done(function (response) {
                if (!response.status) return;
                $('#show_not .un-count').hide();
                $('#notification_sidebar .hb-notification-item').removeClass('is-unread');
                $('#notification_sidebar .hb-unread-dot').remove();
            });
    });
})(window.jQuery);
