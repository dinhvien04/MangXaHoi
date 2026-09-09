(function($){
if(!$)return;
function call(button, api, userId, done){
    const b=$(button); b.prop('disabled',true);
    $.ajax({url:'?api='+api,method:'post',dataType:'json',data:{user_id:userId}})
        .done(function(r){if(r.status)done(r);else b.prop('disabled',false);})
        .fail(function(){b.prop('disabled',false); alert('Không thể thực hiện thao tác.');});
}
$('.verify_user_btn').click(function(){const b=this;call(b,'verify_user',$(b).data('userId'),function(){$(b).text('Đã xác minh');});});
$('.block_user_btn').click(function(){const b=this;call(b,'block_user',$(b).data('userId'),function(){$(b).hide();$(b).siblings('.unblock_user_btn').show().prop('disabled',false);});});
$('.unblock_user_btn').click(function(){const b=this;call(b,'unblock_user',$(b).data('userId'),function(){$(b).hide();$(b).siblings('.block_user_btn').show().prop('disabled',false);});});
})(window.jQuery);
