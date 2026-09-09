(function ($) {
    if (!$) return;
    $('#show_not').click(function(){ $.ajax({url:'?api=notifications_read',method:'post',dataType:'json',success:function(r){if(r.status)$('.un-count').hide();}}); });
})(window.jQuery);
