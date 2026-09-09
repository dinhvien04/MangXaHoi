(function ($) {
    if (!$) return;
    $('.add-comment').click(function () {
        const button = this;
        const input = $(button).siblings('.comment-input');
        const comment = input.val();
        if (!comment) return;
        const postId = $(button).data('postId');
        const target = $(button).data('cs');
        const page = $(button).data('page');
        $(button).attr('disabled', true);
        input.attr('disabled', true);
        $.ajax({
            url: '?api=add_comment', method: 'post', dataType: 'json', data: { post_id: postId, comment: comment },
            success: function (response) {
                $(button).attr('disabled', false); input.attr('disabled', false);
                if (!response.status) { alert('Không thể thêm bình luận. Vui lòng thử lại.'); return; }
                input.val(''); $('#' + target).prepend(response.comment); $('.nce').hide();
                if (page === 'wall') location.reload();
            },
            error: function () { $(button).attr('disabled', false); input.attr('disabled', false); }
        });
    });
})(window.jQuery);
