(function ($) {
    if (!$) return;
    function run(button, api, successText, reload) {
        const userId = Number($(button).data('userId')) || 0;
        if (userId <= 0) return;
        $(button).prop('disabled', true);
        $.ajax({url: '?api=' + api, method: 'post', dataType: 'json', data: {user_id:userId}})
            .done(function(r){
                if (!r.status) return;
                if (reload) { location.reload(); return; }
                $(button).text(successText).prop('disabled', true);
            })
            .fail(function(){ alert('Không thể thực hiện thao tác. Vui lòng thử lại.'); })
            .always(function(){ if (!reload) $(button).prop('disabled', false); });
    }
    $('.followbtn').click(function(){ run(this,'follow','Đã theo dõi',false); });
    $('.unfollowbtn').click(function(){ run(this,'unfollow','Đã hủy theo dõi',false); });
    $('.unblockbtn').click(function(){ run(this,'unblock','',true); });
})(window.jQuery);
