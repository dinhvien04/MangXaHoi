(function ($) {
    if (!$) return;
    $('.like_btn').click(function(){ const b=this,p=$(b).data('postId'); $(b).attr('disabled',true); $.ajax({url:'?api=like',method:'post',dataType:'json',data:{post_id:p},success:function(r){$(b).attr('disabled',false);if(!r.status)return;$(b).hide().siblings('.unlike_btn').show();const c=$('#likecount'+p);c.text(Number(c.text())+1);}}); });
    $('.unlike_btn').click(function(){ const b=this,p=$(b).data('postId'); $(b).attr('disabled',true); $.ajax({url:'?api=unlike',method:'post',dataType:'json',data:{post_id:p},success:function(r){$(b).attr('disabled',false);if(!r.status)return;$(b).hide().siblings('.like_btn').show();const c=$('#likecount'+p);c.text(Math.max(0,Number(c.text())-1));}}); });
})(window.jQuery);
