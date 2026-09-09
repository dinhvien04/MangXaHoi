(function ($) {
    if (!$) return;
    function toggle(button, api, liked) {
        const postId = Number($(button).data('postId')) || 0;
        if (postId <= 0) return;
        $(button).css('pointer-events', 'none');
        $.ajax({url:'?api='+api,method:'post',dataType:'json',data:{post_id:postId}})
            .done(function(r){
                if (!r.status) return;
                const count = $('#likecount'+postId);
                const current = Number(count.text()) || 0;
                if (liked) {
                    $(button).hide().siblings('.unlike_btn').show();
                    count.text(current + 1);
                } else {
                    $(button).hide().siblings('.like_btn').show();
                    count.text(Math.max(0, current - 1));
                }
            })
            .always(function(){ $(button).css('pointer-events', ''); });
    }
    $('.like_btn').click(function(){ toggle(this,'like',true); });
    $('.unlike_btn').click(function(){ toggle(this,'unlike',false); });
})(window.jQuery);
