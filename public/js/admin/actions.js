(function($){
if(!$)return;
$('.verify_user_btn').click(function(){const b=this;$(b).prop('disabled',true);$.post('?api=verify_user',{user_id:$(b).data('userId')},function(r){if(r.status)$(b).text('Đã xác minh');else $(b).prop('disabled',false);},'json');});
$('.block_user_btn').click(function(){const b=this;$(b).prop('disabled',true);$.post('?api=block_user',{user_id:$(b).data('userId')},function(r){if(r.status){$(b).hide();$(b).siblings('.unblock_user_btn').show().prop('disabled',false);}else $(b).prop('disabled',false);},'json');});
$('.unblock_user_btn').click(function(){const b=this;$(b).prop('disabled',true);$.post('?api=unblock_user',{user_id:$(b).data('userId')},function(r){if(r.status){$(b).hide();$(b).siblings('.block_user_btn').show().prop('disabled',false);}else $(b).prop('disabled',false);},'json');});
})(window.jQuery);
