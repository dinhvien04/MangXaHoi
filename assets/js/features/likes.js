(function ($) {
    if (!$) return;

    $('.like_btn').click(function () {
        const button = this;
        const postId = $(button).data('postId');
        $(button).attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?like',
            method: 'post',
            dataType: 'json',
            data: { post_id: postId },
            success: function (response) {
                $(button).attr('disabled', false);
                if (!response.status) {
                    alert('Không thể thích bài viết. Vui lòng thử lại.');
                    return;
                }

                $(button).hide().siblings('.unlike_btn').show();
                const count = $('#likecount' + postId);
                count.text(Number(count.text()) + 1);
            }
        });
    });

    $('.unlike_btn').click(function () {
        const button = this;
        const postId = $(button).data('postId');
        $(button).attr('disabled', true);

        $.ajax({
            url: 'assets/php/ajax.php?unlike',
            method: 'post',
            dataType: 'json',
            data: { post_id: postId },
            success: function (response) {
                $(button).attr('disabled', false);
                if (!response.status) {
                    alert('Không thể bỏ thích bài viết. Vui lòng thử lại.');
                    return;
                }

                $(button).hide().siblings('.like_btn').show();
                const count = $('#likecount' + postId);
                count.text(Math.max(0, Number(count.text()) - 1));
            }
        });
    });
})(window.jQuery);
