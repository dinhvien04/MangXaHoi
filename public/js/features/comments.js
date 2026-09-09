(function ($) {
    if (!$) return;

    function commentNode(comment) {
        const user = comment.user || {};
        const row = $('<div>').addClass('d-flex align-items-center p-2 border-bottom');
        const img = $('<img>').attr({src: 'public/images/profile/' + (user.profile_pic || 'default_profile.jpg'), height: 40, width: 40, alt: ''}).addClass('rounded-circle border');
        const content = $('<div>').addClass('ms-2');
        const title = $('<h6>').css('margin', 0);
        $('<a>').attr('href', '?u=' + encodeURIComponent(user.username || '')).addClass('text-decoration-none text-muted').text('@' + (user.username || '')).appendTo(title);
        title.append(document.createTextNode(' - ' + (comment.comment || '')));
        content.append(title, $('<p>').addClass('mb-0 text-muted small').text('(vừa xong)'));
        return row.append(img, content);
    }

    $('.add-comment').click(function () {
        const button = this;
        const input = $(button).siblings('.comment-input');
        const comment = String(input.val() || '').trim();
        if (!comment) return;
        const postId = Number($(button).data('postId')) || 0;
        const target = String($(button).data('cs') || '');
        const page = $(button).data('page');
        $(button).prop('disabled', true);
        input.prop('disabled', true);
        $.ajax({
            url: '?api=add_comment', method: 'post', dataType: 'json', data: { post_id: postId, comment: comment },
            success: function (response) {
                if (!response.status || !response.comment) return;
                input.val('');
                $('#' + target).prepend(commentNode(response.comment));
                $('.nce').hide();
                if (page === 'wall') location.reload();
            },
            error: function (xhr) {
                const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Không thể thêm bình luận. Vui lòng thử lại.';
                alert(message);
            },
            complete: function () { $(button).prop('disabled', false); input.prop('disabled', false); }
        });
    });
})(window.jQuery);
