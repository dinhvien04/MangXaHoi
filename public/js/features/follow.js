(function ($) {
    if (!$) return;
    function run(button, api, successText, reload) {
        const userId = $(button).data('userId');
        $(button).attr('disabled', true);
        $.ajax({url: '?api=' + api, method: 'post', dataType: 'json', data: {user_id:userId}, success:function(r){
            if (!r.status) { $(button).attr('disabled', false); alert('Không thể thực hiện thao tác. Vui lòng thử lại.'); return; }
            if (reload) { location.reload(); return; }
            $(button).html(successText).attr('disabled', true);
        }, error:function(){ $(button).attr('disabled', false); }});
    }
    $('.followbtn').click(function(){ run(this,'follow','<i class="bi bi-check-circle-fill"></i> Đã theo dõi',false); });
    $('.unfollowbtn').click(function(){ run(this,'unfollow','<i class="bi bi-check-circle-fill"></i> Đã hủy theo dõi',false); });
    $('.unblockbtn').click(function(){ run(this,'unblock','',true); });
})(window.jQuery);
