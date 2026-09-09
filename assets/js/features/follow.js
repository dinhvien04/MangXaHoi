(function ($) {
    if (!$) return;

    $('.followbtn').click(function () {
        const button = this;
        const userId = $(button).data('userId');
        $(button).attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?follow',
            method: 'post',
            dataType: 'json',
            data: { user_id: userId },
            success: function (response) {
                if (response.status) {
                    $(button).data('userId', 0);
                    $(button).html('<i class="bi bi-check-circle-fill"></i> Đã theo dõi');
                } else {
                    $(button).attr('disabled', false);
                    alert('Không thể theo dõi người dùng. Vui lòng thử lại.');
                }
            }
        });
    });

    $('.unfollowbtn').click(function () {
        const button = this;
        const userId = $(button).data('userId');
        $(button).attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?unfollow',
            method: 'post',
            dataType: 'json',
            data: { user_id: userId },
            success: function (response) {
                if (response.status) {
                    $(button).data('userId', 0);
                    $(button).html('<i class="bi bi-check-circle-fill"></i> Đã hủy theo dõi');
                } else {
                    $(button).attr('disabled', false);
                    alert('Không thể hủy theo dõi. Vui lòng thử lại.');
                }
            }
        });
    });

    $('.unblockbtn').click(function () {
        const button = this;
        const userId = $(button).data('userId');
        $(button).attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?unblock',
            method: 'post',
            dataType: 'json',
            data: { user_id: userId },
            success: function (response) {
                if (response.status) {
                    location.reload();
                } else {
                    $(button).attr('disabled', false);
                    alert('Không thể bỏ chặn. Vui lòng thử lại.');
                }
            }
        });
    });
})(window.jQuery);
